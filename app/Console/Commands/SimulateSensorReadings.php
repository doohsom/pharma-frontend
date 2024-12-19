<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpMqtt\Client\MqttClient;
use Carbon\Carbon;
use Illuminate\Support\Str;

class SimulateSensorReadings extends Command
{
    protected $signature = 'sensors:generate {batch_id?}';
    protected $description = 'Generate and publish sensor readings for a batch';

    private $client;
    private $sensors = [];
    private $readingTypes = ['temperature', 'humidity', 'pressure'];
    private $readingInterval = 10;
    
    private $limits = [
        'temperature' => ['min' => 20, 'max' => 30],
        'humidity' => ['min' => 35, 'max' => 65],
        'pressure' => ['min' => 990, 'max' => 1010]
    ];

    private $excursionProbability = 0.15; // 15% chance for a sensor to have excursions
    private $excursionSensors = [];
    
    public function __construct()
    {
        parent::__construct();
        
        // Assign reading types evenly across sensors (4 sensors per type)
        foreach ($this->readingTypes as $type) {
            for ($i = 1; $i <= 4; $i++) {
                $sensorId = sprintf('SENSOR_%s_%d', strtoupper($type), $i);
                $this->sensors[$sensorId] = $type;
            }
        }
    }

    public function handle()
    {
        $batchId = $this->argument('batch_id') ?? 'BATCH' . Carbon::now()->format('YmdHis');
        
        try {
            $this->client = new MqttClient(
                config('mqtt.host', 'localhost'),
                config('mqtt.port', 1883)
            );
            
            $this->client->connect();
            
            $this->info("Starting monitoring for batch: {$batchId}");
            
            // Initialize base values and mark sensors for excursions
            $sensorBaseValues = [];
            foreach ($this->sensors as $sensorId => $readingType) {
                $sensorBaseValues[$sensorId] = $this->getInitialValue($readingType);

                if (rand(0, 100) < ($this->excursionProbability * 100)) {
                    $this->excursionSensors[] = $sensorId;
                    $this->info("Sensor {$sensorId} will have excursions");
                }
            }

            $cycleCount = 0;
            while (true) {
                $cycleCount++;
                foreach ($this->sensors as $sensorId => $readingType) {
                    $value = $this->generateReading(
                        $sensorId, 
                        $readingType, 
                        $sensorBaseValues[$sensorId], 
                        $cycleCount
                    );
                    
                    // Update base value
                    $sensorBaseValues[$sensorId] = $value;

                    $hasExcursion = $this->isExcursion($value, $readingType, $sensorId);

                    $reading = [
                        'reading_id' => 'READING' . Str::random(8),
                        'sensor_id' => $sensorId,
                        'batch_id' => $batchId,
                        'reading_type' => $readingType,
                        'value' => $value,
                        'has_excursion' => $hasExcursion,
                        'reading_timestamp' => Carbon::now()->toIso8601String()
                    ];

                    $topic = "sensors/{$sensorId}";
                    $this->client->publish(
                        $topic,
                        json_encode($reading),
                        0
                    );

                    if ($hasExcursion) {
                        $this->info("Excursion detected for {$sensorId}: {$value}");
                    }
                }

                $this->info("Published readings for batch {$batchId} at " . Carbon::now()->format('Y-m-d H:i:s'));
                sleep($this->readingInterval);
            }
            
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            
            if ($this->client && $this->client->isConnected()) {
                $this->client->disconnect();
            }
            
            return 1;
        }
    }

    private function getInitialValue($type)
    {
        $range = $this->limits[$type];
        $mid = ($range['min'] + $range['max']) / 2;
        $spread = ($range['max'] - $range['min']) * 0.2;
        return round(rand(
            intval(($mid - $spread) * 10), 
            intval(($mid + $spread) * 10)
        ) / 10, 2);
    }

    private function generateReading($sensorId, $readingType, $baseValue, $cycleCount)
    {
        if (in_array($sensorId, $this->excursionSensors) && $cycleCount % 5 === 0) {
            $excursionMagnitude = match($readingType) {
                'temperature' => rand(30, 50) / 10,
                'humidity' => rand(50, 100) / 10,
                'pressure' => rand(100, 200) / 10
            };

            return round(
                rand(0, 1) === 0 
                    ? $this->limits[$readingType]['min'] - $excursionMagnitude
                    : $this->limits[$readingType]['max'] + $excursionMagnitude,
                2
            );
        }

        $variation = match($readingType) {
            'temperature' => rand(-5, 5) / 10,
            'humidity' => rand(-20, 20) / 10,
            'pressure' => rand(-10, 10) / 10
        };

        return round($baseValue + $variation, 2);
    }

    private function isExcursion($value, $type, $sensorId)
    {
        return $value < $this->limits[$type]['min'] || $value > $this->limits[$type]['max'];
    }
}