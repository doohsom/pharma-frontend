// app/Services/Coldchain/SensorDataService.php
<?php

namespace App\Services\Coldchain;

use Illuminate\Support\Collection;
use Carbon\Carbon;

class DashboardService
{
    private $mockDataPath;

    public function __construct()
    {
        $this->mockDataPath = storage_path('app/mock/sensor_readings.json');
    }

    public function getSensorData($batchId = null)
    {
        $jsonContent = file_get_contents($this->mockDataPath);
        $readings = json_decode($jsonContent, true);
        
        if ($batchId) {
            $readings = array_filter($readings, function($reading) use ($batchId) {
                return $reading['batchId'] === $batchId;
            });
        }

        return collect($readings);
    }

    public function getAnalytics(Collection $readings)
    {
        return [
            'summary' => $this->getSummaryStats($readings),
            'readingsByType' => $this->getReadingsByType($readings),
            'locationStats' => $this->getLocationStats($readings),
            'excursionAnalysis' => $this->getExcursionAnalysis($readings),
            'trends' => $this->getTrends($readings),
            'latestReadings' => $this->getLatestReadings($readings),
        ];
    }

    private function getSummaryStats($readings)
    {
        return [
            'totalReadings' => $readings->count(),
            'excursions' => $readings->where('hasExcursion', true)->count(),
            'uniqueSensors' => $readings->pluck('sensorId')->unique()->count(),
            'uniqueLocations' => $readings->pluck('location')->unique()->count(),
            'timeSpan' => [
                'start' => $readings->min('timestamp'),
                'end' => $readings->max('timestamp')
            ],
            'excursionRate' => round(($readings->where('hasExcursion', true)->count() / $readings->count()) * 100, 2)
        ];
    }

    private function getReadingsByType($readings)
    {
        $types = ['temperature', 'humidity', 'pressure'];
        $stats = [];

        foreach ($types as $type) {
            $typeReadings = $readings->where('readingType', $type);
            $stats[$type] = [
                'count' => $typeReadings->count(),
                'average' => round($typeReadings->avg('value'), 2),
                'min' => $typeReadings->min('value'),
                'max' => $typeReadings->max('value'),
                'excursions' => $typeReadings->where('hasExcursion', true)->count()
            ];
        }

        return $stats;
    }

    private function getLocationStats($readings)
    {
        return $readings->groupBy('location')
            ->map(function ($locationReadings) {
                return [
                    'total' => $locationReadings->count(),
                    'excursions' => $locationReadings->where('hasExcursion', true)->count(),
                    'lastReading' => $locationReadings->sortByDesc('timestamp')->first()
                ];
            });
    }

    private function getExcursionAnalysis($readings)
    {
        $excursions = $readings->where('hasExcursion', true);
        
        return [
            'byType' => $excursions->groupBy('readingType')
                ->map(function ($typeExcursions) {
                    return $typeExcursions->count();
                }),
            'byLocation' => $excursions->groupBy('location')
                ->map(function ($locationExcursions) {
                    return $locationExcursions->count();
                }),
            'timeline' => $excursions->sortBy('timestamp')->values()
        ];
    }

    private function getTrends($readings)
    {
        return $readings->groupBy('readingType')
            ->map(function ($typeReadings) {
                return $typeReadings->sortBy('timestamp')
                    ->values()
                    ->map(function ($reading) {
                        return [
                            'timestamp' => Carbon::parse($reading['timestamp'])->format('Y-m-d H:i'),
                            'value' => $reading['value'],
                            'hasExcursion' => $reading['hasExcursion']
                        ];
                    });
            });
    }

    private function getLatestReadings($readings)
    {
        return $readings->sortByDesc('timestamp')
            ->take(10)
            ->values();
    }
}
