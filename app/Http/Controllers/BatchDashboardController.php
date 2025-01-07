<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class BatchDashboardController extends Controller
{
    private const API_BASE_URL = 'http://localhost:5050/api';

    public function show($batchId)
    {
        // Fetch 10 readings for the given batch ID
        // Generate sample data (replace this with actual database queries when ready)
    $readings = [
        ['id' => 'READING_1735936107684_za640vhwv', 'sensorId' => 'SENSOR_5dhebr2l', 'batchId' => $batchId, 'readingType' => 'temperature', 'value' => 2.09, 'hasExcursion' => true, 'timestamp' => '2025-01-03T20:28:27.684Z'],
        ['id' => 'READING_1735936107685_zb640vhwv', 'sensorId' => 'SENSOR_5dhebr2l', 'batchId' => $batchId, 'readingType' => 'temperature', 'value' => 2.08, 'hasExcursion' => false, 'timestamp' => '2025-01-03T20:28:27.685Z'],
        ['id' => 'READING_1735936107686_zc640vhwv', 'sensorId' => 'SENSOR_5dhebr2m', 'batchId' => $batchId, 'readingType' => 'humidity', 'value' => 60.5, 'hasExcursion' => true, 'timestamp' => '2025-01-03T20:28:27.686Z'],
        ['id' => 'READING_1735936107687_zd640vhwv', 'sensorId' => 'SENSOR_5dhebr2m', 'batchId' => $batchId, 'readingType' => 'humidity', 'value' => 60.6, 'hasExcursion' => false, 'timestamp' => '2025-01-03T20:28:27.687Z'],
        ['id' => 'READING_1735936107688_ze640vhwv', 'sensorId' => 'SENSOR_5dhebr2n', 'batchId' => $batchId, 'readingType' => 'pressure', 'value' => 1013.25, 'hasExcursion' => true, 'timestamp' => '2025-01-03T20:28:27.688Z'],
        ['id' => 'READING_1735936107689_za640vhwv', 'sensorId' => 'SENSOR_5dhebr2n', 'batchId' => $batchId, 'readingType' => 'pressure', 'value' => 1013.26, 'hasExcursion' => false, 'timestamp' => '2025-01-03T20:28:27.689Z'],
        ['id' => 'READING_1735936107690_zb640vhwv', 'sensorId' => 'SENSOR_5dhebr2o', 'batchId' => $batchId, 'readingType' => 'temperature', 'value' => 2.10, 'hasExcursion' => true, 'timestamp' => '2025-01-03T20:28:27.690Z'],
        ['id' => 'READING_1735936107691_zc640vhwv', 'sensorId' => 'SENSOR_5dhebr2o', 'batchId' => $batchId, 'readingType' => 'temperature', 'value' => 2.09, 'hasExcursion' => false, 'timestamp' => '2025-01-03T20:28:27.691Z'],
        ['id' => 'READING_1735936107692_zd640vhwv', 'sensorId' => 'SENSOR_5dhebr2p', 'batchId' => $batchId, 'readingType' => 'humidity', 'value' => 60.3, 'hasExcursion' => true, 'timestamp' => '2025-01-03T20:28:27.692Z'],
        ['id' => 'READING_1735936107693_za640vhwv', 'sensorId' => 'SENSOR_5dhebr2p', 'batchId' => $batchId, 'readingType' => 'humidity', 'value' => 60.4, 'hasExcursion' => false, 'timestamp' => '2025-01-03T20:28:27.693Z'],
    ];
    $readings = collect($readings);

    //// Get all excursion readings
    $excursionReadings = $readings->where('hasExcursion', true);
    
    // Get the latest excursion
    $latestExcursion = $excursionReadings->sortByDesc('timestamp')->first();

    // Your existing calculations
    $uniqueSensors = $readings->unique('sensorId')->count();
    $excursions = $excursionReadings->count();
    
    // Get temperature readings for chart
    $temperatureReadings = $readings
        ->where('readingType', 'temperature')
        ->sortBy('timestamp')
        ->values();
    
    // Get latest 50 readings
    $latestReadings = $readings
        ->sortByDesc('timestamp')
        ->take(50)
        ->values();

    return view('batch.readings', compact(
        'readings',
        'uniqueSensors',
        'excursions',
        'temperatureReadings',
        'latestReadings',
        'batchId',
        'latestExcursion'  // Add this to the compact array
    ));
    
    
}

    
    public function showing($batchId)
    {
        try {
            $sensorDataResponse = Http::timeout(60)->retry(3, 2000)->get(self::API_BASE_URL . "/readings/batch/{$batchId}");
            
            if (!$sensorDataResponse->successful()) {
                \Log::error('Failed to fetch sensor data', ['status' => $sensorDataResponse->status(), 'body' => $sensorDataResponse->body()]);
                return view('batch.shew', ['message' => 'Failed to fetch sensor data']);
            }

            $readings = $sensorDataResponse->json();
            
            \Log::info('Sensor data fetched successfully', ['batchId' => $batchId, 'readingsCount' => count($readings)]);

            $latestReadings = collect($readings)->sortByDesc('timestamp')->take(50)->values();
            $latestReading = $latestReadings->first(); // Add this line to get the most recent reading
            $sensorIds = collect($readings)->pluck('sensorId')->unique()->values() ?? [];
            $excursionCount = collect($readings)->where('hasExcursion', true)->count();

            $lineChartData = $this->prepareLineChartData($readings);
            $barChartData = $this->prepareBarChartData($readings);
            $histogramData = $this->prepareHistogramData($readings);
            $heatmapData = $this->prepareHeatmapData($readings);
            $gaugeData = $this->prepareGaugeData($latestReading); // Use latestReading here
            Log::info($latestReadings);

            return view('batch.shew', compact(
                'latestReadings', 'latestReading', 'sensorIds', 'excursionCount', 'lineChartData',
                'barChartData', 'histogramData', 'heatmapData', 'gaugeData', 'batchId'
            ));
        } catch (\Exception $e) {
            \Log::error('Error in SensorDashboardController', ['error' => $e->getMessage()]);
            return view('batch.shew', ['message' => 'An error occurred while processing the data']);
        }
    }

    private function prepareLineChartData($readings)
    {
        $groupedReadings = collect($readings)->groupBy('sensorId');
        $datasets = $groupedReadings->map(function ($sensorReadings, $sensorId) {
            return [
                'label' => $sensorId,
                'data' => $sensorReadings->map(function ($reading) {
                    return [
                        'x' => Carbon::parse($reading['timestamp'])->timestamp * 1000,
                        'y' => $reading['decryptedValue']
                    ];
                })->sortBy('x')->values()->all()
            ];
        })->values();

        return [
            'datasets' => $datasets,
            'timestamps' => $groupedReadings->first()->pluck('timestamp')->map(function ($timestamp) {
                return Carbon::parse($timestamp)->timestamp * 1000;
            })->sort()->values()
        ];
    }

    private function prepareBarChartData($readings)
    {
        $latestReadings = collect($readings)->groupBy('sensorId')->map->last();
        return [
            'labels' => $latestReadings->keys()->all(),
            'data' => $latestReadings->pluck('decryptedValue')->all()
        ];
    }

    private function prepareHistogramData($readings)
    {
        $values = collect($readings)->pluck('decryptedValue');
        $min = $values->min();
        $max = $values->max();
        $binCount = 10;
        $binSize = ($max - $min) / $binCount;

        $histogram = $values->groupBy(function ($value) use ($min, $binSize) {
            return floor(($value - $min) / $binSize);
        })->map->count();

        return [
            'labels' => $histogram->keys()->map(function ($bin) use ($min, $binSize) {
                $start = $min + ($bin * $binSize);
                $end = $start + $binSize;
                return sprintf("%.1f - %.1f", $start, $end);
            })->all(),
            'data' => $histogram->values()->all(),
        ];
    }

    private function prepareHeatmapData($readings)
    {
        $latestReadings = collect($readings)->groupBy('sensorId')->map->last();
        $sensorCount = $latestReadings->count();
        $gridSize = ceil(sqrt($sensorCount));

        return $latestReadings->values()->map(function ($reading, $index) use ($gridSize) {
            return [
                'x' => $index % $gridSize,
                'y' => floor($index / $gridSize),
                'v' => $reading['decryptedValue']
            ];
        })->all();
    }

    private function prepareGaugeData($latestReading)
    {
        return [
            'value' => $latestReading['decryptedValue'],
            'min' => 0,
            'max' => 100  // Adjust this based on your temperature range
        ];
    }
}
