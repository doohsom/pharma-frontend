<?php

$sensors = [
    ['id' => 'SENSOR1', 'type' => 'temperature', 'min' => 2.0, 'max' => 8.0],
    ['id' => 'SENSOR2', 'type' => 'humidity', 'min' => 30.0, 'max' => 65.0],
    ['id' => 'SENSOR3', 'type' => 'pressure', 'min' => 980.0, 'max' => 1020.0]
];

$readings = [];
$readingCounter = 1;
$baseTime = strtotime('2024-12-18 00:00:00');

foreach ($sensors as $sensor) {
    for ($i = 0; $i < 50; $i++) {
        $timestamp = date('Y-m-d\TH:i:s\Z', $baseTime + ($i * 1800)); // Every 30 minutes
        
        // Calculate value with sine wave variation
        $hourOfDay = date('G', $baseTime + ($i * 1800));
        $variation = sin($hourOfDay * M_PI / 12);
        
        $baseValue = ($sensor['max'] + $sensor['min']) / 2;
        $range = ($sensor['max'] - $sensor['min']) / 2;
        $value = $baseValue + ($range * $variation * 0.5) + (rand(-100, 100) / 1000 * $range);
        
        // Add occasional excursions (5% chance)
        $hasExcursion = rand(1, 100) <= 5;
        if ($hasExcursion) {
            $excursionMagnitude = rand(0, 100) > 50 ? 1.5 : 0.5;
            $value = $sensor['min'] + ($sensor['max'] - $sensor['min']) * $excursionMagnitude;
        }
        
        // Ensure value stays within absolute limits
        $value = max($sensor['min'] * 0.5, min($sensor['max'] * 1.5, $value));
        
        $readings[] = [
            'reading_id' => 'READING' . $readingCounter,
            'sensor_id' => $sensor['id'],
            'batch_id' => 'BATCH1',
            'reading_type' => $sensor['type'],
            'value' => round($value, 2),
            'has_excursion' => $hasExcursion,
            'reading_timestamp' => $timestamp
        ];
        
        $readingCounter++;
    }
}

$output = ['sensor_readings' => $readings];
file_put_contents('sensor_readings.json', json_encode($output, JSON_PRETTY_PRINT));
echo "Generated " . count($readings) . " readings\n";