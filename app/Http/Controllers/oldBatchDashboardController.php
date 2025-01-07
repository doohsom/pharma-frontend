<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class BatchDashboardController extends Controller
{
    private const API_BASE_URL = 'http://localhost:5050/api';

    
    public function show($batchId)
    {

        // Fetch the readings
        $sensorDataResponse = Http::timeout(60)->retry(3, 2000)->get(self::API_BASE_URL . "/readings/batch/{$batchId}");
        $sensorData = $sensorDataResponse->json();
        
        Log::info('datres', [$sensorDataResponse]);
        Log::info('dat', [$sensorData]);

        $sensorDataResponse = Http::timeout(60)->get(self::API_BASE_URL . "/readings/batch/{$batchId}");

        if ($sensorDataResponse->failed()) {
            return view('batch.shew', [
                'error' => 'Failed to fetch sensor data or timeout error',
                'sensors' => [],
                'excursions' => [],
                'totalReadings' => 0
            ]);
        }

        $sensorData = $sensorDataResponse->json();
        $readings = Arr::get($sensorData, 'readings', []);

        // Prepare data for charts
        $sensorIds = array_unique(array_column($readings, 'sensorId'));
        $totalReadings = count($readings);
        $excursions = array_filter($readings, fn($reading) => $reading['hasExcursion']);

        // Data for line chart (time series)
        $timestamps = array_column($readings, 'timestamp');
        $decryptedValues = array_column($readings, 'decryptedValue');

        // Data for bar chart (latest values)
        $latestSensorReadings = [];
        foreach ($sensorIds as $sensorId) {
            // Get the latest reading for each sensor
            $latestSensorReading = collect($readings)
                ->where('sensorId', $sensorId)
                ->sortByDesc('timestamp')
                ->first();

            $latestSensorReadings[$sensorId] = $latestSensorReading['decryptedValue'];
        }

        // Data for histogram
        $readingValues = array_map('strval', array_column($readings, 'decryptedValue'));
        $readingFrequency = array_count_values($readingValues);

        return view('batch.shew', [
            'timestamps' => $timestamps,
            'decryptedValues' => $decryptedValues,
            'latestSensorReadings' => $latestSensorReadings,
            'readingFrequency' => $readingFrequency,
            'excursions' => $excursions,
            'totalReadings' => $totalReadings,
            'sensors' => $sensorIds,
            'numberOfSensors' => 3
        ]);
    }


    public function shower($batchId)
    {
        try {
            $response = Http::get(self::API_BASE_URL . "/readings/batch/{$batchId}");
            
            if (!$response->successful()) {
                Log::error('Failed to fetch batch data', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                throw new \Exception('Failed to fetch batch data');
            }

            $apiData = $response->json();
            
            Log::info('API Response:', [
                'sample_data' => array_slice($apiData['readings'] ?? [], 0, 2)
            ]);

            // Group readings by sensor type
            $readingsByType = $this->groupReadings($apiData['readings'] ?? []);
            $excursions = array_filter($apiData['readings'] ?? [], fn($reading) => $reading['hasExcursion']);
            $paginatedReadings = $this->paginateReadings($apiData['readings'] ?? []);

            return view('batch.show', [
                'batchId' => $batchId,
                'readingsByType' => $readingsByType,
                'excursions' => array_values($excursions),
                'paginatedReadings' => $paginatedReadings,
                'lastUpdated' => now(),
            ]);

        } catch (\Exception $e) {
            Log::error('Error in batch dashboard:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Failed to load batch data');
        }
    }

    public function data($batchId)
    {
        try {
            $response = Http::get(self::API_BASE_URL . "/readings/batch/{$batchId}");
            
            if (!$response->successful()) {
                throw new \Exception('Failed to fetch batch data');
            }

            $apiData = $response->json();
            $readingsByType = $this->groupReadings($apiData['readings'] ?? []);
            $excursions = array_filter($apiData['readings'] ?? [], fn($reading) => $reading['hasExcursion']);
            $paginatedReadings = $this->paginateReadings($apiData['readings'] ?? []);

            return response()->json([
                'readingsByType' => $readingsByType,
                'excursions' => array_values($excursions),
                'paginatedReadings' => $paginatedReadings
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching batch data:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function groupReadings(array $readings)
    {
        $groups = [
            'temperature' => ['readings' => [], 'current_value' => null, 'min_value' => null, 'max_value' => null, 'avg_value' => null],
            'humidity' => ['readings' => [], 'current_value' => null, 'min_value' => null, 'max_value' => null, 'avg_value' => null],
            'pressure' => ['readings' => [], 'current_value' => null, 'min_value' => null, 'max_value' => null, 'avg_value' => null]
        ];

        foreach ($readings as $reading) {
            // Extract sensor type from sensorId
            if (str_contains($reading['sensorId'], '_TEMP')) {
                $type = 'temperature';
            } elseif (str_contains($reading['sensorId'], '_HUM')) {
                $type = 'humidity';
            } elseif (str_contains($reading['sensorId'], '_PRESS')) {
                $type = 'pressure';
            } else {
                continue;
            }

            $groups[$type]['readings'][] = [
                'timestamp' => Carbon::parse($reading['timestamp']),
                'value' => $reading['decryptedValue'],
                'hasExcursion' => $reading['hasExcursion']
            ];
        }

        // Calculate statistics for each type
        foreach ($groups as $type => &$data) {
            if (!empty($data['readings'])) {
                $values = array_column($data['readings'], 'value');
                $data['current_value'] = end($values);
                $data['min_value'] = min($values);
                $data['max_value'] = max($values);
                $data['avg_value'] = round(array_sum($values) / count($values), 2);
            }
        }

        return $groups;
    }

    private function paginateReadings(array $readings)
    {
        $page = request()->get('page', 1);
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        // Sort readings by timestamp descending
        usort($readings, function($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });

        $total = count($readings);
        $currentPageReadings = array_slice($readings, $offset, $perPage);

        return [
            'current_page' => $page,
            'data' => $currentPageReadings,
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $total),
            'total' => $total,
            'per_page' => $perPage,
            'last_page' => ceil($total / $perPage),
            'links' => $this->generatePaginationLinks($total, $perPage, $page)
        ];
    }

    private function generatePaginationLinks($total, $perPage, $currentPage)
    {
        $lastPage = ceil($total / $perPage);
        $links = [];
        
        // Previous link
        $links[] = [
            'url' => $currentPage > 1 ? url()->current() . '?page=' . ($currentPage - 1) : null,
            'label' => '&laquo; Previous',
            'active' => false
        ];

        // Numbered links
        for ($i = 1; $i <= $lastPage; $i++) {
            $links[] = [
                'url' => url()->current() . '?page=' . $i,
                'label' => (string)$i,
                'active' => $i == $currentPage
            ];
        }

        // Next link
        $links[] = [
            'url' => $currentPage < $lastPage ? url()->current() . '?page=' . ($currentPage + 1) : null,
            'label' => 'Next &raquo;',
            'active' => false
        ];

        return $links;
    }
}