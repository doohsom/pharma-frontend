<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class SensorService
{
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = 'http://localhost:5050/api';
    }

    

    public function createSensors(array $sensorsData)
    {
        try {
            $formattedSensors = [];
            
            foreach ($sensorsData as $sensorData) {
                $sensorId = 'SENSOR_' . strtoupper(Str::random(8));
                
                $formattedSensors[] = [
                    'ID' => $sensorId,
                    'BatchID' => $sensorData['batch_id'],
                    'Type' => $sensorData['type'],
                    'Location' => (string) $sensorData['location'],
                ];
            }

            $requestData = [
                'sensors' => $formattedSensors
            ];

            Log::info('Creating bulk sensors:', [
                'request_data' => $requestData,
                'sensor_count' => count($formattedSensors)
            ]);

            $response = Http::post("{$this->baseUrl}/sensors", $requestData);

            if (!$response->successful()) {
                $errorMessage = $response->json()['error'] ?? 'Failed to create sensors';
                Log::error('Blockchain API error:', [
                    'status_code' => $response->status(),
                    'response' => $response->json(),
                    'request_data' => $requestData
                ]);
                throw new Exception($errorMessage);
            }

            // Return both the API response and our generated sensor IDs
            return array_merge(
                $response->json(),
                ['sensors' => $formattedSensors]
            );

        } catch (Exception $e) {
            Log::error('Error creating bulk sensors:', [
                'error' => $e->getMessage(),
                'data' => $sensorsData,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function getBatchSensors($batchId)
    {
        try {
            $response = Http::get("{$this->baseUrl}/sensors/batch/{$batchId}");

            if (!$response->successful()) {
                Log::error('Failed to fetch batch sensors:', [
                    'batch_id' => $batchId,
                    'status_code' => $response->status(),
                    'response' => $response->json()
                ]);
                throw new Exception($response->json()['error'] ?? 'Failed to fetch sensors');
            }

            return $response->json();

        } catch (Exception $e) {
            Log::error('Error fetching batch sensors:', [
                'batch_id' => $batchId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function getSensors(?string $batchId = null)
    {
        try {
            $url = "{$this->baseUrl}/sensors";
            if ($batchId) {
                $url .= "?batch_id=" . urlencode($batchId);
            }

            Log::info('Fetching sensors:', [
                'url' => $url,
                'batch_id' => $batchId
            ]);

            $response = Http::get($url);

            if (!$response->successful()) {
                Log::error('Failed to fetch sensors:', [
                    'status_code' => $response->status(),
                    'response' => $response->json(),
                    'batch_id' => $batchId
                ]);
                throw new Exception($response->json()['error'] ?? 'Failed to fetch sensors');
            }

            return $response->json();

        } catch (Exception $e) {
            Log::error('Error fetching sensors:', [
                'error' => $e->getMessage(),
                'batch_id' => $batchId,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
