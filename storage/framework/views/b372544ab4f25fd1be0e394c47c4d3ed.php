
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e(config('app.name', 'Supply Chain Management')); ?> - <?php echo $__env->yieldContent('title', 'Dashboard'); ?></title>
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Styles -->
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <?php echo $__env->yieldPushContent('styles'); ?>
    <!-- Styles -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-moment"></script>
    
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <!-- Logo -->
                    <div class="flex-shrink-0 flex items-center">
                        <a href="<?php echo e(route('dashboard')); ?>" class="text-xl font-bold text-gray-800">
                            Supply Chain
                        </a>
                    </div>

                    <!-- Navigation Links -->
                    <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                        <a href="<?php echo e(route('dashboard')); ?>" 
                           class="inline-flex items-center px-1 pt-1 border-b-2 
                           <?php echo e(request()->routeIs('dashboard') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'); ?>">
                            Dashboard
                        </a>
                        
                        <?php if(Auth::user()->role === 'manufacturer'): ?>
                            <a href="<?php echo e(route('users.index')); ?>"
                               class="inline-flex items-center px-1 pt-1 border-b-2
                               <?php echo e(request()->routeIs('users.*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'); ?>">
                                Users
                            </a>
                            <a href="<?php echo e(route('products.index')); ?>"
                               class="inline-flex items-center px-1 pt-1 border-b-2
                               <?php echo e(request()->routeIs('products.*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'); ?>">
                                Products
                            </a>
                            <a href="<?php echo e(route('orders.index')); ?>"
                               class="inline-flex items-center px-1 pt-1 border-b-2
                               <?php echo e(request()->routeIs('orders.*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'); ?>">
                                Orders
                            </a>
                            <a href="<?php echo e(route('batches.index')); ?>"
                               class="inline-flex items-center px-1 pt-1 border-b-2
                               <?php echo e(request()->routeIs('batches.*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'); ?>">
                                Batches
                            </a>
                        <?php endif; ?>

                        <?php if(Auth::user()->role === 'vendor'): ?>
                            <a href="<?php echo e(route('products.index')); ?>"
                               class="inline-flex items-center px-1 pt-1 border-b-2
                               <?php echo e(request()->routeIs('products.*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'); ?>">
                                Browse Products
                            </a>
                            <a href="<?php echo e(route('orders.index')); ?>"
                               class="inline-flex items-center px-1 pt-1 border-b-2
                               <?php echo e(request()->routeIs('orders.*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'); ?>">
                                My Orders
                            </a>
                            <a href="<?php echo e(route('batches.index')); ?>"
                               class="inline-flex items-center px-1 pt-1 border-b-2
                               <?php echo e(request()->routeIs('batches.*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'); ?>">
                                Track Shipments
                            </a>
                        <?php endif; ?>

                        <?php if(Auth::user()->role === 'logistics'): ?>
                            <a href="<?php echo e(route('batches.index')); ?>"
                               class="inline-flex items-center px-1 pt-1 border-b-2
                               <?php echo e(request()->routeIs('batches.*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'); ?>">
                                Manage Shipments
                            </a>
                            <a href="<?php echo e(route('sensor-data.index')); ?>"
                               class="inline-flex items-center px-1 pt-1 border-b-2
                               <?php echo e(request()->routeIs('sensor-data.*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'); ?>">
                                Sensor Data
                            </a>
                        <?php endif; ?>

                        <?php if(Auth::user()->role === 'regulator'): ?>
                            <a href="<?php echo e(route('products.index')); ?>"
                               class="inline-flex items-center px-1 pt-1 border-b-2
                               <?php echo e(request()->routeIs('products.*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'); ?>">
                                Product Approvals
                            </a>
                            <a href="<?php echo e(route('batches.index')); ?>"
                               class="inline-flex items-center px-1 pt-1 border-b-2
                               <?php echo e(request()->routeIs('batches.*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'); ?>">
                                Batch Approvals
                            </a>
                            <a href=""
                               class="inline-flex items-center px-1 pt-1 border-b-2
                               <?php echo e(request()->routeIs('audit-logs.*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'); ?>">
                                Audit Logs
                            </a>
                        <?php endif; ?>

                        <?php if(Auth::user()->role === 'admin'): ?>
                            <a href="<?php echo e(route('users.index')); ?>"
                               class="inline-flex items-center px-1 pt-1 border-b-2
                               <?php echo e(request()->routeIs('users.*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'); ?>">
                                Users
                            </a>
                            <a href="<?php echo e(route('products.index')); ?>"
                               class="inline-flex items-center px-1 pt-1 border-b-2
                               <?php echo e(request()->routeIs('products.*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'); ?>">
                                Products
                            </a>
                            <a href="<?php echo e(route('batches.index')); ?>"
                               class="inline-flex items-center px-1 pt-1 border-b-2
                               <?php echo e(request()->routeIs('batches.*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'); ?>">
                                Batches
                            </a>
                            <a href="<?php echo e(route('audit-logs.index')); ?>"
                               class="inline-flex items-center px-1 pt-1 border-b-2
                               <?php echo e(request()->routeIs('audit-logs.*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'); ?>">
                                Audit Logs
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- User Menu -->
                <div class="hidden sm:flex sm:items-center sm:ml-6">
                    <div class="ml-3 relative">
                        <?php if(auth()->guard()->check()): ?>
                            <div class="flex items-center space-x-4">
                                <span class="text-gray-700"><?php echo e(Auth::user()->name); ?></span>
                                <span class="text-sm text-gray-500">(<?php echo e(ucfirst(Auth::user()->role)); ?>)</span>
                                <form method="POST" action="<?php echo e(route('logout')); ?>" class="inline">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="text-gray-500 hover:text-gray-700">
                                        Logout
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Mobile menu button -->
                <div class="-mr-2 flex items-center sm:hidden">
                    <button type="button" class="mobile-menu-button inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile menu -->
        <div class="sm:hidden hidden mobile-menu">
            <div class="pt-2 pb-3 space-y-1">
                <!-- Add the same conditional navigation items here but with mobile styling -->
                <?php if(Auth::user()->role === 'manufacturer'): ?>
                    <!-- Manufacturer mobile menu items -->
                <?php elseif(Auth::user()->role === 'vendor'): ?>
                    <!-- Vendor mobile menu items -->
                <?php elseif(Auth::user()->role === 'logistics'): ?>
                    <!-- Logistics mobile menu items -->
                <?php elseif(Auth::user()->role === 'regulator'): ?>
                    <!-- Regulator mobile menu items -->
                <?php endif; ?>
            </div>

            <?php if(auth()->guard()->check()): ?>
                <div class="pt-4 pb-1 border-t border-gray-200">
                    <!-- Mobile user menu -->
                </div>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Page Content -->
    <main>
        <?php echo $__env->yieldContent('content'); ?>
    </main>

    <!-- Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const button = document.querySelector('.mobile-menu-button');
            const menu = document.querySelector('.mobile-menu');
            
            button.addEventListener('click', () => {
                menu.classList.toggle('hidden');
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html><?php /**PATH /var/www/html/pharma-api/resources/views/layouts/app.blade.php ENDPATH**/ ?>