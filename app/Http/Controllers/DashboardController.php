<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    private $readings = [];
    private $apiBaseUrl = 'http://localhost:8080/api';
    private $cacheTimeout = 30;

    public function __construct()
    {
        $this->readings = Cache::remember('sensor_readings', $this->cacheTimeout, function () {
            try {
                $response = Http::get("{$this->apiBaseUrl}/readings");
                if ($response->successful()) {
                    return $response->json()['readings'] ?? [];
                }
                return [];
            } catch (\Exception $e) {
                \Log::error("Error fetching sensor readings: " . $e->getMessage());
                return [];
            }
        });
    }

    public function show()
    {
        // Group all readings by batch first
        $batches = collect($this->readings)->groupBy('batch_id');
        
        // Get latest batch readings
        $latestBatch = $batches
            ->sortByDesc(function ($batch) {
                return $batch->first()['reading_timestamp'];
            })
            ->first() ?? collect([]);

        // Get latest readings by type from the latest batch
        $latestReadings = $latestBatch
            ->groupBy('reading_type')
            ->map(function ($readings) {
                return $readings->first();
            });

        // Get excursions grouped by batch
        $excursions = $batches->map(function ($batchReadings) {
            return [
                'batch_id' => $batchReadings->first()['batch_id'],
                'timestamp' => $batchReadings->first()['reading_timestamp'],
                'readings' => $batchReadings->where('has_excursion', true)->values()
            ];
        })->filter(function ($batch) {
            return $batch['readings']->isNotEmpty();
        })->values();

        // Prepare batch summary data
        $batchSummary = $batches->map(function ($batchReadings) {
            $timestamp = $batchReadings->first()['reading_timestamp'];
            return [
                'batch_id' => $batchReadings->first()['batch_id'],
                'timestamp' => Carbon::parse($timestamp)->format('Y-m-d H:i:s'),
                'total_readings' => $batchReadings->count(),
                'excursions' => $batchReadings->where('has_excursion', true)->count(),
                'readings_by_type' => $batchReadings->groupBy('reading_type')->map(function ($typeReadings) {
                    return [
                        'avg_value' => $typeReadings->avg('value'),
                        'excursions' => $typeReadings->where('has_excursion', true)->count()
                    ];
                })
            ];
        });

        // Prepare time series data for charts
        $chartData = collect(['temperature', 'humidity', 'pressure'])->mapWithKeys(function ($type) use ($batches) {
            return [$type => $batches->map(function ($batchReadings) use ($type) {
                $typeReadings = $batchReadings->where('reading_type', $type);
                return [
                    'timestamp' => Carbon::parse($batchReadings->first()['reading_timestamp'])->format('Y-m-d H:i:s'),
                    'avg_value' => $typeReadings->avg('value'),
                    'min_value' => $typeReadings->min('value'),
                    'max_value' => $typeReadings->max('value'),
                    'batch_id' => $batchReadings->first()['batch_id']
                ];
            })->sortBy('timestamp')->values()];
        });

        return view('dashboard', [
            'latestReadings' => $latestReadings,
            'excursions' => $excursions,
            'chartData' => $chartData,
            'batchSummary' => $batchSummary,
            'latestBatch' => $latestBatch->groupBy('reading_type')
        ]);
    }

    public function refreshData()
    {
        Cache::forget('sensor_readings');
        return redirect()->route('dashboard')->with('message', 'Data refreshed successfully');
    }
}