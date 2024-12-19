<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cold Chain Monitoring Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-moment"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen p-8">
        <div class="max-w-7xl mx-auto">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-3xl font-bold">Cold Chain Monitoring Dashboard</h1>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-500">Last updated: {{ Carbon\Carbon::parse($latestReadings->first()['reading_timestamp'] ?? now())->diffForHumans() }}</span>
                    <a href="{{ route('dashboard.refresh') }}" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
                        <i class="fas fa-sync-alt mr-2"></i> Refresh Data
                    </a>
                </div>
            </div>

            <!-- Latest Readings Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                @foreach(['temperature' => ['icon' => 'thermometer-half', 'color' => 'red', 'unit' => '°C'],
                         'humidity' => ['icon' => 'tint', 'color' => 'blue', 'unit' => '%'],
                         'pressure' => ['icon' => 'tachometer-alt', 'color' => 'green', 'unit' => 'hPa']] as $type => $config)
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center space-x-4">
                        <i class="fas fa-{{ $config['icon'] }} text-3xl text-{{ $config['color'] }}-500"></i>
                        <div>
                            <p class="text-sm font-medium text-gray-500 capitalize">{{ $type }}</p>
                            <h3 class="text-2xl font-bold">
                                {{ $latestReadings[$type]['value'] ?? 'N/A' }}{{ $config['unit'] }}
                            </h3>
                            <p class="text-sm text-gray-500">
                                Batch: {{ $latestReadings[$type]['batch_id'] ?? 'N/A' }}
                            </p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Current Batch Summary -->
            @if(isset($latestBatch))
            <div class="bg-white rounded-lg shadow mb-8">
                <div class="p-6">
                    <h2 class="text-xl font-bold mb-4">Current Batch Details</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @foreach($latestBatch as $type => $readings)
                        <div class="border rounded-lg p-4 {{ $readings->contains('has_excursion', true) ? 'border-red-200 bg-red-50' : 'border-green-200 bg-green-50' }}">
                            <h3 class="text-lg font-semibold capitalize mb-2">{{ $type }}</h3>
                            <div class="space-y-2">
                                <p class="text-sm">Average: {{ $readings->avg('value') }}
                                    @if($type === 'temperature')°C
                                    @elseif($type === 'humidity')%
                                    @else hPa
                                    @endif
                                </p>
                                <p class="text-sm">Min: {{ $readings->min('value') }}</p>
                                <p class="text-sm">Max: {{ $readings->max('value') }}</p>
                                <p class="text-sm">Sensors: {{ $readings->count() }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Batch Summary -->
            <div class="bg-white rounded-lg shadow mb-8">
                <div class="p-6">
                    <h2 class="text-xl font-bold mb-4">Batch History</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($batchSummary as $batchId => $batch)
                        <div class="border rounded-lg p-4 {{ $batch['excursions'] > 0 ? 'border-red-200 bg-red-50' : 'border-green-200 bg-green-50' }}">
                            <div class="flex justify-between items-start mb-2">
                                <h3 class="text-lg font-semibold">{{ $batchId }}</h3>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $batch['excursions'] > 0 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                    {{ $batch['excursions'] > 0 ? $batch['excursions'] . ' Excursions' : 'Normal' }}
                                </span>
                            </div>
                            <div class="space-y-2">
                                <p class="text-sm text-gray-600">Time: {{ $batch['timestamp'] }}</p>
                                <p class="text-sm text-gray-600">Readings: {{ $batch['total_readings'] }}</p>
                                @foreach($batch['readings_by_type'] as $type => $data)
                                <div class="text-sm">
                                    <span class="capitalize">{{ $type }}:</span>
                                    {{ round($data['avg_value'], 2) }}
                                    @if($type === 'temperature')°C
                                    @elseif($type === 'humidity')%
                                    @else hPa
                                    @endif
                                    @if($data['excursions'] > 0)
                                    <span class="text-red-600 ml-1">({{ $data['excursions'] }} exc.)</span>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Excursion Alerts -->
            @if($excursions->isNotEmpty())
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-8 rounded-lg">
                <div class="flex">
                    <i class="fas fa-exclamation-triangle text-red-500 mr-3"></i>
                    <div>
                        <h3 class="text-red-800 font-medium">Excursions Detected</h3>
                        @foreach($excursions as $batch)
                        <div class="mt-2">
                            <p class="text-red-700">
                                Batch {{ $batch['batch_id'] }} ({{ Carbon\Carbon::parse($batch['timestamp'])->format('Y-m-d H:i:s') }}):
                                {{ $batch['readings']->count() }} excursions
                            </p>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Charts -->
            <div class="grid grid-cols-1 gap-6 mb-8">
                @foreach(['temperature' => ['color' => '#ef4444', 'unit' => '°C'],
                         'humidity' => ['color' => '#3b82f6', 'unit' => '%'],
                         'pressure' => ['color' => '#22c55e', 'unit' => 'hPa']] as $type => $config)
                <div class="bg-white rounded-lg shadow">
                    <div class="p-6">
                        <h2 class="text-xl font-bold mb-4 capitalize">{{ $type }} Trend</h2>
                        <div class="h-[300px]">
                            <canvas id="{{ $type }}Chart"></canvas>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Batch Statistics Chart -->
            <div class="bg-white rounded-lg shadow mb-8">
                <div class="p-6">
                    <h2 class="text-xl font-bold mb-4">Batch Statistics</h2>
                    <div class="h-[400px]">
                        <canvas id="batchChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
function createChart(elementId, data, label, color, unit) {
    const ctx = document.getElementById(elementId).getContext('2d');
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.map(d => d.timestamp),
            datasets: [{
                label: label,
                data: data.map(d => ({
                    x: d.timestamp,
                    y: d.avg_value,
                    min: d.min_value,
                    max: d.max_value,
                    batchId: d.batch_id
                })),
                borderColor: color,
                backgroundColor: color + '20',
                borderWidth: 2,
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            plugins: {
                legend: {
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const datapoint = context.raw;
                            return [
                                `Average: ${datapoint.y.toFixed(2)}${unit}`,
                                `Min: ${datapoint.min.toFixed(2)}${unit}`,
                                `Max: ${datapoint.max.toFixed(2)}${unit}`,
                                `Batch: ${datapoint.batchId}`
                            ];
                        }
                    }
                }
            },
            scales: {
                x: {
                    type: 'time',
                    time: {
                        unit: 'minute',
                        displayFormats: {
                            minute: 'MMM D, HH:mm'
                        }
                    },
                    title: {
                        display: true,
                        text: 'Time'
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: label
                    },
                    ticks: {
                        callback: function(value) {
                            return value + unit;
                        }
                    }
                }
            }
        }
    });
}

// Initialize batch statistics chart
function createBatchChart(data) {
    const ctx = document.getElementById('batchChart').getContext('2d');
    const batchIds = Object.keys(data);
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: batchIds,
            datasets: [
                {
                    label: 'Temperature (°C)',
                    data: batchIds.map(id => data[id].readings_by_type.temperature.avg_value),
                    backgroundColor: '#ef444480',
                    borderColor: '#ef4444',
                    borderWidth: 1
                },
                {
                    label: 'Humidity (%)',
                    data: batchIds.map(id => data[id].readings_by_type.humidity.avg_value),
                    backgroundColor: '#3b82f680',
                    borderColor: '#3b82f6',
                    borderWidth: 1
                },
                {
                    label: 'Pressure (hPa)',
                    data: batchIds.map(id => data[id].readings_by_type.pressure.avg_value),
                    backgroundColor: '#22c55e80',
                    borderColor: '#22c55e',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: false
                }
            }
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const chartData = @json($chartData);
    const batchSummary = @json($batchSummary);
    
    createChart('temperatureChart', chartData.temperature, 'Temperature', '#ef4444', '°C');
    createChart('humidityChart', chartData.humidity, 'Humidity', '#3b82f6', '%');
    createChart('pressureChart', chartData.pressure, 'Pressure', '#22c55e', ' hPa');
    createBatchChart(batchSummary);
});
</script>

</body>
</html>