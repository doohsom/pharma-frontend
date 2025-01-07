{{-- resources/views/batch/show.blade.php --}}
@extends('layouts.app')

@push('css')
<style>
    .sensor-card {
        transition: all 0.3s ease;
    }
    .sensor-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
    }
    .loading {
        opacity: 0.7;
        pointer-events: none;
    }
</style>
@endpush

@section('content')
<div class="container mx-auto px-4 py-6">
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Batch #{{ $batchId }}</h1>
            <p class="text-sm text-gray-500 mt-1 last-updated">Last Updated: {{ $lastUpdated->diffForHumans() }}</p>
        </div>
        <div class="mt-4 md:mt-0">
            <button id="refreshData" class="flex items-center px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 refresh-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Refresh Data
            </button>
        </div>
    </div>

    {{-- Sensors Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 mb-6">
        @foreach($latestSensorReadings as $reading)
        {{-- Update your sensor card in the Sensors Grid section to be clickable --}}
        <div class="sensor-card bg-white rounded-lg shadow p-4 cursor-pointer hover:bg-gray-50" 
        data-sensor-id="{{ $reading['sensorId'] }}"
        onclick="window.location.href='{{ route('batch.sensor', ['batchId' => $batchId, 'sensorId' => $reading['sensorId']]) }}'">
            <div class="flex justify-between items-center mb-2">
                <h3 class="text-lg font-semibold text-gray-900">
                    Sensor {{ explode('_', $reading['sensorId'])[1] }}
                </h3>
                <div class="status-indicator {{ $reading['hasExcursion'] ? 'text-red-500' : 'text-green-500' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="{{ $reading['hasExcursion'] 
                                  ? 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'
                                  : 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' }}" />
                    </svg>
                </div>
            </div>
            <div class="text-3xl font-bold mb-2 temperature-value">
                {{ number_format($reading['value'], 1) }}°C
            </div>
            <div class="text-sm text-gray-500 timestamp">
                {{ \Carbon\Carbon::parse($reading['timestamp'])->format('H:i:s') }}
            </div>
        </div>
        @endforeach
    </div>

    {{-- Chart Section --}}
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-4 border-b">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 sm:mb-0">Temperature Trends</h2>
                <div class="flex space-x-2">
                    <button data-range="1h" class="px-3 py-1 rounded bg-blue-500 text-white time-range-btn">1h</button>
                    <button data-range="6h" class="px-3 py-1 rounded bg-gray-100 time-range-btn">6h</button>
                    <button data-range="24h" class="px-3 py-1 rounded bg-gray-100 time-range-btn">24h</button>
                </div>
            </div>
        </div>
        <div class="p-4">
            <div class="h-96">
                <canvas id="temperature-chart"></canvas>
            </div>
        </div>
    </div>

    {{-- Excursions Alert --}}
    @if($excursions->count() > 0)
    <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg mb-6 excursions-alert">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-red-800">
                    <span class="excursions-count">{{ $excursions->count() }}</span> temperature excursions detected
                </p>
            </div>
        </div>
    </div>
    @endif

    {{-- Readings Table --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-4 py-3 border-b">
            <h2 class="text-lg font-semibold text-gray-900">Recent Readings</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sensor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Temperature</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="readings-tbody">
                    @foreach($paginatedReadings as $reading)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ explode('_', $reading['sensorId'])[1] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ number_format($reading['value'], 1) }}°C
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($reading['timestamp'])->format('H:i:s') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $reading['hasExcursion'] ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                {{ $reading['hasExcursion'] ? 'Excursion' : 'Normal' }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $paginatedReadings->links() }}
        </div>
    </div>
</div>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>

<script>
$(document).ready(function() {
    let temperatureChart = null;
    let currentTimeRange = '1h';
    const colors = ['#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#6366f1', '#ec4899', '#8b5cf6'];
    
    // Initialize chart
    function initChart(data) {
        const ctx = document.getElementById('temperature-chart').getContext('2d');
        const sensorGroups = groupBySensor(data);
        
        if (temperatureChart) {
            temperatureChart.destroy();
        }

        temperatureChart = new Chart(ctx, {
            type: 'line',
            data: {
                datasets: Object.entries(sensorGroups).map(([sensorId, readings], index) => ({
                    label: `Sensor ${sensorId.split('_')[1]}`,
                    data: readings.map(r => ({
                        x: new Date(r.timestamp),
                        y: r.value
                    })),
                    borderColor: colors[index % colors.length],
                    tension: 0.4,
                    borderWidth: 2,
                    pointRadius: 0,
                    pointHoverRadius: 4
                }))
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
                            title: (context) => {
                                return new Date(context[0].raw.x).toLocaleString();
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
                                minute: 'HH:mm'
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
                            text: 'Temperature (°C)'
                        }
                    }
                }
            }
        });
    }

    // Helper function to group readings by sensor
    function groupBySensor(readings) {
        return readings.reduce((acc, reading) => {
            if (!acc[reading.sensorId]) {
                acc[reading.sensorId] = [];
            }
            acc[reading.sensorId].push(reading);
            return acc;
        }, {});
    }

    // Initialize chart with initial data
    initChart(@json($paginatedReadings->items()));

    // Handle time range selection
    $('.time-range-btn').click(function() {
        $('.time-range-btn').removeClass('bg-blue-500 text-white').addClass('bg-gray-100');
        $(this).addClass('bg-blue-500 text-white').removeClass('bg-gray-100');
        
        currentTimeRange = $(this).data('range');
        updateChartTimeRange(currentTimeRange);
    });

    // Update chart time range
    function updateChartTimeRange(range) {
        const now = new Date();
        let startTime;

        switch(range) {
            case '1h':
                startTime = new Date(now - 3600000);
                break;
            case '6h':
                startTime = new Date(now - 21600000);
                break;
            case '24h':
                startTime = new Date(now - 86400000);
                break;
        }

        temperatureChart.options.scales.x.min = startTime;
        temperatureChart.options.scales.x.max = now;
        temperatureChart.update();
    }

    // Handle refresh button click
    $('#refreshData').click(function() {
        const button = $(this);
        const icon = button.find('.refresh-icon');
        
        // Add loading state
        button.prop('disabled', true);
        icon.addClass('animate-spin');
        
        $.ajax({
            url: window.location.href,
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                // Update sensor cards
                response.latestSensorReadings.forEach(reading => {
                    const card = $(`.sensor-card[data-sensor-id="${reading.sensorId}"]`);
                    card.find('.temperature-value').text(`${reading.value.toFixed(1)}°C`);
                    card.find('.timestamp').text(new Date(reading.timestamp).toLocaleTimeString());
                    
                    const newStatusClass = reading.hasExcursion ? 'text-red-500' : 'text-green-500';
                    card.find('.status-indicator')
                        .removeClass('text-red-500 text-green-500')
                        .addClass(newStatusClass);
                });

                // Update chart data
                initChart(response.paginatedReadings);

                // Update excursions alert
                const excursionsCount = response.excursions.length;
                if (excursionsCount > 0) {
                    $('.excursions-alert').removeClass('hidden')
                        .find('.excursions-count').text(excursionsCount);
                } else {
                    $('.excursions-alert').addClass('hidden');
                }

                // Update table data
                let tableHtml = '';
                response.paginatedReadings.forEach(reading => {
                    tableHtml += `
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                ${reading.sensorId.split('_')[1]}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                ${reading.value.toFixed(1)}°C
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                ${new Date(reading.timestamp).toLocaleTimeString()}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    ${reading.hasExcursion ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'}">
                                    ${reading.hasExcursion ? 'Excursion' : 'Normal'}
                                </span>
                            </td>
                        </tr>
                    `;
                });
                $('#readings-tbody').html(tableHtml);

                // Update last updated time
                $('.last-updated').text(`Last Updated: ${new Date().toLocaleTimeString()}`);
            },
            error: function(xhr, status, error) {
                console.error('Error refreshing data:', error);
                // Show error notification
                const errorHtml = `
                    <div class="fixed bottom-4 right-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-lg error-notification">
                        <p class="font-bold">Error</p>
                        <p>Failed to refresh data. Please try again.</p>
                    </div>
                `;
                $('body').append(errorHtml);
                setTimeout(() => $('.error-notification').remove(), 5000);
            },
            complete: function() {
                // Remove loading state
                button.prop('disabled', false);
                icon.removeClass('animate-spin');
            }
        });
    });

    // Auto refresh every 30 seconds
    setInterval(function() {
        $('#refreshData').click();
    }, 30000);

    // Handle error notification dismissal
    $(document).on('click', '.error-notification', function() {
        $(this).remove();
    });
});
</script>
@endpush
@endsection