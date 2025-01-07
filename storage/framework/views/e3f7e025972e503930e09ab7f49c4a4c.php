<?php $__env->startSection('title', 'Sensor Dashboard - ' . $batchId); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .metric-card {
        @apply bg-white rounded-lg shadow p-6;
    }
    .table-row-excursion {
        @apply bg-red-50;
    }
    .alert-panel {
        @apply fixed top-4 right-4 w-96 z-50;
    }
    .badge {
        @apply px-2 py-1 text-xs font-bold rounded-full;
    }
    .badge-danger {
        @apply bg-red-500 text-white;
    }
    .alert-banner {
        @apply bg-red-50 border-l-4 border-red-500 p-4;
    }
    .pulse {
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.5; }
        100% { opacity: 1; }
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <div class="flex justify-between items-center">
            <div class="flex items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Sensor Monitoring</h1>
                    <p class="text-gray-600">Batch: <?php echo e($batchId); ?></p>
                </div>
                <?php if($excursions > 0): ?>
                    <div class="ml-4">
                        <span class="badge badge-danger pulse">
                            <?php echo e($excursions); ?> Active Excursion<?php echo e($excursions > 1 ? 's' : ''); ?>

                        </span>
                    </div>
                <?php endif; ?>
            </div>
            <div class="text-right text-sm text-gray-600">
                <p>Last Updated:</p>
                <p class="font-semibold" id="lastUpdated"></p>
            </div>
        </div>
    </div>

    
    <?php if($excursions > 0): ?>
        <div class="alert-banner mb-6" id="excursionAlert">
            <div class="flex justify-between items-start">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-red-800 font-medium">Attention Required</h3>
                        <div class="mt-1">
                            <p class="text-sm text-red-700">
                                <?php echo e($excursions); ?> sensor<?php echo e($excursions > 1 ? 's' : ''); ?> showing excursion values. 
                                Immediate action may be required.
                            </p>
                        </div>
                        <div class="mt-2">
                            <button type="button" 
                                    onclick="showNotificationModal()"
                                    class="text-sm font-medium text-red-800 hover:text-red-900">
                                Manage Notifications →
                            </button>
                        </div>
                    </div>
                </div>
                <button onclick="dismissAlert('excursionAlert')" class="text-red-700 hover:text-red-900">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    <?php endif; ?>

    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        
        <div class="metric-card">
            <div class="flex justify-between items-start">
                <div>
                    <h3 class="text-gray-500 text-sm font-medium">Active Sensors</h3>
                    <p class="text-2xl font-bold text-gray-800 mt-2"><?php echo e($uniqueSensors); ?></p>
                </div>
                <div class="bg-blue-50 rounded-full p-2">
                    <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        
        <div class="metric-card <?php echo e($excursions > 0 ? 'ring-2 ring-red-500 ring-opacity-50' : ''); ?>">
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center">
                        <h3 class="text-gray-500 text-sm font-medium">Total Excursions</h3>
                        <?php if($excursions > 0): ?>
                            <span class="ml-2 badge badge-danger">Active</span>
                        <?php endif; ?>
                    </div>
                    <p class="text-2xl font-bold <?php echo e($excursions > 0 ? 'text-red-600' : 'text-gray-800'); ?> mt-2">
                        <?php echo e($excursions); ?>

                    </p>
                </div>
                <div class="<?php echo e($excursions > 0 ? 'bg-red-50' : 'bg-gray-50'); ?> rounded-full p-2">
                    <svg class="w-6 h-6 <?php echo e($excursions > 0 ? 'text-red-500' : 'text-gray-400'); ?>" 
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>
            <?php if($excursions > 0): ?>
                <div class="mt-4 text-sm text-red-600">
                    Last excursion: <?php echo e(\Carbon\Carbon::parse($latestExcursion['timestamp'])->diffForHumans()); ?>

                </div>
            <?php endif; ?>
        </div>

        
        <div class="metric-card">
            <div class="flex justify-between items-start">
                <div>
                    <h3 class="text-gray-500 text-sm font-medium">Latest Temperature</h3>
                    <p class="text-2xl font-bold text-gray-800 mt-2">
                        <?php echo e(number_format(collect($temperatureReadings)->last()['value'], 1)); ?>°C
                    </p>
                </div>
                <div class="bg-green-50 rounded-full p-2">
                    <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold">Temperature Readings</h2>
            <?php if($excursions > 0): ?>
                <span class="badge badge-danger">
                    <?php echo e($excursions); ?> Excursion<?php echo e($excursions > 1 ? 's' : ''); ?>

                </span>
            <?php endif; ?>
        </div>
        <div class="h-80">
            <canvas id="temperatureChart"></canvas>
        </div>
    </div>

    
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h2 class="text-lg font-semibold">Latest 50 Readings</h2>
            <div class="flex items-center space-x-2 text-sm text-gray-600">
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-green-100 rounded-full mr-1"></div>
                    <span>Normal</span>
                </div>
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-red-100 rounded-full mr-1"></div>
                    <span>Excursion</span>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sensor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Value</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php $__currentLoopData = $latestReadings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reading): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="<?php echo e($reading['hasExcursion'] ? 'table-row-excursion' : ''); ?>">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo e(\Carbon\Carbon::parse($reading['timestamp'])->format('H:i:s')); ?>

                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?php echo e($reading['sensorId']); ?>

                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo e(ucfirst($reading['readingType'])); ?>

                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo e(number_format($reading['value'], 2)); ?>

                                <?php echo e($reading['readingType'] === 'temperature' ? '°C' : 
                                   ($reading['readingType'] === 'humidity' ? '%' : '')); ?>

                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <?php if($reading['hasExcursion']): ?>
                                    <span class="inline-flex items-center">
                                        <span class="badge badge-danger">Excursion</span>
                                        <button onclick="showExcursionDetails('<?php echo e($reading['id']); ?>')" 
                                                class="ml-2 text-gray-400 hover:text-gray-600">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </button>
                                    </span>
                                <?php else: ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Normal
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>

    
    <div id="excursionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <div class="flex justify-end">
                    <button onclick="closeExcursionModal()" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M12 9v2m0 4h.01m-6.938 4d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4" id="modalTitle">Excursion Details</h3>
                <div class="mt-4 px-7 py-3">
                    <div class="text-sm text-gray-500 text-left" id="excursionDetails">
                        
                    </div>
                </div>
                <div class="mt-4 flex justify-end space-x-3">
                    <button onclick="acknowledgeExcursion()" 
                            class="bg-red-600 text-white rounded-md px-4 py-2 hover:bg-red-700">
                        Acknowledge
                    </button>
                    <button onclick="closeExcursionModal()" 
                            class="bg-gray-100 text-gray-700 rounded-md px-4 py-2 hover:bg-gray-200">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Update timestamp
    function updateTimestamp() {
        const now = new Date();
        document.getElementById('lastUpdated').textContent = 
            now.toLocaleString('en-US', { 
                year: 'numeric', 
                month: '2-digit', 
                day: '2-digit',
                hour: '2-digit', 
                minute: '2-digit', 
                second: '2-digit',
                hour12: false 
            });
    }

    // Initialize temperature chart with excursion thresholds
    const ctx = document.getElementById('temperatureChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(collect($temperatureReadings)->pluck('timestamp')); ?>,
            datasets: [{
                label: 'Temperature (°C)',
                data: <?php echo json_encode(collect($temperatureReadings)->pluck('value')); ?>,
                borderColor: '#3b82f6',
                backgroundColor: '#93c5fd20',
                fill: true,
                tension: 0.3,
                borderWidth: 2,
                pointRadius: 3,
                pointBackgroundColor: function(context) {
                    const value = context.raw;
                    return value > 2.5 || value < 1.5 ? '#ef4444' : '#3b82f6';
                },
                pointRadius: function(context) {
                    const value = context.raw;
                    return value > 2.5 || value < 1.5 ? 5 : 3;
                }
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
                            const value = context.raw;
                            let label = `Temperature: ${value}°C`;
                            if (value > 2.5 || value < 1.5) {
                                label += ' (Excursion)';
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: false,
                    grid: {
                        drawBorder: false
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // Excursion details modal functions
    function showExcursionDetails(readingId) {
        const reading = <?php echo json_encode($latestReadings); ?>.find(r => r.id === readingId);
        if (reading) {
            document.getElementById('excursionDetails').innerHTML = `
                <div class="space-y-2">
                    <p><strong>Sensor ID:</strong> ${reading.sensorId}</p>
                    <p><strong>Reading Type:</strong> ${reading.readingType}</p>
                    <p><strong>Value:</strong> ${reading.value}</p>
                    <p><strong>Timestamp:</strong> ${new Date(reading.timestamp).toLocaleString()}</p>
                    <div class="mt-4 p-3 bg-red-50 rounded">
                        <p class="text-red-700"><strong>Excursion Details:</strong></p>
                        <p class="text-sm text-red-600">Value exceeds normal threshold range.</p>
                    </div>
                </div>
            `;
            document.getElementById('excursionModal').classList.remove('hidden');
        }
    }

    function closeExcursionModal() {
        document.getElementById('excursionModal').classList.add('hidden');
    }

    function acknowledgeExcursion() {
        // Here you would typically send an acknowledgment to your backend
        alert('Excursion acknowledged. Notifications will be sent to relevant parties.');
        closeExcursionModal();
    }

    function dismissAlert(elementId) {
        document.getElementById(elementId).style.display = 'none';
    }

    // Initialize page
    updateTimestamp();
    
    // Refresh page every minute
    setInterval(() => {
        updateTimestamp();
        window.location.reload();
    }, 60000);

    // Add pulse animation to excursion badges if there are active excursions
    <?php if($excursions > 0): ?>
    const badges = document.querySelectorAll('.badge-danger');
    badges.forEach(badge => {
        badge.classList.add('pulse');
    });
    <?php endif; ?>
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/pharma-api/resources/views/batch/readings.blade.php ENDPATH**/ ?>