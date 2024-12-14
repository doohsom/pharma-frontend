<?php

namespace App\Services\Coldchain;
use Illuminate\Support\Facades\Http;
use Exception;

class SensorApiService
{
    private $mockDataPath;

    public function __construct()
    {
        $this->mockDataPath = storage_path('app/mock');
    }

    public function createSensorReading(array $readingData)
    {
        $readings = $this->readJsonFile('sensor_readings.json');
        
        if (isset($readings[$readingData['id']])) {
            throw new \Exception('Sensor reading already exists');
        }

        $readingData['timestamp'] = now()->toISOString();
        $readingData['hasExcursion'] = false; // This should be calculated based on product requirements
        
        $readings[$readingData['id']] = $readingData;
        $this->writeJsonFile('sensor_readings.json', $readings);

        return $readingData;
    }

    public function getReadingsByBatch(string $batchId)
    {
        $readings = $this->readJsonFile('sensor_readings.json');
        return array_values(array_filter($readings, fn($reading) => $reading['batchId'] === $batchId));
    }

    public function getExcursionReadings()
    {
        $readings = $this->readJsonFile('sensor_readings.json');
        return array_values(array_filter($readings, fn($reading) => $reading['hasExcursion']));
    }

    private function readJsonFile(string $filename)
    {
        $filepath = $this->mockDataPath . '/' . $filename;
        
        if (!file_exists($filepath)) {
            return [];
        }

        $content = file_get_contents($filepath);
        return json_decode($content, true) ?? [];
    }

    private function writeJsonFile(string $filename, array $data)
    {
        if (!file_exists($this->mockDataPath)) {
            mkdir($this->mockDataPath, 0755, true);
        }

        $filepath = $this->mockDataPath . '/' . $filename;
        file_put_contents($filepath, json_encode($data, JSON_PRETTY_PRINT));
    }
}