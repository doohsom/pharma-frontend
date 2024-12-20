<!-- resources/views/dashboard/index.blade.php -->
@extends('layouts.app')

@section('styles')
<style>
    .metric-card {
        @apply bg-white rounded-lg shadow p-6;
    }
    .metric-title {
        @apply text-lg font-semibold text-gray-700;
    }
    .metric-value {
        @apply text-3xl font-bold mt-2;
    }
    .metric-subvalue {
        @apply text-sm mt-1;
    }
    .alert-item {
        @apply flex items-center p-3 rounded-lg mb-2;
    }
    .alert-icon {
        @apply w-6 h-6 mr-3;
    }
</style>
@endsection

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold">Unified Dashboard</h1>
        <p class="text-gray-600">Welcome back, {{ Auth::user()->name }} ({{ Auth::user()->role }})</p>
    </div>

    <!-- Overview Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
        <!-- Users Metric -->
        <div class="metric-card">
            <div class="flex justify-between items-center">
                <h3 class="metric-title">Users</h3>
                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
            <p class="metric-value">{{ $generalStats['total_metrics']['users'] }}</p>
            <p class="metric-subvalue text-gray-500">{{ $generalStats['active_metrics']['active_users'] }} active</p>
        </div>

        <!-- Products Metric -->
        <div class="metric-card">
            <div class="flex justify-between items-center">
                <h3 class="metric-title">Products</h3>
                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            <p class="metric-value">{{ $generalStats['total_metrics']['products'] }}</p>
            <p class="metric-subvalue text-red-500">{{ $generalStats['alerts']['low_stock'] }} low stock</p>
        </div>

        <!-- Orders Metric -->
        <div class="metric-card">
            <div class="flex justify-between items-center">
                <h3 class="metric-title">Orders</h3>
                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
            </div>
            <p class="metric-value">{{ $generalStats['total_metrics']['orders'] }}</p>
            <p class="metric-subvalue text-blue-500">{{ $generalStats['active_metrics']['pending_orders'] }} pending</p>
        </div>

        <!-- Batches Metric -->
        <div class="metric-card">
            <div class="flex justify-between items-center">
                <h3 class="metric-title">Batches</h3>
                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </div>
            <p class="metric-value">{{ $generalStats['total_metrics']['batches'] }}</p>
            <p class="metric-subvalue text-yellow-500">{{ $generalStats['alerts']['expiring_batches'] }} expiring soon</p>
        </div>

        <!-- Sensors Metric -->
        <div class="metric-card">
            <div class="flex justify-between items-center">
                <h3 class="metric-title">Sensors</h3>
                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                </svg>
            </div>
            <p class="metric-value">{{ $generalStats['total_metrics']['sensors'] }}</p>
            <p class="metric-subvalue text-green-500">{{ $generalStats['active_metrics']['active_sensors'] }} active</p>
        </div>
    </div>

    <!-- Alerts Section -->
    @if(array_sum($generalStats['alerts']) > 0)
    <div class="mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Active Alerts</h2>
            <!-- Low Stock Alert -->
            @if($generalStats['alerts']['low_stock'] > 0)
            <div class="alert-item bg-yellow-50">
                <svg class="alert-icon text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <span class="text-yellow-600">{{ $generalStats['alerts']['low_stock'] }} products with low stock</span>
            </div>
            @endif

            <!-- Expiring Batches Alert -->
            @if($generalStats['alerts']['expiring_batches'] > 0)
            <div class="alert-item bg-orange-50">
                <svg class="alert-icon text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-orange-600">{{ $generalStats['alerts']['expiring_batches'] }} batches expiring soon</span>
            </div>
            @endif

            <!-- Sensor Alerts -->
            @if($generalStats['alerts']['sensor_alerts'] > 0)
            <div class="alert-item bg-red-50">
                <svg class="alert-icon text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <span class="text-red-600">{{ $generalStats['alerts']['sensor_alerts'] }} sensors reporting errors</span>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Role-Specific Metrics -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        @if(Auth::user()->role === 'manufacturer')
            <!-- Production Overview -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Production Overview</h2>
                <div class="space-y-4">
                    <div class="flex justify-between">
                        <span>Daily Production:</span>
                        <span class="font-bold">{{ $roleSpecificStats['production']['daily_production'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Weekly Production:</span>
                        <span class="font-bold">{{ $roleSpecificStats['production']['weekly_production'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Monthly Production:</span>
                        <span class="font-bold">{{ $roleSpecificStats['production']['monthly_production'] }}</span>
                    </div>
                </div>
            </div>

            <!-- Quality Metrics -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Quality Metrics</h2>
                <div class="space-y-4">
                    @foreach($roleSpecificStats['quality']['sensor_readings'] as $reading)
                    <div class="flex justify-between">
                        <span class="capitalize">{{ $reading->type }}:</span>
                        <span class="font-bold">{{ number_format($reading->average_reading, 2) }}</span>
                    </div>
                    @endforeach
                    <div class="flex justify-between text-red-600">
                        <span>Quality Alerts:</span>
                        <span class="font-bold">{{ $roleSpecificStats['quality']['quality_alerts'] }}</span>
                    </div>
                </div>
            </div>
        @endif

        @if(Auth::user()->role === 'regulator')
            <!-- Compliance Overview -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Compliance Overview</h2>
                <div class="space-y-4">
                    <div class="flex justify-between text-red-600">
                        <span>Expired Batches:</span>
                        <span class="font-bold">{{ $roleSpecificStats['compliance']['expired_batches'] }}</span>
                    </div>
                    <div class="flex justify-between text-yellow-600">
                        <span>Non-compliant Sensors:</span>
                        <span class="font-bold">{{ $roleSpecificStats['compliance']['non_compliant_sensors'] }}</span>
                    </div>
                    <div class="flex justify-between text-orange-600">
                        <span>Quality Violations (30d):</span>
                        <span class="font-bold">{{ $roleSpecificStats['compliance']['quality_violations'] }}</span>
                    </div>
                </div>
            </div>

            <!-- Audit Status -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Audit Status</h2>
                <div class="space-y-4">
                    @foreach($roleSpecificStats['auditing']['batches_by_status'] as $status)
                    <div class="flex justify-between">
                        <span class="capitalize">{{ str_replace('_', ' ', $status->status) }}:</span>
                        <span class="font-bold">{{ $status->count }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if(Auth::user()->role === 'vendor')
            <!-- Inventory Status -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Inventory Status</h2>
                <div class="space-y-4">
                    <h3 class="font-semibold">Low Stock Items</h3>
                    @foreach($roleSpecificStats['inventory']['low_stock_items'] as $item)
                    <div class="flex justify-between text-red-600">
                        <span>{{ $item->name }}:</span>
                        <span class="font-bold">{{ $item->stock }} units</span>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Recent Orders</h2>
                <div class="space-y-4">
                    @foreach($roleSpecificStats['orders']['recent_orders'] as $order)
                    <div class="flex justify-between">
                        <span>Order #{{ $order->id }}:</span>
                        <span class="font-bold capitalize">{{ $order->status }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if(Auth::user()->role === 'logistics')
            <!-- Shipment Overview -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Shipment Overview</h2>
                <div class="space-y-4">
                    <div class="flex justify-between">
                        <span>Pending Shipments:</span>
                        <span class="font-bold">{{ $roleSpecificStats['shipments']['pending_shipments'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>In Transit:</span>
                        <span class="font-bold">{{ $roleSpecificStats['shipments']['in_transit'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Delivered:</span>
                        <span class="font-bold">{{ $roleSpecificStats['shipments']['delivered'] }}</span>
                    </div>
                </div>
            </div>

            <!-- Delivery Performance -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Delivery Performance</h2>
                <div class="space-y-4">
                    <div class="flex justify-between text-green-600">
                        <span>On-time Deliveries:</span>
                        <span class="font-bold">{{ $roleSpecificStats['tracking']['delivery_performance']['on_time'] }}</span>
                    </div>
                    <div class="flex justify-between text-red-600">
                        <span>Delayed Deliveries:</span>
                        <span class="font-bold">{{ $roleSpecificStats['tracking']['delivery_performance']['delayed'] }}</span>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Monthly Trends -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Monthly Trends</h2>
            <div class="h-64" id="monthlyTrendsChart"></div>
        </div>

        <!-- Status Distribution -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Status Distribution</h2>
            <div class="h-64" id="statusDistributionChart"></div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Monthly Trends Chart
    const monthlyTrendsCtx = document.getElementById('monthlyTrendsChart').getContext('2d');
    new Chart(monthlyTrendsCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode(array_column($chartData['monthly_trends']['orders'], 'month')) !!},
            datasets: [{
                label: 'Orders',
                data: {!! json_encode(array_column($chartData['monthly_trends']['orders'], 'count')) !!},
                borderColor: 'rgb(59, 130, 246)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Status Distribution Chart
    const statusDistributionCtx = document.getElementById('statusDistributionChart').getContext('2d');
    new Chart(statusDistributionCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_column($chartData['status_distribution']['batches'], 'status')) !!},
            datasets: [{
                label: 'Batches',
                data: {!! json_encode(array_column($chartData['status_distribution']['batches'], 'count')) !!},
                backgroundColor: [
                    'rgba(59, 130, 246, 0.5)',
                    'rgba(16, 185, 129, 0.5)',
                    'rgba(245, 158, 11, 0.5)',
                    'rgba(239, 68, 68, 0.5)'
                ],
                borderColor: [
                    'rgb(59, 130, 246)',
                    'rgb(16, 185, 129)',
                    'rgb(245, 158, 11)',
                    'rgb(239, 68, 68)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
@endsection