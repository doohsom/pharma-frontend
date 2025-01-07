<?php $__env->startSection('content'); ?>
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Products Management</h1>
        <?php if(Auth::user()->role === 'manufacturer'): ?>
            <a href="<?php echo e(route('products.create')); ?>" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                <i class="fas fa-plus mr-2"></i>Add New Product
            </a>
        <?php endif; ?>
    </div>

    <?php if(session('success')): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expiry Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td class="px-6 py-4"><?php echo e($product['name']); ?></td>
                    <td class="px-6 py-4"><?php echo e($product['description']); ?></td>
                    <td class="px-6 py-4">#<?php echo e(number_format($product['price'], 2)); ?></td>
                    <td class="px-6 py-4"><?php echo e(\Carbon\Carbon::parse($product['expiryDate'])->format('Y-m-d')); ?></td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-sm rounded-full 
                            <?php echo e($product['status'] === 'approved' ? 'bg-green-100 text-green-800' : 
                              ($product['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                               'bg-red-100 text-red-800')); ?>">
                            <?php echo e(ucfirst($product['status'])); ?>

                        </span>
                    </td>
                    <td class="px-6 py-4 space-x-3">
                        <!-- View Details Button - Available to all -->
                        <button onclick="openDetailsModal('<?php echo e($product['id']); ?>')" 
                            class="text-blue-600 hover:text-blue-800 focus:outline-none">
                            <i class="fas fa-eye"></i>
                        </button>

                        <?php if(Auth::user()->role === 'regulator' && $product['status'] === 'for_approval'): ?>
                            <!-- Update Status Button - Only for regulators and pending products -->
                            <button onclick="openStatusModal('<?php echo e($product['id']); ?>')" 
                                class="text-yellow-600 hover:text-yellow-800 focus:outline-none">
                                <i class="fas fa-check-circle"></i>
                            </button>
                        <?php endif; ?>
                        <?php if(Auth::user()->role === 'manufacturer' && $product['status'] !== 'for_approval'): ?>
                            <!-- Update Status Button - Only for regulators and pending products -->
                            <button onclick="openStatusModal('<?php echo e($product['id']); ?>')" 
                                class="text-yellow-600 hover:text-yellow-800 focus:outline-none">
                                <i class="fas fa-check-circle"></i>
                            </button>
                        <?php endif; ?>

                        <?php if(Auth::user()->role === 'vendor' && $product['status'] === 'approved'): ?>
                            <!-- Order Button - Only for vendors and approved products -->
                            <a href="<?php echo e(route('orders.create', ['product' => $product['id']])); ?>" 
                               class="inline-flex items-center px-3 py-1 bg-green-500 text-white text-sm rounded hover:bg-green-600">
                                <i class="fas fa-shopping-cart mr-1"></i>
                                Order
                            </a>
                        <?php endif; ?>
                        
                        
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">No products found</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php echo $__env->make('products.partials.details-modal', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php echo $__env->make('products.partials.status-modal', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/pharma-api/resources/views/products/index.blade.php ENDPATH**/ ?>