<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Batch <?php echo e($batchId); ?> - Cold Chain Monitoring</title>
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
                    <a href="<?php echo e(route('batches.index')); ?>" class="text-blue-500 hover:text-blue-600 mb-2 inline-block">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Batches
                    </a>
                    <h1 class="text-3xl font-bold">Batch <?php echo e($batchId); ?></h1>
                    <p class="text-gray-500">
                        Started: <?php echo e($timestamp->format('Y-m-d H:i:s')); ?>

                        <span class="text-sm">(<?php echo e($timestamp->diffForHumans()); ?>)</span>
                    </p>
                </div>
                <div class="flex items-center gap-4">
                    <p class="text-sm text-gray-500">
                        Last updated: <?php echo e($lastUpdated->diffForHumans()); ?>

                    </p>
                    <a href="<?php echo e(route('batches.refresh.batch', $batchId)); ?>" 
                       class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
                        <i class="fas fa-sync-alt mr-2"></i> Refresh Data
                    </a>
                </div>
            </div>

            <!-- Excursion Alert -->
            <?php if($excursions->isNotEmpty()): ?>
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-8 rounded-lg">
                <div class="flex items-start">
                    <i class="fas fa-exclamation-triangle text-red-500 mr-3 mt-1"></i>
                    <div>
                        <h3 class="text-red-800 font-medium">Excursions Detected</h3>
                        <p class="text-red-700"><?php echo e($excursions->count()); ?> excursions detected in this batch</p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Sensors Grid -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-8">
                <?php $__currentLoopData = $latestSensorReadings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reading): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="bg-white rounded-lg shadow p-4 cursor-pointer hover:shadow-lg transition-shadow"
                     onclick="showSensorDetails('<?php echo e($reading['sensor_id']); ?>')">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="font-semibold"><?php echo e($reading['sensor_id']); ?></h3>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full 
                            <?php echo e($reading['has_excursion'] ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'); ?>">
                            <?php echo e($reading['has_excursion'] ? 'Excursion' : 'Normal'); ?>

                        </span>
                    </div>
                    <p class="text-gray-600"><?php echo e(ucfirst($reading['reading_type'])); ?></p>
                    <p class="text-lg font-semibold">
                        <?php echo e($reading['value']); ?>

                        <?php if($reading['reading_type'] === 'temperature'): ?>°C
                        <?php elseif($reading['reading_type'] === 'humidity'): ?>%
                        <?php else: ?> hPa
                        <?php endif; ?>
                    </p>
                    <p class="text-xs text-gray-500 mt-2">
                        <?php echo e(Carbon\Carbon::parse($reading['reading_timestamp'])->diffForHumans()); ?>

                    </p>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <!-- Sensor Charts -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <?php $__currentLoopData = ['temperature', 'humidity', 'pressure']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold mb-4 capitalize"><?php echo e($type); ?> Distribution</h3>
                    <canvas id="<?php echo e($type); ?>Chart" height="200"></canvas>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                                <?php $__currentLoopData = $paginatedReadings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reading): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="border-b <?php echo e($reading['has_excursion'] ? 'bg-red-50' : ''); ?>">
                                    <td class="py-3 px-4">
                                        <?php echo e(Carbon\Carbon::parse($reading['reading_timestamp'])->format('Y-m-d H:i:s')); ?>

                                    </td>
                                    <td class="py-3 px-4"><?php echo e($reading['sensor_id']); ?></td>
                                    <td class="py-3 px-4 capitalize"><?php echo e($reading['reading_type']); ?></td>
                                    <td class="py-3 px-4">
                                        <?php echo e($reading['value']); ?>

                                        <?php if($reading['reading_type'] === 'temperature'): ?>°C
                                        <?php elseif($reading['reading_type'] === 'humidity'): ?>%
                                        <?php else: ?> hPa
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                            <?php echo e($reading['has_excursion'] ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'); ?>">
                                            <?php echo e($reading['has_excursion'] ? 'Excursion' : 'Normal'); ?>

                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        <?php echo e($paginatedReadings->links()); ?>

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
    document.addEventListener('DOMContentLoaded', function() {
        const chartColors = {
            temperature: '#ef4444',
            humidity: '#3b82f6',
            pressure: '#22c55e'
        };

        <?php $__currentLoopData = ['temperature', 'humidity', 'pressure']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        const <?php echo e($type); ?>Data = {
            labels: <?php echo json_encode($latestSensorReadings->where('reading_type', $type)->pluck('sensor_id')); ?>,
            values: <?php echo json_encode($latestSensorReadings->where('reading_type', $type)->pluck('value')); ?>

        };

        new Chart(document.getElementById('<?php echo e($type); ?>Chart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: <?php echo e($type); ?>Data.labels,
                datasets: [{
                    label: '<?php echo e(ucfirst($type)); ?>',
                    data: <?php echo e($type); ?>Data.values,
                    backgroundColor: chartColors.<?php echo e($type); ?> + '40',
                    borderColor: chartColors.<?php echo e($type); ?>,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: false
                    }
                }
            }
        });
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    });

    function showSensorDetails(sensorId) {
        const modal = document.getElementById('sensorModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalContent = document.getElementById('modalContent');

        modalTitle.textContent = `Sensor ${sensorId} Readings`;
        modalContent.innerHTML = '<div class="flex justify-center"><i class="fas fa-spinner fa-spin text-3xl text-blue-500"></i></div>';
        modal.classList.remove('hidden');

        fetch(`/batches/<?php echo e($batchId); ?>/sensors/${sensorId}`)
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
</html><?php /**PATH /var/www/html/pharma-api/resources/views/batch/show.blade.php ENDPATH**/ ?>