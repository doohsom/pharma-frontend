{{-- resources/views/products/partials/status-modal.blade.php --}}
<div id="updateStatusModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto hidden" 
     role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <form id="updateProductStatusForm" class="p-6">
                <!-- Modal Header -->
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Update Product Status</h3>
                    <button type="button" class="text-gray-400 hover:text-gray-500" onclick="closeStatusModal()">
                        <span class="sr-only">Close</span>
                        <span class="text-2xl">&times;</span>
                    </button>
                </div>

                <input type="hidden" id="productId">

                <!-- Status Selection -->
                <div class="mb-4">
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                        Status
                    </label>
                    <select id="status" name="status" 
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Select status...</option>
                        <option value="created">Created</option>
                        <option value="for_approval">Waiting Approval</option>
                        <option value="approved">Approved</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    <p id="statusError" class="mt-1 text-sm text-red-600 hidden"></p>
                </div>

                <!-- Notes Field -->
                <div class="mb-4">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                        Notes
                    </label>
                    <textarea id="notes" name="notes" rows="3" 
                              class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" 
                              placeholder="Enter notes about this status change..."></textarea>
                    <p id="notesError" class="mt-1 text-sm text-red-600 hidden"></p>
                </div>

                <!-- Buttons -->
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" 
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            onclick="closeStatusModal()">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Update Status
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentProductId = null;

function openStatusModal(productId, currentStatus = '') {
    currentProductId = productId;
    document.getElementById('productId').value = productId;
    
    // Reset form and errors
    document.getElementById('updateProductStatusForm').reset();
    document.getElementById('statusError').classList.add('hidden');
    document.getElementById('notesError').classList.add('hidden');
    
    // Pre-select current status if provided
    if (currentStatus) {
        document.getElementById('status').value = currentStatus;
    }
    
    document.getElementById('updateStatusModal').classList.remove('hidden');
}

function closeStatusModal() {
    document.getElementById('updateStatusModal').classList.add('hidden');
    currentProductId = null;
    document.getElementById('updateProductStatusForm').reset();
}

function showError(fieldId, message) {
    const errorElement = document.getElementById(`${fieldId}Error`);
    errorElement.textContent = message;
    errorElement.classList.remove('hidden');
}

function hideErrors() {
    document.getElementById('statusError').classList.add('hidden');
    document.getElementById('notesError').classList.add('hidden');
}

document.getElementById('updateProductStatusForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    hideErrors();
    
    const status = document.getElementById('status').value;
    const notes = document.getElementById('notes').value;
    
    // Validation
    let hasError = false;
    if (!status) {
        showError('status', 'Please select a status');
        hasError = true;
    }
    if (!notes.trim()) {
        showError('notes', 'Please provide notes for this status change');
        hasError = true;
    }
    
    if (hasError) return;
    
    try {
        const response = await fetch(`http://localhost:5050/api/products/${currentProductId}/status`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ status, notes })
        });

        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.error || 'Failed to update status');
        }

        // Show success message
        toastr.success('Product status updated successfully');
        
        // Close modal
        closeStatusModal();

        // Refresh the page or update the UI
        window.location.reload();
    } catch (error) {
        toastr.error(error.message || 'Failed to update product status');
    }
});
</script>