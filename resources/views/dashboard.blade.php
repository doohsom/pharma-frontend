<!-- resources/views/dashboard/index.blade.php -->
@extends('layouts.app')

@section('styles')
<style>
    .metric-card {
        @apply rounded-lg shadow-lg p-12 transition-all duration-300 hover:shadow-xl text-white;
    }
    .metric-title {
        @apply text-lg font-semibold mb-2;
    }
    .metric-value {
        @apply text-3xl font-bold mt-2;
    }
    .metric-subvalue {
        @apply text-sm mt-1 font-medium;
    }
    .alert-item {
        @apply flex items-center p-4 rounded-lg mb-3 transition-all duration-300 hover:shadow-md;
    }
    .alert-icon {
        @apply w-8 h-8 mr-4;
    }
    .chart-container {
        @apply bg-white rounded-lg shadow-lg p-6 mt-8;
    }
</style>
@endsection

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
        <h1 class="text-3xl font-bold"> Dashboard</h1>
        <p class="text-xl mt-2">Welcome back, {{ Auth::user()->name }} ({{ Auth::user()->role }})</p>
    </div>

    <!-- Overview Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <!-- Users Metric -->
        <div class="rounded-lg shadow-lg p-8 transition-all duration-300 hover:shadow-xl text-white bg-blue-500 border border-white/10">
            <div class="flex justify-between items-center">
                <h3 class="metric-title">Users</h3>
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
            <p class="metric-value">20</p>
            <p class="metric-subvalue">12 active</p>
        </div>

        <!-- Products Metric -->
        <div class="rounded-lg shadow-lg p-8 transition-all duration-300 hover:shadow-xl text-white bg-green-500 border border-white/10">
            <div class="flex justify-between items-center">
                <h3 class="metric-title">Products</h3>
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            <p class="metric-value">12</p>
            <p class="metric-subvalue">2 low stock</p>
        </div>

        <!-- Orders Metric -->
        <div class="rounded-lg shadow-lg p-8 transition-all duration-300 hover:shadow-xl text-white bg-purple-500 border border-white/10">
            <div class="flex justify-between items-center">
                <h3 class="metric-title">Orders</h3>
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
            </div>
            <p class="metric-value">30</p>
            <p class="metric-subvalue">12 pending</p>
        </div>

        <!-- Batches Metric -->
        <div class="rounded-lg shadow-lg p-8 transition-all duration-300 hover:shadow-xl text-white bg-yellow-500 border border-white/10">
            <div class="flex justify-between items-center">
                <h3 class="metric-title">Batches</h3>
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </div>
            <p class="metric-value">15</p>
            <p class="metric-subvalue">5 expiring soon</p>
        </div>

        <!-- Sensors Metric -->
        <div class="rounded-lg shadow-lg p-8 transition-all duration-300 hover:shadow-xl text-white bg-red-500 border border-white/10">
            <div class="flex justify-between items-center">
                <h3 class="metric-title">Sensors</h3>
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                </svg>
            </div>
            <p class="metric-value">100</p>
            <p class="metric-subvalue">50 active</p>
        </div>

        <!-- Add another metric card here if needed -->
    </div>

    
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Orders Chart
    var ordersCtx = document.getElementById('ordersChart').getContext('2d');
    var ordersChart = new Chart(ordersCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Orders',
                data: [12, 19, 3, 5, 2, 3],
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Monthly Orders'
                }
            }
        }
    });

    // Users Chart
    var usersCtx = document.getElementById('usersChart').getContext('2d');
    var usersChart = new Chart(usersCtx, {
        type: 'bar',
        data: {
            labels: ['Active', 'Inactive', 'New'],
            datasets: [{
                label: 'Users',
                data: [12, 8, 3],
                backgroundColor: [
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(54, 162, 235, 0.2)'
                ],
                borderColor: [
                    'rgb(75, 192, 192)',
                    'rgb(255, 99, 132)',
                    'rgb(54, 162, 235)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'User Status'
                }
            }
        }
    });
</script>
@endsection
