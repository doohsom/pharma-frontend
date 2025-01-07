<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DashboardService
{
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = 'http://localhost:5050/api';
    }


    public function getBatch($batchId)
    {
        try {
            $response = Http::get("{$this->baseUrl}/readings/batch/{$batchId}");
            
            if (!$response->successful()) {
                throw new \Exception('Failed to fetch batch data');
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Error fetching batch data', [
                'batch_id' => $batchId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function getSensorReadings($batchId, $sensorId = null)
    {
        try {
            $url = $sensorId 
                ? "{$this->baseUrl}/sensors/{$sensorId}/readings" 
                : "{$this->baseUrl}/readings/batch/{$batchId}";

            $response = Http::get($url);
            
            if (!$response->successful()) {
                throw new \Exception('Failed to fetch sensor readings');
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Error fetching sensor readings', [
                'batch_id' => $batchId,
                'sensor_id' => $sensorId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function getSensor($sensorId)
    {
        try {
            $response = Http::get("{$this->baseUrl}/sensors/{$sensorId}");
            
            if (!$response->successful()) {
                throw new \Exception('Failed to fetch sensor data');
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Error fetching sensor data', [
                'sensor_id' => $sensorId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}