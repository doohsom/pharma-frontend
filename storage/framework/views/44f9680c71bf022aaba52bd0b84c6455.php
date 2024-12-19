<!-- resources/views/batches/index.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cold Chain Monitoring - Batches</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen p-8">
        <div class="max-w-7xl mx-auto">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-3xl font-bold">Cold Chain Monitoring Batches</h1>
                <a href="<?php echo e(route('batches.refresh')); ?>" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
                    <i class="fas fa-sync-alt mr-2"></i> Refresh Data
                </a>
            </div>

            <!-- Batches Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php $__currentLoopData = $batches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $batch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route('batches.show', $batch['batch_id'])); ?>" 
                   class="block bg-white rounded-lg shadow hover:shadow-lg transition-shadow duration-200">
                    <div class="p-6 <?php echo e($batch['excursions'] > 0 ? 'border-l-4 border-red-500' : 'border-l-4 border-green-500'); ?>">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h2 class="text-xl font-bold"><?php echo e($batch['batch_id']); ?></h2>
                                <p class="text-sm text-gray-500"><?php echo e($batch['timestamp']->format('Y-m-d H:i:s')); ?></p>
                            </div>
                            <?php if($batch['excursions'] > 0): ?>
                            <span class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-800 rounded-full">
                                <?php echo e($batch['excursions']); ?> Excursions
                            </span>
                            <?php else: ?>
                            <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full">
                                Normal
                            </span>
                            <?php endif; ?>
                        </div>

                        <div class="space-y-2">
                            <?php $__currentLoopData = $batch['readings_by_type']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="flex justify-between items-center text-sm">
                                <span class="capitalize"><?php echo e($type); ?>:</span>
                                <span class="<?php echo e($data['excursions'] > 0 ? 'text-red-600 font-semibold' : 'text-gray-600'); ?>">
                                    <?php echo e($data['avg_value']); ?>

                                    <?php if($type === 'temperature'): ?>°C
                                    <?php elseif($type === 'humidity'): ?>%
                                    <?php else: ?> hPa
                                    <?php endif; ?>
                                </span>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <div class="text-sm text-gray-500 mt-2">
                                Total Readings: <?php echo e($batch['total_readings']); ?>

                            </div>
                        </div>
                    </div>
                </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>
</body>
</html><?php /**PATH /var/www/html/pharma-api/resources/views/batch/index.blade.php ENDPATH**/ ?>