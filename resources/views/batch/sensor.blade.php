@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    {{-- Header Section --}}
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <a href="{{ route('batches.show', $batchId) }}" class="text-blue-500 hover:text-blue-700 mb-2 inline-flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to Batch
                </a>
                <h1 class="text-2xl font-bold text-gray-900">Sensor {{ explode('_', $sensorId)[1] }}</h1>
                <p class="text-sm text-gray-500 mt-1 last-updated">Last Updated: {{ $lastUpdated->diffForHumans() }}</p>
            </div>
            <button id="refreshData" class="flex items-center px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 refresh-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Refresh Data
            </button>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="text-sm font-medium text-gray-500">Current Temperature</h3>
            <p class="text-2xl font-bold current-temp">{{ number_format($latestReading['value'], 1) }}°C</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="text-sm font-medium text-gray-500">Min Temperature</h3>
            <p class="text-2xl font-bold min-temp">{{ number_format($stats['min'], 1) }}°C</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="text-sm font-medium text-gray-500">Max Temperature</h3>
            <p class="text-2xl font-bold max-temp">{{ number_format($stats['max'], 1) }}°C</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="text-sm font-medium text-gray-500">Average Temperature</h3>
            <p class="text-2xl font-bold avg-temp">{{ number_format($stats['avg'], 1) }}°C</p>
        </div>
    </div>

    {{-- Temperature Chart --}}
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-4 border-b">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 sm:mb-0">Temperature History</h2>
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

    {{-- Readings Table --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-4 py-3 border-b">
            <h2 class="text-lg font-semibold text-gray-900">Reading History</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Temperature</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($readings as $reading)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($reading['timestamp'])->format('Y-m-d H:i:s') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ number_format($reading['value'], 1) }}°C
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
            {{ $readings->links() }}
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
    
    // Initialize chart
    function initChart(data) {
        console.log('Chart Data', data);
        const ctx = document.getElementById('temperature-chart').getContext('2d');
        
        const chartData = Object.values(data);

        if (temperatureChart) {
            temperatureChart.destroy();
        }

        temperatureChart = new Chart(ctx, {
        type: 'line',
        data: {
            datasets: [{
                label: 'Temperature',
                data: chartData.map(r => ({
                    x: new Date(r.timestamp),
                    y: r.value
                })),
                borderColor: '#3b82f6',
                tension: 0.4,
                borderWidth: 2,
                pointRadius: 0,
                pointHoverRadius: 4
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
                        display: false
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

    // Initialize chart with initial data
    initChart(@json($readings->items()));

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
        
        button.prop('disabled', true);
        icon.addClass('animate-spin');
        
        $.ajax({
            url: window.location.href,
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                // Update stats
                $('.current-temp').text(`${response.latestReading.value.toFixed(1)}°C`);
                $('.min-temp').text(`${response.stats.min.toFixed(1)}°C`);
                $('.max-temp').text(`${response.stats.max.toFixed(1)}°C`);
                $('.avg-temp').text(`${response.stats.avg.toFixed(1)}°C`);

                // Update chart
                initChart(response.readings);

                // Update last updated time
                $('.last-updated').text(`Last Updated: ${new Date().toLocaleTimeString()}`);
            },
            error: function(xhr, status, error) {
                console.error('Error refreshing data:', error);
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