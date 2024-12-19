<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class BatchController extends Controller
{
    private $apiBaseUrl = 'http://localhost:8080/api';
    private $cacheTimeout = 30;

    public function index()
    {
        $readings = Cache::remember('sensor_readings', $this->cacheTimeout, function () {
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

        // Group readings by batch and prepare summary
        $batches = collect($readings)
            ->groupBy('batch_id')
            ->map(function ($batchReadings) {
                $timestamp = $batchReadings->first()['reading_timestamp'];
                return [
                    'batch_id' => $batchReadings->first()['batch_id'],
                    'timestamp' => Carbon::parse($timestamp),
                    'total_readings' => $batchReadings->count(),
                    'excursions' => $batchReadings->where('has_excursion', true)->count(),
                    'readings_by_type' => $batchReadings->groupBy('reading_type')
                        ->map(function ($typeReadings) {
                            return [
                                'avg_value' => round($typeReadings->avg('value'), 2),
                                'excursions' => $typeReadings->where('has_excursion', true)->count()
                            ];
                        })
                ];
            })
            ->sortByDesc('timestamp');

        return view('batch.index', [
            'batches' => $batches
        ]);
    }



    public function show($batchId)
{
    $readings = Cache::remember("batch_{$batchId}", $this->cacheTimeout, function () use ($batchId) {
        try {
            $response = Http::get("{$this->apiBaseUrl}/readings/batch/{$batchId}");
            if ($response->successful()) {
                return $response->json()['readings'] ?? [];
            }
            return [];
        } catch (\Exception $e) {
            \Log::error("Error fetching batch readings: " . $e->getMessage());
            return [];
        }
    });

    $batchReadings = collect($readings);
    
    if ($batchReadings->isEmpty()) {
        return redirect()->route('batch.index')
            ->with('error', 'Batch not found');
    }

    // Get latest reading for each sensor
    $latestSensorReadings = $batchReadings
        ->groupBy('sensor_id')
        ->map(function ($readings) {
            return $readings->sortByDesc('reading_timestamp')->first();
        })
        ->values(); 

    // Manually paginate readings
    $page = request()->get('page', 1);
    $perPage = 20;
    $allReadings = $batchReadings->sortByDesc('reading_timestamp')->values();
    $paginatedReadings = new \Illuminate\Pagination\LengthAwarePaginator(
        $allReadings->forPage($page, $perPage),
        $allReadings->count(),
        $perPage,
        $page,
        ['path' => request()->url()]
    );

    return view('batch.show', [
        'batchId' => $batchId,
        'timestamp' => Carbon::parse($batchReadings->first()['reading_timestamp']),
        'latestSensorReadings' => $latestSensorReadings,
        'excursions' => $batchReadings->where('has_excursion', true),
        'paginatedReadings' => $paginatedReadings,
        'lastUpdated' => now()
    ]);
}

    public function sensorReadings($batchId, $sensorId)
    {
        $readings = Cache::remember("batch_{$batchId}_sensor_{$sensorId}", $this->cacheTimeout, function () use ($batchId, $sensorId) {
            try {
                $response = Http::get("{$this->apiBaseUrl}/readings/batch/{$batchId}");
                if ($response->successful()) {
                    return collect($response->json()['readings'] ?? [])
                        ->where('sensor_id', $sensorId)
                        ->sortByDesc('reading_timestamp')
                        ->values()
                        ->all();
                }
                return [];
            } catch (\Exception $e) {
                \Log::error("Error fetching sensor readings: " . $e->getMessage());
                return [];
            }
        });

        return response()->json([
            'readings' => $readings
        ]);
    }

    public function refreshBatch($batchId)
    {
        Cache::forget("batch_{$batchId}");
        return redirect()->route('batch.show', $batchId)
            ->with('message', 'Data refreshed successfully');
    }


    private function calculateStdDev($values)
    {
        $mean = $values->avg();
        $variance = $values->map(function ($value) use ($mean) {
            return pow($value - $mean, 2);
        })->avg();
        
        return sqrt($variance);
    }

    public function refresh()
    {
        Cache::forget('sensor_readings');
        return redirect()->route('batch.index')
            ->with('message', 'Data refreshed successfully');
    }
}