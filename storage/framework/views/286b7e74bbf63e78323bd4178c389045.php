<?php $__env->startSection('content'); ?>
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Users Management</h1>
        <a href="<?php echo e(route('users.create')); ?>" class="bg-blue-500 text-white px-4 py-2 rounded">Add New User</a>
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td class="px-6 py-4"><?php echo e($user['name']); ?></td>
                    <td class="px-6 py-4"><?php echo e($user['email']); ?></td>
                    <td class="px-6 py-4"><?php echo e($user['role']); ?></td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-sm rounded-full <?php echo e($user['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'); ?>">
                            <?php echo e($user['status']); ?>

                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <a href="<?php echo e(route('users.show', $user['id'])); ?>" class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                        <a href="<?php echo e(route('users.edit', $user['id'])); ?>" class="text-green-600 hover:text-green-900 mr-3">Edit</a>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td> There is nothing here</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/pharma-api/resources/views/users/index.blade.php ENDPATH**/ ?>