<?php $__env->startSection('styles'); ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Orders Management</h1>
        <?php if(Auth::user()->role === 'vendor'): ?>
            <a href="<?php echo e(route('orders.create')); ?>" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                Create New Order
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sender</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Receiver</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td class="px-6 py-4"><?php echo e($order['id']); ?></td>
                    <td class="px-6 py-4"><?php echo e($order['productId']); ?></td>
                    <td class="px-6 py-4"><?php echo e($order['senderId']); ?></td>
                    <td class="px-6 py-4"><?php echo e($order['receiverId']); ?></td>
                    <td class="px-6 py-4"><?php echo e($order['quantity']); ?></td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-sm rounded-full <?php echo e($order['status']); ?>">
                            <?php echo e(ucfirst(strtolower($order['status']))); ?>

                        </span>
                    </td>
                    <!-- In index.blade.php actions column -->
                    <td class="px-6 py-4 space-x-2">
                        <!-- View Button - Always visible -->
                        <button onclick="openDetailsModal('<?php echo e($order['id']); ?>')" 
                                class="inline-flex items-center px-2 py-1 text-blue-600 hover:text-blue-800">
                            <i class="fas fa-eye"></i>
                            <span class="ml-1">View</span>
                        </button>
                        
                        <!-- Manufacturer Actions -->
                        <?php
                            $orderStatus = strtolower($order['status']);
                        ?>
                    
                        <?php if(Auth::user()->role === 'manufacturer'): ?>
                            <?php if(in_array($orderStatus, ['pending', 'processed'])): ?>
                                <button onclick="openDetailsModal('<?php echo e($order['id']); ?>')" 
                                        class="inline-flex items-center px-2 py-1 text-yellow-600 hover:text-yellow-800">
                                    <i class="fas fa-edit"></i>
                                    <span class="ml-1">Update</span>
                                </button>
                            <?php endif; ?>
                            <?php if($order['status'] === 'to_be_batched'): ?>
                            <a href="<?php echo e(route('batches.create', $order['id'])); ?>" 
                            class="inline-flex items-center px-3 py-1 bg-green-500 text-white text-sm rounded hover:bg-green-600">
                                <i class="fas fa-box"></i>
                                <span class="ml-1">Create Batch</span>
                            </a>
                        <?php endif; ?>
                        <?php endif; ?>
                        
                        <!-- Logistics Actions -->
                        <?php if(Auth::user()->role === 'logistics'): ?>
                            <?php if(in_array($orderStatus, ['to_be_batched', 'in_transit'])): ?>
                                <button onclick="openDetailsModal('<?php echo e($order['id']); ?>')" 
                                        class="inline-flex items-center px-2 py-1 text-green-600 hover:text-green-800">
                                    <i class="fas fa-truck"></i>
                                    <span class="ml-1">Update Delivery</span>
                                </button>
                            <?php endif; ?>
                        <?php endif; ?>
                    
                        <!-- Vendor Actions -->
                        <?php if(Auth::user()->role === 'vendor' && $orderStatus === 'pending'): ?>
                            <button onclick="openDetailsModal('<?php echo e($order['id']); ?>')" 
                                    class="inline-flex items-center px-2 py-1 text-red-600 hover:text-red-800">
                                <i class="fas fa-times"></i>
                                <span class="ml-1">Cancel</span>
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</div>
<div id="orderDetailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto hidden" style="z-index: 50;">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full mx-auto">
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h3 class="text-xl font-semibold text-gray-900">Order Details</h3>
                    <button type="button" class="text-gray-400 hover:text-gray-500" onclick="closeDetailsModal()">
                        <span class="sr-only">Close</span>
                        <span class="text-2xl">&times;</span>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="px-6 py-4">
                <div class="space-y-4">
                    <!-- Order Details Section -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Order ID</label>
                            <p id="modalOrderId" class="mt-1 text-sm text-gray-900"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <p id="modalOrderStatus" class="mt-1 text-sm"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Product ID</label>
                            <p id="modalProductId" class="mt-1 text-sm text-gray-900"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Quantity</label>
                            <p id="modalQuantity" class="mt-1 text-sm text-gray-900"></p>
                        </div>
                    </div>

                    <!-- Status Update Form -->
                    <?php if(Auth::user()->role === 'manufacturer' || Auth::user()->role === 'logistics'): ?>
                    <div class="mt-6 pt-4 border-t border-gray-200">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Update Status</h4>
                        <form id="statusUpdateForm" class="space-y-4">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" id="modalOrderIdInput">
                            
                            <div>
                                <label for="statusSelect" class="block text-sm font-medium text-gray-700">New Status</label>
                                <select id="statusSelect" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-200">
                                    <!-- Options will be populated dynamically -->
                                </select>
                            </div>

                            <div>
                                <label for="statusNotes" class="block text-sm font-medium text-gray-700">Notes</label>
                                <textarea id="statusNotes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-200"
                                    placeholder="Add notes about this status update..."></textarea>
                            </div>

                            <div class="flex justify-end space-x-3">
                                <button type="button" onclick="closeDetailsModal()" 
                                    class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-500">
                                    Cancel
                                </button>
                                <button type="button" onclick="updateOrderStatus()" 
                                    class="px-4 py-2 bg-blue-500 text-white text-sm font-medium rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    Update Status
                                </button>
                            </div>
                        </form>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
// Configure toastr
toastr.options = {
    "closeButton": true,
    "progressBar": true,
    "positionClass": "toast-top-right",
};

function getStatusClass(status) {
    const statusClasses = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'processed': 'bg-blue-100 text-blue-800',
        'to_be_batched': 'bg-purple-100 text-purple-800',
        'in_transit': 'bg-cyan-100 text-cyan-800',
        'delivered': 'bg-green-100 text-green-800',
        'cancelled': 'bg-red-100 text-red-800'
    };
    return statusClasses[status.toLowerCase()] || 'bg-gray-100 text-gray-800';
}



async function updateOrderStatus() {
    const orderId = document.getElementById('modalOrderIdInput').value;
    const status = document.getElementById('statusSelect').value;
    const notes = document.getElementById('statusNotes').value;

    if (!notes.trim()) {
        toastr.error('Please provide notes for the status update');
        return;
    }

    try {
        const response = await fetch(`/orders/${orderId}/status`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ status, notes })
        });

        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.message || 'Failed to update status');
        }

        toastr.success('Order status updated successfully');
        closeDetailsModal();
        window.location.reload();

    } catch (error) {
        console.error('Error:', error);
        toastr.error(error.message || 'Failed to update order status');
    }
}

function closeDetailsModal() {
    const modal = document.getElementById('orderDetailsModal');
    modal.classList.add('hidden');
    
    // Clear form if it exists
    const form = document.getElementById('statusUpdateForm');
    if (form) form.reset();
}

function updateStatusOptions(currentStatus) {
    const select = document.getElementById('statusSelect');
    const userRole = '<?php echo e(Auth::user()->role); ?>';
    select.innerHTML = ''; // Clear existing options

    const options = {
        manufacturer: {
            'pending': [
                { value: 'processed', label: 'Processed' },
                { value: 'cancelled', label: 'Cancelled' }
            ],
            'processed': [
                { value: 'to_be_batched', label: 'To Be Batched' },
                { value: 'cancelled', label: 'Cancelled' }
            ]
        },
        logistics: {
            'to_be_batched': [
                { value: 'in_transit', label: 'In Transit' }
            ],
            'in_transit': [
                { value: 'delivered', label: 'Delivered' }
            ]
        },
        vendor: {
            'pending': [
                { value: 'cancelled', label: 'Cancelled' }
            ]
        }
    };

    // Get available options for current role and status
    const availableOptions = options[userRole]?.[currentStatus] || [];

    // Add options to select
    availableOptions.forEach(option => {
        const optionElement = document.createElement('option');
        optionElement.value = option.value;
        optionElement.textContent = option.label;
        select.appendChild(optionElement);
    });

    // Show/hide the status update form based on available options
    const updateForm = document.getElementById('statusUpdateForm');
    if (updateForm) {
        updateForm.style.display = availableOptions.length > 0 ? 'block' : 'none';
    }
}

async function openDetailsModal(orderId) {
    console.log('Opening modal for order:', orderId);
    try {
        const response = await fetch(`/orders/${orderId}`);
        if (!response.ok) {
            throw new Error('Failed to fetch order details');
        }
        
        const data = await response.json();
        console.log('Raw API response:', data);
        
        // Correctly extract the order data based on your API response
        const order = data[0].order.order;
        const product = data[0].order.product;
        const sender = data[0].sender;
        const receiver = data[0].receiver;

        console.log('Order details:', order);

        // Update modal content with null checks
        document.getElementById('modalOrderId').textContent = order?.id || 'N/A';
        document.getElementById('modalProductId').textContent = product?.name || 'N/A';
        document.getElementById('modalQuantity').textContent = order?.quantity || 'N/A';
        
        // Fix the status text formatting with null check
        const statusElem = document.getElementById('modalOrderStatus');
        if (order?.status) {
            const statusText = order.status.toString().replace(/_/g, ' ').toUpperCase();
            statusElem.textContent = statusText;
            statusElem.className = `px-2 py-1 text-sm rounded-full ${getStatusClass(order.status)}`;
        } else {
            statusElem.textContent = 'N/A';
            statusElem.className = 'px-2 py-1 text-sm rounded-full bg-gray-100 text-gray-800';
        }

        // Set hidden input value
        document.getElementById('modalOrderIdInput').value = order?.id || '';

        // Update status options based on current status
        if (order?.status) {
            updateStatusOptions(order.status.toLowerCase());
        }

        // Show modal
        const modal = document.getElementById('orderDetailsModal');
        modal.classList.remove('hidden');
        console.log('Modal should now be visible');

    } catch (error) {
        console.error('Error:', error);
        toastr.error('Failed to load order details');
    }
}


</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/pharma-api/resources/views/orders/index.blade.php ENDPATH**/ ?>