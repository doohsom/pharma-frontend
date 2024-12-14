<?php

function generateSensorReadings($count = 50)
{
    $readings = [];
    $startTime = strtotime('2024-02-01 10:00:00');
    $batchIds = ['BATCH_001', 'BATCH_002'];
    $sensorIds = ['SENSOR_001', 'SENSOR_002', 'SENSOR_003'];
    $locations = ['Transit Hub NYC', 'Highway I-95', 'Distribution Center PA', 'Vendor Warehouse FL'];
    $types = ['temperature', 'humidity', 'pressure'];

    
    for ($i = 0; $i < $count; $i++) {
        $timestamp = date('Y-m-d\TH:i:s\Z', $startTime + ($i * 900)); // Every 15 minutes
        $temperature = rand(10, 90) / 10; // Random temperature between 1.0 and 9.0
        $batchId = $batchIds[array_rand($batchIds)];
        $sensorId = $sensorIds[array_rand($sensorIds)];
        $location = $locations[array_rand($locations)];
        $type = $types[array_rand($types)];
        
        $readings[] = [
            "id" => "READ_" . str_pad($i + 4, 3, '0', STR_PAD_LEFT),
            "sensorId" => $sensorId,
            "readingType" => $type,
            "value" => $temperature,
            "batchId" => $batchId,
            "location" => $location,
            "timestamp" => $timestamp,
            "hasExcursion" => $temperature < 2.0 || $temperature > 8.0
        ];
    }

    return $readings;
}




        // Generate extra readings and merge with existing ones
$extraReadings = generateSensorReadings(50);
print_r(json_encode($extraReadings));