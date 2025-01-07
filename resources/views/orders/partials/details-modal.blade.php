{{-- resources/views/orders/partials/details-modal.blade.php --}}
<div id="orderDetailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto hidden" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full">
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h3 class="text-xl font-semibold text-gray-900" id="modalTitle">Order Details</h3>
                    <button type="button" class="text-gray-400 hover:text-gray-500" onclick="closeDetailsModal()">
                        <span class="sr-only">Close</span>
                        <span class="text-2xl">&times;</span>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="px-6 py-4 max-h-[70vh] overflow-y-auto">
                <div class="space-y-6">
                    <!-- Order Details Section -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Order ID</label>
                            <p id="modalOrderId" class="mt-1 text-sm text-gray-900"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <p id="modalOrderStatus" class="mt-1"></p>
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

                    <!-- Status Update Section for authorized users -->
                    @if(Auth::user()->role === 'manufacturer' || Auth::user()->role === 'logistics')
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <h4 class="text-lg font-medium text-gray-900 mb-4">Update Status</h4>
                            <form id="statusUpdateForm" class="space-y-4">
                                @csrf
                                <input type="hidden" id="modalOrderIdInput" name="orderId">

                                <div>
                                    <label for="statusSelect" class="block text-sm font-medium text-gray-700">New Status</label>
                                    <select id="statusSelect" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-200">
                                        @if(Auth::user()->role === 'manufacturer')
                                            <option value="processing">Processing</option>
                                            <option value="ready_for_pickup">Ready for Pickup</option>
                                            <option value="cancelled">Cancelled</option>
                                        @endif
                                        @if(Auth::user()->role === 'logistics')
                                            <option value="picked_up">Picked Up</option>
                                            <option value="in_transit">In Transit</option>
                                            <option value="delivered">Delivered</option>
                                        @endif
                                    </select>
                                </div>

                                <div>
                                    <label for="statusNotes" class="block text-sm font-medium text-gray-700">Notes</label>
                                    <textarea id="statusNotes" name="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-200" 
                                              placeholder="Add notes about this status update..."></textarea>
                                </div>

                                <div class="flex justify-end">
                                    <button type="button" onclick="closeDetailsModal()" class="mr-3 px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-500">
                                        Cancel
                                    </button>
                                    <button type="button" onclick="updateOrderStatus()" class="px-4 py-2 bg-blue-500 text-white text-sm font-medium rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        Update Status
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function getStatusClass(status) {
    const classes = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'processing': 'bg-blue-100 text-blue-800',
        'ready_for_pickup': 'bg-purple-100 text-purple-800',
        'picked_up': 'bg-indigo-100 text-indigo-800',
        'in_transit': 'bg-cyan-100 text-cyan-800',
        'delivered': 'bg-green-100 text-green-800',
        'cancelled': 'bg-red-100 text-red-800'
    };
    return classes[status.toLowerCase()] || 'bg-gray-100 text-gray-800';
}

async function openDetailsModal(orderId) {
    try {
        const response = await fetch(`/orders/${orderId}`);
        if (!response.ok) throw new Error('Failed to fetch order details');
        
        const order = await response.json();
        console.log('Order details:', order);  // Debug log

        // Update modal content
        document.getElementById('modalOrderId').textContent = order.id;
        document.getElementById('modalProductId').textContent = order.productId;
        document.getElementById('modalQuantity').textContent = order.quantity;
        
        // Update status with styling
        const statusElem = document.getElementById('modalOrderStatus');
        statusElem.textContent = order.status.replace(/_/g, ' ').toUpperCase();
        statusElem.className = `px-2 py-1 text-sm rounded-full ${getStatusClass(order.status)}`;

        // Update hidden input
        document.getElementById('modalOrderIdInput').value = order.id;

        // Show modal
        document.getElementById('orderDetailsModal').classList.remove('hidden');
    } catch (error) {
        console.error('Error fetching order details:', error);
        toastr.error('Failed to load order details');
    }
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
        console.error('Error updating status:', error);
        toastr.error(error.message || 'Failed to update order status');
    }
}

function closeDetailsModal() {
    document.getElementById('orderDetailsModal').classList.add('hidden');
    // Clear form if it exists
    const form = document.getElementById('statusUpdateForm');
    if (form) form.reset();
}
</script>
@endpush