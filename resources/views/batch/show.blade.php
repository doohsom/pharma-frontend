@extends('layouts.app')

@section('styles')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.css" rel="stylesheet">
@endsection

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <a href="{{ url('/batches') }}" class="text-blue-500 hover:text-blue-600 mb-2 inline-flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Batches
                </a>
                <h1 class="text-3xl font-bold text-gray-900">Batch {{ $batchId }}</h1>
            </div>
            <div class="flex items-center gap-4">
                <p class="text-sm text-gray-500" id="lastUpdated">
                    Last updated: {{ $lastUpdated->diffForHumans() }}
                </p>
                <button onclick="refreshData()" 
                        class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors"
                        id="refreshButton">
                    <i class="fas fa-sync-alt mr-2"></i> Refresh Data
                </button>
            </div>
        </div>

        <!-- Excursions Alert -->
        @if(count($excursions) > 0)
        <div class="mb-8">
            <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
                <div class="flex items-start">
                    <i class="fas fa-exclamation-triangle text-red-500 mr-3 mt-1"></i>
                    <div>
                        <h3 class="text-red-800 font-medium">Excursions Detected</h3>
                        <p class="text-red-700">{{ count($excursions) }} excursions detected in this batch</p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Metrics Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            @foreach(['temperature' => ['icon' => 'thermometer-half', 'color' => 'red', 'unit' => '°C', 'limits' => ['min' => 2, 'max' => 8]],
                     'humidity' => ['icon' => 'tint', 'color' => 'blue', 'unit' => '%', 'limits' => ['min' => 35, 'max' => 65]],
                     'pressure' => ['icon' => 'tachometer-alt', 'color' => 'green', 'unit' => 'hPa', 'limits' => ['min' => 990, 'max' => 1010]]] 
                     as $type => $config)
            <div class="bg-white rounded-lg shadow p-6" id="metric-{{ $type }}">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-4">
                        <i class="fas fa-{{ $config['icon'] }} text-3xl text-{{ $config['color'] }}-500"></i>
                        <div>
                            <p class="text-sm font-medium text-gray-500 capitalize">{{ $type }}</p>
                            <h3 class="text-2xl font-bold">
                                {{ isset($readingsByType[$type]['current_value']) ? number_format($readingsByType[$type]['current_value'], 1) . $config['unit'] : '--' }}
                            </h3>
                        </div>
                    </div>
                    @if(isset($readingsByType[$type]['current_value']))
                        @php
                            $value = $readingsByType[$type]['current_value'];
                            $hasExcursion = $value < $config['limits']['min'] || $value > $config['limits']['max'];
                        @endphp
                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $hasExcursion ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                            {{ $hasExcursion ? 'Excursion' : 'Normal' }}
                        </span>
                    @else
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                            No Data
                        </span>
                    @endif
                </div>
                @if(isset($readingsByType[$type]['current_value']))
                    <div class="space-y-2 text-sm text-gray-600">
                        <div class="flex justify-between">
                            <span>Range:</span>
                            <span>{{ number_format($readingsByType[$type]['min_value'], 1) }}{{ $config['unit'] }} - {{ number_format($readingsByType[$type]['max_value'], 1) }}{{ $config['unit'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Average:</span>
                            <span>{{ number_format($readingsByType[$type]['avg_value'], 1) }}{{ $config['unit'] }}</span>
                        </div>
                        <div class="text-xs text-gray-500 mt-2">
                            Acceptable range: {{ $config['limits']['min'] }}{{ $config['unit'] }} - {{ $config['limits']['max'] }}{{ $config['unit'] }}
                        </div>
                    </div>
                @endif
            </div>
            @endforeach
        </div>

        <!-- Charts -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h3 class="text-lg font-semibold mb-4">Temperature Distribution</h3>
            <canvas id="temperatureChart" class="w-full" style="height: 300px;"></canvas>
        </div>

        <!-- Readings Table -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6">
                <h2 class="text-xl font-bold mb-4">Reading History</h2>
                <div class="overflow-x-auto">
                    <table class="w-full" id="readingsTable">
                        <thead>
                            <tr class="border-b">
                                <th class="py-3 px-4 text-left">Time</th>
                                <th class="py-3 px-4 text-left">Sensor</th>
                                <th class="py-3 px-4 text-left">Value</th>
                                <th class="py-3 px-4 text-left">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($paginatedReadings['data'] as $reading)
                            <tr class="border-b {{ $reading['hasExcursion'] ? 'bg-red-50' : '' }} hover:bg-gray-50">
                                <td class="py-3 px-4">{{ \Carbon\Carbon::parse($reading['timestamp'])->format('Y-m-d H:i:s') }}</td>
                                <td class="py-3 px-4">{{ $reading['sensorId'] }}</td>
                                <td class="py-3 px-4">{{ number_format($reading['decryptedValue'], 1) }}</td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $reading['hasExcursion'] ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                        {{ $reading['hasExcursion'] ? 'Excursion' : 'Normal' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-gray-500">No readings available</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($paginatedReadings['total'] > $paginatedReadings['per_page'])
                <div class="mt-4 flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Showing {{ $paginatedReadings['from'] }} to {{ $paginatedReadings['to'] }} of {{ $paginatedReadings['total'] }} results
                    </div>
                    <div class="flex space-x-2">
                        @if($paginatedReadings['current_page'] > 1)
                            <button onclick="loadPage({{ $paginatedReadings['current_page'] - 1 }})" 
                                    class="px-3 py-1 bg-white border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">
                                Previous
                            </button>
                        @endif
                        
                        @if($paginatedReadings['current_page'] < $paginatedReadings['last_page'])
                            <button onclick="loadPage({{ $paginatedReadings['current_page'] + 1 }})"
                                    class="px-3 py-1 bg-white border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">
                                Next
                            </button>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
<script>
const CHART_COLORS = {
    temperature: {
        backgroundColor: 'rgba(239, 68, 68, 0.2)',
        borderColor: 'rgb(239, 68, 68)'
    },
    humidity: {
        backgroundColor: 'rgba(59, 130, 246, 0.2)',
        borderColor: 'rgb(59, 130, 246)'
    },
    pressure: {
        backgroundColor: 'rgba(34, 197, 94, 0.2)',
        borderColor: 'rgb(34, 197, 94)'
    }
};

const SENSOR_LIMITS = {
    temperature: { min: 2, max: 8, unit: '°C' },
    humidity: { min: 35, max: 65, unit: '%' },
    pressure: { min: 990, max: 1010, unit: 'hPa' }
};

let charts = {};

async function refreshData() {
    const button = document.getElementById('refreshButton');
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Refreshing...';

    try {
        const response = await fetch(`/api/batches/{{ $batchId }}/data`);
        if (!response.ok) throw new Error('Failed to fetch data');
        
        const data = await response.json();
        updateDashboard(data);
        
        document.getElementById('lastUpdated').textContent = 'Last updated: just now';
        showToast('Data refreshed successfully');
    } catch (error) {
        console.error('Error refreshing data:', error);
        showToast('Error refreshing data', 'error');
    } finally {
        button.disabled = false;
        button.innerHTML = '<i class="fas fa-sync-alt mr-2"></i> Refresh Data';
    }
}

function updateDashboard(data) {
    updateMetrics(data.readingsByType);
    updateCharts(data.readingsByType);
    updateTable(data.paginatedReadings);
}

function updateMetrics(readingsByType) {
    Object.entries(readingsByType).forEach(([type, data]) => {
        const card = document.getElementById(`metric-${type}`);
        if (!card) return;

        const hasExcursion = data.current_value < SENSOR_LIMITS[type].min || data.current_value > SENSOR_LIMITS[type].max;
        
        const html = `
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-4">
                    <i class="fas fa-${type === 'temperature' ? 'thermometer-half' : 
                              type === 'humidity' ? 'tint' : 
                              'tachometer-alt'} 
                       text-3xl text-${type === 'temperature' ? 'red' :
                                  type === 'humidity' ? 'blue' : 
                                  'green'}-500"></i>
                    <div>
                        <p class="text-sm font-medium text-gray-500 capitalize">${type}</p>
                        <h3 class="text-2xl font-bold">
                            ${data.current_value.toFixed(1)}${SENSOR_LIMITS[type].unit}
                        </h3>
                    </div>
                </div>
                <span class="px-2 py-1 text-xs font-semibold rounded-full
                    ${hasExcursion ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'}">
                    ${hasExcursion ? 'Excursion' : 'Normal'}
                </span>
            </div>
            <div class="space-y-2 text-sm text-gray-600">
                <div class="flex justify-between">
                    <span>Range:</span>
                    <span>${data.min_value.toFixed(1)}${SENSOR_LIMITS[type].unit} - ${data.max_value.toFixed(1)}${SENSOR_LIMITS[type].unit}</span>
                </div>
                <div class="flex justify-between">
                    <span>Average:</span>
                    <span>${data.avg_value.toFixed(1)}${SENSOR_LIMITS[type].unit}</span>
                </div>
                <div class="text-xs text-gray-500 mt-2">
                    Acceptable range: ${SENSOR_LIMITS[type].min}${SENSOR_LIMITS[type].unit} - ${SENSOR_LIMITS[type].max}${SENSOR_LIMITS[type].unit}
                </div>
            </div>
        `;

        card.innerHTML = html;
    });
}

function updateChart(readings) {
    if (!charts.temperature) return;

    const values = readings.map(r => r.value);
    if (values.length === 0) return;

    // Calculate bins
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
        return `${start}-${end}°C`;
    });

    charts.temperature.data.labels = labels;
    charts.temperature.data.datasets[0].data = bins;
    charts.temperature.update();
}
}

function updateTable(paginatedReadings) {
    const tbody = document.querySelector('#readingsTable tbody');
    if (!tbody) return;

    if (paginatedReadings.data.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="py-8 text-center text-gray-500">No readings available</td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = paginatedReadings.data.map(reading => `
        <tr class="border-b ${reading.hasExcursion ? 'bg-red-50' : ''} hover:bg-gray-50">
            <td class="py-3 px-4">${new Date(reading.timestamp).toLocaleString()}</td>
            <td class="py-3 px-4">${reading.sensorId}</td>
            <td class="py-3 px-4">${reading.decryptedValue.toFixed(1)}</td>
            <td class="py-3 px-4">
                <span class="px-2 py-1 text-xs font-semibold rounded-full 
                    ${reading.hasExcursion ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'}">
                    ${reading.hasExcursion ? 'Excursion' : 'Normal'}
                </span>
            </td>
        </tr>
    `).join('');

    // Update pagination
    const paginationDiv = document.querySelector('.pagination');
    if (paginationDiv && paginatedReadings.total > paginatedReadings.per_page) {
        paginationDiv.innerHTML = `
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Showing ${paginatedReadings.from} to ${paginatedReadings.to} of ${paginatedReadings.total} results
                </div>
                <div class="flex space-x-2">
                    ${paginatedReadings.current_page > 1 ? `
                        <button onclick="loadPage(${paginatedReadings.current_page - 1})" 
                                class="px-3 py-1 bg-white border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">
                            Previous
                        </button>
                    ` : ''}
                    
                    ${paginatedReadings.current_page < paginatedReadings.last_page ? `
                        <button onclick="loadPage(${paginatedReadings.current_page + 1})"
                                class="px-3 py-1 bg-white border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">
                            Next
                        </button>
                    ` : ''}
                </div>
            </div>
        `;
    }
}

async function loadPage(page) {
    try {
        const response = await fetch(`/api/batches/{{ $batchId }}/data?page=${page}`);
        if (!response.ok) throw new Error('Failed to fetch page');
        
        const data = await response.json();
        updateTable(data.paginatedReadings);
    } catch (error) {
        console.error('Error loading page:', error);
        showToast('Error loading page', 'error');
    }
}

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `fixed bottom-4 right-4 px-4 py-2 rounded-lg text-white ${
        type === 'error' ? 'bg-red-500' : 'bg-green-500'
    } transition-opacity duration-300 z-50`;
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Initialize chart
document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing temperature chart...');

    const canvas = document.getElementById('temperatureChart');
    if (!canvas) {
        console.error('Temperature chart canvas not found');
        return;
    }

    const ctx = canvas.getContext('2d');
    const data = @json($readingsByType['temperature']['readings'] ?? []);
    console.log('Temperature readings:', data);

    const values = data.map(r => r.value);
    if (values.length === 0) {
        console.warn('No temperature readings available');
        return;
    }

    // Calculate bins
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
        return `${start}-${end}°C`;
    });

    console.log('Chart data:', { labels, bins });

    charts.temperature = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Temperature Distribution',
                data: bins,
                backgroundColor: 'rgba(239, 68, 68, 0.2)',
                borderColor: 'rgb(239, 68, 68)',
                borderWidth: 1,
                barPercentage: 1,
                categoryPercentage: 0.9
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `Count: ${context.raw}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Number of Readings'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Temperature Range (°C)'
                    }
                }
            }
        }
    });

    console.log('Temperature chart initialized');
});
</script>
@endsection