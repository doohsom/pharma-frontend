{{-- resources/views/dashboard/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Cold Chain Monitoring Dashboard</h1>

    @if ($errors->any())
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
        <strong class="font-bold">Error!</strong>
        <span class="block sm:inline">{{ $errors->first() }}</span>
    </div>
    @endif

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        {{-- Temperature Card --}}
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center space-x-4">
                <svg class="h-10 w-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                <div>
                    <p class="text-sm font-medium text-gray-600">Average Temperature</p>
                    <h3 class="text-xl font-bold text-gray-900">
                        {{ number_format($avgTemp, 1) }}°C
                    </h3>
                </div>
            </div>
        </div>

        {{-- Humidity Card --}}
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center space-x-4">
                <svg class="h-10 w-10 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14.5v-5a2 2 0 00-2-2h-4l-3-3-3 3H3a2 2 0 00-2 2v5"/>
                </svg>
                <div>
                    <p class="text-sm font-medium text-gray-600">Average Humidity</p>
                    <h3 class="text-xl font-bold text-gray-900">
                        {{ number_format($avgHumidity, 1) }}%
                    </h3>
                </div>
            </div>
        </div>

        {{-- Pressure Card --}}
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center space-x-4">
                <svg class="h-10 w-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5M8 8v8m-4-5v5"/>
                </svg>
                <div>
                    <p class="text-sm font-medium text-gray-600">Average Pressure</p>
                    <h3 class="text-xl font-bold text-gray-900">
                        {{ number_format($avgPressure, 1) }} hPa
                    </h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Excursion Alert --}}
    @if($excursionCount > 0)
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
        <p class="font-bold">Storage Condition Excursions Detected</p>
        <p>{{ $excursionCount }} excursions detected in the recent readings. Immediate action may be required.</p>
    </div>
    @endif

    {{-- Charts Section --}}
    <div class="grid grid-cols-1 gap-6 mb-6">
        {{-- Temperature Chart --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Temperature Trend</h2>
            <div class="h-64">
                <canvas id="temperatureChart"></canvas>
            </div>
        </div>

        {{-- Humidity Chart --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Humidity Trend</h2>
            <div class="h-64">
                <canvas id="humidityChart"></canvas>
            </div>
        </div>

        {{-- Pressure Chart --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Pressure Trend</h2>
            <div class="h-64">
                <canvas id="pressureChart"></canvas>
            </div>
        </div>

        {{-- Readings History Table --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Sensor Readings History</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full table-auto">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="px-4 py-2">Timestamp</th>
                            <th class="px-4 py-2">Location</th>
                            <th class="px-4 py-2">Reading Type</th>
                            <th class="px-4 py-2">Value</th>
                            <th class="px-4 py-2">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sensorData as $reading)
                        <tr class="{{ $reading->hasExcursion ? 'bg-red-50' : '' }}">
                            <td class="border px-4 py-2">
                                {{ \Carbon\Carbon::parse($reading->timestamp)->format('Y-m-d H:i:s') }}
                            </td>
                            <td class="border px-4 py-2">{{ $reading->location}}</td>
                            <td class="border px-4 py-2">{{ ucfirst($reading->readingType) }}</td>
                            <td class="border px-4 py-2">
                                {{ number_format($reading->value, 1) }}
                                @switch($reading->readingType)
                                    @case('temperature')
                                        °C
                                        @break
                                    @case('humidity')
                                        %
                                        @break
                                    @case('pressure')
                                        hPa
                                        @break
                                @endswitch
                            </td>
                            <td class="border px-4 py-2">
                                @if($reading->hasExcursion)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Excursion
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Normal
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Debug the incoming data
    const sensorData = @json($sensorData);
    console.log('Raw sensor data:', sensorData);

    // Format the data for charts
    const formatChartData = (data, type) => {
        const formatted = data
            .filter(reading => reading.readingType === type)
            .map(reading => ({
                x: new Date(reading.timestamp),
                y: parseFloat(reading.value)
            }))
            .sort((a, b) => a.x - b.x);
        
        console.log(`Formatted ${type} data:`, formatted);
        return formatted;
    };

    // Temperature Chart
    const temperatureData = formatChartData(sensorData, 'temperature');
    const temperatureCtx = document.getElementById('temperatureChart');
    if (temperatureCtx && temperatureData.length > 0) {
        console.log('Creating temperature chart');
        new Chart(temperatureCtx, {
            type: 'line',
            data: {
                datasets: [{
                    label: 'Temperature (°C)',
                    data: temperatureData,
                    borderColor: 'rgb(239, 68, 68)',
                    tension: 0.1,
                    fill: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        type: 'time',
                        time: {
                            unit: 'hour'
                        }
                    },
                    y: {
                        beginAtZero: false
                    }
                }
            }
        });
    } else {
        console.log('Temperature chart element not found or no data');
    }

    // Humidity Chart
    const humidityCtx = document.getElementById('humidityChart');
    if (humidityCtx) {
        new Chart(humidityCtx, {
            type: 'line',
            data: {
                datasets: [{
                    label: 'Humidity (%)',
                    data: formatChartData(sensorData, 'humidity'),
                    borderColor: 'rgb(59, 130, 246)',
                    tension: 0.1,
                    fill: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        type: 'time',
                        time: {
                            unit: 'hour',
                            displayFormats: {
                                hour: 'MMM D, HH:mm'
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
                            text: 'Humidity (%)'
                        }
                    }
                }
            }
        });
    }

    // Pressure Chart
    const pressureCtx = document.getElementById('pressureChart');
    if (pressureCtx) {
        new Chart(pressureCtx, {
            type: 'line',
            data: {
                datasets: [{
                    label: 'Pressure (hPa)',
                    data: formatChartData(sensorData, 'pressure'),
                    borderColor: 'rgb(34, 197, 94)',
                    tension: 0.1,
                    fill: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        type: 'time',
                        time: {
                            unit: 'hour',
                            displayFormats: {
                                hour: 'MMM D, HH:mm'
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
                            text: 'Pressure (hPa)'
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush
@endsection