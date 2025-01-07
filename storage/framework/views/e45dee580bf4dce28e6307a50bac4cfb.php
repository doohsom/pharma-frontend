<?php $__env->startSection('content'); ?>
<div class="container mx-auto px-4 py-6">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold mb-6">Edit User</h1>

        <?php if($errors->any()): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul>
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?php echo e(route('users.update', $user['id'])); ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>
            
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                <input type="text" name="name" id="name" value="<?php echo e($user['name']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" id="email" value="<?php echo e($user['email']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
            </div>

            <div>
                <label for="phone_number" class="block text-sm font-medium text-gray-700">Phone Number</label>
                <input type="tel" name="phone_number" id="phoneNumber" value="<?php echo e($user['phone_number']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
            </div>

            <div>
                <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                <select name="role" id="role" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="manufacturer" <?php echo e($user['role'] === 'manufacturer' ? 'selected' : ''); ?>>Manufacturer</option>
                    <option value="logistics" <?php echo e($user['role'] === 'logistics' ? 'selected' : ''); ?>>Logistics</option>
                    <option value="vendor" <?php echo e($user['role'] === 'vendor' ? 'selected' : ''); ?>>Vendor</option>
                    <option value="regulator" <?php echo e($user['role'] === 'regulator' ? 'selected' : ''); ?>>Regulator</option>
                </select>
            </div>

            <div>
                <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                <textarea name="address" id="address" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"><?php echo e($user['address']); ?></textarea>
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="active" <?php echo e($user['status'] === 'active' ? 'selected' : ''); ?>>Active</option>
                    <option value="inactive" <?php echo e($user['status'] === 'inactive' ? 'selected' : ''); ?>>Inactive</option>
                    <option value="pending" <?php echo e($user['status'] === 'pending' ? 'selected' : ''); ?>>Pending</option>
                </select>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Update User</button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/pharma-api/resources/views/users/edit.blade.php ENDPATH**/ ?>