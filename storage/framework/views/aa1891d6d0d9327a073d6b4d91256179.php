
<div id="productDetailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto hidden" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h3 class="text-xl font-semibold text-gray-900" id="modalProductName">Product Details</h3>
                    <button type="button" class="text-gray-400 hover:text-gray-500" onclick="closeDetailsModal()">
                        <span class="sr-only">Close</span>
                        <span class="text-2xl">&times;</span>
                    </button>
                </div>
            </div>

            <div class="px-6 py-4 max-h-[70vh] overflow-y-auto">
                <div class="space-y-6">
                    <!-- Basic Information -->
                    <div>
                        <h4 class="text-lg font-medium text-gray-900 mb-3">Basic Information</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Product ID</p>
                                <p class="mt-1" id="modalProductId"></p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Status</p>
                                <p class="mt-1"><span id="modalProductStatus" class="px-2 py-1 text-sm rounded-full"></span></p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Created At</p>
                                <p class="mt-1" id="modalProductCreatedAt"></p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Created By</p>
                                <p class="mt-1" id="modalProductUserId"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Product Details -->
                    <div>
                        <h4 class="text-lg font-medium text-gray-900 mb-3">Product Details</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="col-span-2">
                                <p class="text-sm font-medium text-gray-500">Description</p>
                                <p class="mt-1" id="modalProductDescription"></p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Manufacture Date</p>
                                <p class="mt-1" id="modalProductManufactureDate"></p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Expiry Date</p>
                                <p class="mt-1" id="modalProductExpiryDate"></p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Price</p>
                                <p class="mt-1" id="modalProductPrice"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Storage Requirements -->
                    <div>
                        <h4 class="text-lg font-medium text-gray-900 mb-3">Storage Requirements</h4>
                        <div class="space-y-4" id="modalProductRequirements">
                            <!-- Requirements will be dynamically added here -->
                        </div>
                    </div>

                    <!-- Approval Information -->
                    <div>
                        <h4 class="text-lg font-medium text-gray-900 mb-3">Approval Information</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Approved By</p>
                                <p class="mt-1" id="modalProductApprovedBy"></p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Approval Date</p>
                                <p class="mt-1" id="modalProductApprovalDate"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div>
                        <h4 class="text-lg font-medium text-gray-900 mb-3">Notes</h4>
                        <p id="modalProductNotes" class="text-gray-700"></p>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-200">
                <div class="flex justify-end">
                    <button type="button" 
                            class="px-4 py-2 bg-gray-500 text-white rounded-md text-sm font-medium hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                            onclick="closeDetailsModal()">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

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

// Make sure the getStatusClass function handles 'pending' status
function getStatusClass(status) {
    const classes = {
        'created': 'bg-gray-100 text-gray-800',
        'waiting_approval': 'bg-yellow-100 text-yellow-800',
        'approved': 'bg-green-100 text-green-800',
        'active': 'bg-blue-100 text-blue-800',
        'inactive': 'bg-red-100 text-red-800',
        'pending': 'bg-yellow-100 text-yellow-800' // Added pending status
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
}

function formatRequirement(requirement) {
    const units = {
        'temperature': '°C',
        'humidity': '%',
        'pressure': 'hPa'
    };
    const unit = units[requirement.type] || '';
    
    return `
        <div class="bg-gray-50 rounded-lg p-4">
            <div class="flex justify-between items-center mb-2">
                <h5 class="text-sm font-medium text-gray-900 capitalize">${requirement.type}</h5>
            </div>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <p class="text-xs text-gray-500">Min</p>
                    <p class="text-sm font-medium">${requirement.min}${unit}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Max</p>
                    <p class="text-sm font-medium">${requirement.max}${unit}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Target</p>
                    <p class="text-sm font-medium">${requirement.value}${unit}</p>
                </div>
            </div>
        </div>
    `;
}

async function openDetailsModal(productId) {
    try {
        const response = await fetch(`/products/${productId}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (!response.ok) {
            throw new Error('Failed to fetch product details');
        }
        
        const data = await response.json();
        console.log('Raw API response:', data); // Debug log
        
        // Extract product from the array's first element
        const product = data[0].product;
        const user = data[0].user;
        console.log('Extracted product:', product); // Debug log

        if (!product) {
            throw new Error('Product data not found');
        }

        // Update modal content
        document.getElementById('modalProductName').textContent = product.name;
        document.getElementById('modalProductId').textContent = product.id;
        document.getElementById('modalProductDescription').textContent = product.description;
        document.getElementById('modalProductManufactureDate').textContent = formatDate(product.manufactureDate);
        document.getElementById('modalProductExpiryDate').textContent = formatDate(product.expiryDate);
        document.getElementById('modalProductPrice').textContent = `$${product.price.toFixed(2)}`;
        document.getElementById('modalProductCreatedAt').textContent = formatDate(product.createdAt);
        document.getElementById('modalProductUserId').textContent = user.name;
        document.getElementById('modalProductApprovedBy').textContent = product.approvedBy || 'N/A';
        document.getElementById('modalProductApprovalDate').textContent = formatDate(product.approvalDate);
        document.getElementById('modalProductNotes').textContent = product.notes || 'No notes available';

        // Update status with styling
        const statusElem = document.getElementById('modalProductStatus');
        statusElem.textContent = product.status;
        statusElem.className = `px-2 py-1 text-sm rounded-full ${getStatusClass(product.status)}`;

        // Update requirements
        const requirementsContainer = document.getElementById('modalProductRequirements');
        requirementsContainer.innerHTML = product.requirements
            .map(formatRequirement)
            .join('');

        // Show modal
        const modal = document.getElementById('productDetailsModal');
        modal.classList.remove('hidden');
        console.log('Modal should now be visible'); // Debug log
        
    } catch (error) {
        console.error('Error in openDetailsModal:', error);
        alert('Failed to load product details');
    }
}


function closeDetailsModal() {
    document.getElementById('productDetailsModal').classList.add('hidden');
}
</script><?php /**PATH /var/www/html/pharma-api/resources/views/products/partials/details-modal.blade.php ENDPATH**/ ?>