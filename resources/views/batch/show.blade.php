<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Batch {{ $batchId }} - Cold Chain Monitoring</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen p-8">
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="flex justify-between items-center mb-8">
                <div>
                    <a href="{{ route('batches.index') }}" class="text-blue-500 hover:text-blue-600 mb-2 inline-block">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Batches
                    </a>
                    <h1 class="text-3xl font-bold">Batch {{ $batchId }}</h1>
                    <p class="text-gray-500">
                        Started: {{ $timestamp->format('Y-m-d H:i:s') }}
                        <span class="text-sm">({{ $timestamp->diffForHumans() }})</span>
                    </p>
                </div>
                <div class="flex items-center gap-4">
                    <p class="text-sm text-gray-500">
                        Last updated: {{ $lastUpdated->diffForHumans() }}
                    </p>
                    <a href="{{ route('batches.refresh.batch', $batchId) }}" 
                       class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
                        <i class="fas fa-sync-alt mr-2"></i> Refresh Data
                    </a>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                @foreach(['temperature' => ['icon' => 'thermometer-half', 'color' => 'red', 'unit' => '°C', 'limits' => ['min' => 20, 'max' => 30]],
                         'humidity' => ['icon' => 'tint', 'color' => 'blue', 'unit' => '%', 'limits' => ['min' => 35, 'max' => 65]],
                         'pressure' => ['icon' => 'tachometer-alt', 'color' => 'green', 'unit' => 'hPa', 'limits' => ['min' => 990, 'max' => 1010]]] as $type => $config)
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center space-x-4">
                            <i class="fas fa-{{ $config['icon'] }} text-3xl text-{{ $config['color'] }}-500"></i>
                            <div>
                                <p class="text-sm font-medium text-gray-500 capitalize">{{ $type }}</p>
                                <h3 class="text-2xl font-bold">
                                    {{ $readingsByType[$type]['avg_value'] }}{{ $config['unit'] }}
                                </h3>
                            </div>
                        </div>
                        @if($readingsByType[$type]['excursions'] > 0)
                        <span class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-800 rounded-full">
                            {{ $readingsByType[$type]['excursions'] }} Excursions
                        </span>
                        @else
                        <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full">
                            Normal
                        </span>
                        @endif
                    </div>
                    <div class="space-y-2 text-sm text-gray-600">
                        <div class="flex justify-between">
                            <span>Minimum:</span>
                            <span class="{{ $readingsByType[$type]['min_value'] < $config['limits']['min'] ? 'text-red-600 font-semibold' : '' }}">
                                {{ $readingsByType[$type]['min_value'] }}{{ $config['unit'] }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span>Maximum:</span>
                            <span class="{{ $readingsByType[$type]['max_value'] > $config['limits']['max'] ? 'text-red-600 font-semibold' : '' }}">
                                {{ $readingsByType[$type]['max_value'] }}{{ $config['unit'] }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span>Standard Dev:</span>
                            <span>{{ $readingsByType[$type]['std_dev'] }}{{ $config['unit'] }}</span>
                                                </div>
                        <div class="text-xs text-gray-500 mt-2">
                            Acceptable range: {{ $config['limits']['min'] }}{{ $config['unit'] }} - {{ $config['limits']['max'] }}{{ $config['unit'] }}
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <!-- Excursion Alert -->
            @if($excursions->isNotEmpty())
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-8 rounded-lg">
                <div class="flex items-start">
                    <i class="fas fa-exclamation-triangle text-red-500 mr-3 mt-1"></i>
                    <div>
                        <h3 class="text-red-800 font-medium">Excursions Detected</h3>
                        <p class="text-red-700">{{ $excursions->count() }} excursions detected in this batch</p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Sensors Grid -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-8">
                @foreach($latestSensorReadings as $reading)
                <div class="bg-white rounded-lg shadow p-4 cursor-pointer hover:shadow-lg transition-shadow"
                     onclick="showSensorDetails('{{ $reading['sensor_id'] }}')">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="font-semibold">{{ $reading['sensor_id'] }}</h3>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full 
                            {{ $reading['has_excursion'] ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                            {{ $reading['has_excursion'] ? 'Excursion' : 'Normal' }}
                        </span>
                    </div>
                    <p class="text-gray-600">{{ ucfirst($reading['reading_type']) }}</p>
                    <p class="text-lg font-semibold">
                        {{ $reading['value'] }}
                        @if($reading['reading_type'] === 'temperature')°C
                        @elseif($reading['reading_type'] === 'humidity')%
                        @else hPa
                        @endif
                    </p>
                    <p class="text-xs text-gray-500 mt-2">
                        {{ Carbon\Carbon::parse($reading['reading_timestamp'])->diffForHumans() }}
                    </p>
                </div>
                @endforeach
            </div>

            <!-- Sensor Charts -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                @foreach(['temperature', 'humidity', 'pressure'] as $type)
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold mb-4 capitalize">{{ $type }} Distribution</h3>
                    <canvas id="{{ $type }}Chart" height="200"></canvas>
                </div>
                @endforeach
            </div>

            <!-- Readings Table -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6">
                    <h2 class="text-xl font-bold mb-4">Reading History</h2>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b">
                                    <th class="py-3 px-4 text-left">Time</th>
                                    <th class="py-3 px-4 text-left">Sensor</th>
                                    <th class="py-3 px-4 text-left">Type</th>
                                    <th class="py-3 px-4 text-left">Value</th>
                                    <th class="py-3 px-4 text-left">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($paginatedReadings as $reading)
                                <tr class="border-b {{ $reading['has_excursion'] ? 'bg-red-50' : '' }}">
                                    <td class="py-3 px-4">
                                        {{ Carbon\Carbon::parse($reading['reading_timestamp'])->format('Y-m-d H:i:s') }}
                                    </td>
                                    <td class="py-3 px-4">{{ $reading['sensor_id'] }}</td>
                                    <td class="py-3 px-4 capitalize">{{ $reading['reading_type'] }}</td>
                                    <td class="py-3 px-4">
                                        {{ $reading['value'] }}
                                        @if($reading['reading_type'] === 'temperature')°C
                                        @elseif($reading['reading_type'] === 'humidity')%
                                        @else hPa
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                            {{ $reading['has_excursion'] ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                            {{ $reading['has_excursion'] ? 'Excursion' : 'Normal' }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $paginatedReadings->links() }}
                    </div>
                </div>
            </div>

            <!-- Sensor Details Modal -->
            <div id="sensorModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
                <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold" id="modalTitle">Sensor Details</h3>
                        <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div id="modalContent" class="max-h-[60vh] overflow-y-auto">
                        <!-- Content will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Initialize charts
    <script>
    function createDistributionChart(elementId, readings, type, unit) {
        const ctx = document.getElementById(elementId).getContext('2d');
        const values = readings.map(r => r.value);
        
        // Calculate distribution bins
        const binCount = 10;
        const min = Math.min(...values);
        const max = Math.max(...values);
        const binSize = (max - min) / binCount;
        const bins = Array(binCount).fill(0);
        
        values.forEach(value => {
            const binIndex = Math.min(Math.floor((value - min) / binSize), binCount - 1);
            bins[binIndex]++;
        });

        const labels = bins.map((_, i) => {
            const start = (min + (i * binSize)).toFixed(1);
            const end = (min + ((i + 1) * binSize)).toFixed(1);
            return `${start}-${end}${unit}`;
        });

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: `${type} Distribution`,
                    data: bins,
                    backgroundColor: type === 'temperature' ? '#fecaca' : 
                                   type === 'humidity' ? '#bfdbfe' : '#bbf7d0',
                    borderColor: type === 'temperature' ? '#ef4444' : 
                                type === 'humidity' ? '#3b82f6' : '#22c55e',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Count'
                        }
                    }
                }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const readingsByType = @json($readingsByType);
        
        createDistributionChart('temperatureChart', readingsByType.temperature.readings, 'Temperature', '°C');
        createDistributionChart('humidityChart', readingsByType.humidity.readings, 'Humidity', '%');
        createDistributionChart('pressureChart', readingsByType.pressure.readings, 'Pressure', 'hPa');
    });
    </script>

    function showSensorDetails(sensorId) {
        const modal = document.getElementById('sensorModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalContent = document.getElementById('modalContent');

        modalTitle.textContent = `Sensor ${sensorId} Readings`;
        modalContent.innerHTML = '<div class="flex justify-center"><i class="fas fa-spinner fa-spin text-3xl text-blue-500"></i></div>';
        modal.classList.remove('hidden');

        fetch(`/batches/{{ $batchId }}/sensors/${sensorId}`)
            .then(response => response.json())
            .then(data => {
                let html = `
                    <div class="space-y-4">
                        ${data.readings.map(reading => `
                            <div class="border rounded-lg p-4 ${reading.has_excursion ? 'bg-red-50 border-red-200' : 'bg-gray-50'}">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="text-sm text-gray-500">${new Date(reading.reading_timestamp).toLocaleString()}</p>
                                        <p class="text-lg font-semibold">
                                            ${reading.value}${reading.reading_type === 'temperature' ? '°C' : 
                                                            reading.reading_type === 'humidity' ? '%' : ' hPa'}
                                        </p>
                                    </div>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                        ${reading.has_excursion ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'}">
                                        ${reading.has_excursion ? 'Excursion' : 'Normal'}
                                    </span>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                `;
                modalContent.innerHTML = html;
            })
            .catch(error => {
                modalContent.innerHTML = `<p class="text-red-500">Error loading sensor data: ${error.message}</p>`;
            });
    }

    function closeModal() {
        document.getElementById('sensorModal').classList.add('hidden');
    }

    // Close modal when clicking outside
    document.getElementById('sensorModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });

    // Auto-refresh every 30 seconds
    setTimeout(function() {
        window.location.reload();
    }, 30000);
    </script>
</body>
</html>