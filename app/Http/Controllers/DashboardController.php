<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class DashboardController extends Controller
{
    private $mockDataPath;

    public function __construct()
    {
        $this->mockDataPath = storage_path('app/mock/sensor_readings.json');
    }

    public function show()
    {
        try {
            // Fetch and parse sensor readings
            $rawData = json_decode(file_get_contents($this->mockDataPath));
            $sensorData = collect($rawData)
                ->where('batchId', 'BATCH_001')
                ->map(function($reading) {
                    return (object) [
                        'timestamp' => Carbon::parse($reading->timestamp)->format('c'),
                        'readingType' => $reading->readingType,
                        'value' => (float) $reading->value,
                        'location' => $reading->location,
                        'hasExcursion' => (bool) $reading->hasExcursion,
                        'batchId' => $reading->batchId
                    ];
                });

            // Calculate statistics
            $avgTemp = $sensorData
                ->where('readingType', 'temperature')
                ->avg('value') ?? 0;

            $avgHumidity = $sensorData
                ->where('readingType', 'humidity')
                ->avg('value') ?? 0;

            $avgPressure = $sensorData
                ->where('readingType', 'pressure')
                ->avg('value') ?? 0;

            $excursionCount = $sensorData
                ->where('hasExcursion', true)
                ->count();

            $uniqueLocations = $sensorData
                ->pluck('location')
                ->unique()
                ->count();

            $lastReading = $sensorData->last();

            // Debug data
            Log::info('Sensor Data:', ['data' => $sensorData->toArray()]);

            return view('dashboard', compact(
                'sensorData',
                'excursionCount',
                'uniqueLocations',
                'lastReading',
                'avgTemp',
                'avgHumidity',
                'avgPressure'
            ));
        } catch (\Exception $e) {
            \Log::error('Dashboard Error: ' . $e->getMessage());
            return view('dashboard')->withErrors(['error' => 'Failed to fetch sensor data: ' . $e->getMessage()]);
        }
    }
    
    public function oldShow()
    {
        try {
            // Fetch sensor readings from API
            //$response = Http::get('api/sensor-readings/batch/BATCH001');
            $sensorData = json_decode(file_get_contents($this->mockDataPath));
            $sensorData = collect($sensorData)->where('batchId', 'BATCH_001');
            //$sensorData = $response->json();

            // Calculate statistics
            $uniqueLocations = collect($sensorData)->pluck('location')->unique()->count();
            $lastReading = collect($sensorData)->last();

            // Calculate averages
            $avgTemp = collect($sensorData)
                ->where('readingType', 'temperature')
                ->avg('value');

            $avgHumidity = collect($sensorData)
                ->where('readingType', 'humidity')
                ->avg('value');

            $avgPressure = collect($sensorData)
                ->where('readingType', 'pressure')
                ->avg('value');
                
            $excursionCount = collect($sensorData)
                ->where('hasExcursion', true)
                ->count();

            return view('dashboard', compact(
                'sensorData',
                'excursionCount',
                'uniqueLocations',
                'lastReading',
                'avgTemp',
                'avgHumidity',
                'avgPressure'
            ));
        } catch (\Exception $e) {
            return view('dashboard')->withErrors(['error' => 'Failed to fetch sensor data']);
        }
    }
}