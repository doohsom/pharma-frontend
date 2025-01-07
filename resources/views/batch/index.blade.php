{{-- resources/views//batches/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Batch Management</h1>
        <a href="" class="bg-blue-500 text-white px-4 py-2 rounded">Create New Batch</a>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Batch ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order Quantity</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($batches as $batch)
                <tr>
                    <td class="px-6 py-4">{{ $batch['id'] }}</td>
                    <td class="px-6 py-4">{{ $batch['product']['name'] }}</td>
                    <td class="px-6 py-4">{{ $batch['order']['quantity'] }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-sm rounded-full 
                            {{ $batch['status'] === 'created' ? 'bg-blue-100 text-blue-800' : 
                               ($batch['status'] === 'in_transit' ? 'bg-yellow-100 text-yellow-800' : 
                               'bg-green-100 text-green-800') }}">
                            {{ ucfirst($batch['status']) }}
                        </span>
                    </td>
                    <td class="px-6 py-4">{{ date('Y-m-d', strtotime($batch['createdAt'])) }}</td>
                    <td class="px-6 py-4">
                        <div class="flex space-x-2">
                            <button onclick="openDetailsModal('{{ $batch['id'] }}')" 
                                class="text-blue-600 hover:text-blue-800 focus:outline-none flex items-center">
                                <svg class="w-4 h-4 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
                                    <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                Details
                            </button>
                            <button onclick="openStatusModal('{{ $batch['id'] }}')" 
                                class="text-yellow-600 hover:text-yellow-800 focus:outline-none flex items-center">
                                <svg class="w-4 h-4 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M23 4v6h-6"/>
                                    <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
                                </svg>
                                Update
                            </button>
                            @if($batch['status'] === 'created')
                            <button onclick="openSensorModal('{{ $batch['id'] }}')" 
                                class="text-green-600 hover:text-green-800 focus:outline-none flex items-center">
                                <svg class="w-4 h-4 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 2v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                Add Sensor
                            </button>
                            <button onclick="openSensorsModal('{{ $batch['id'] }}')" 
                                class="text-indigo-600 hover:text-indigo-800 focus:outline-none flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="3"></circle>
                                    <line x1="3" y1="12" x2="9" y2="12"></line>
                                    <line x1="15" y1="12" x2="21" y2="12"></line>
                                </svg>
                                <span class="ml-1">View Sensors</span>
                            </button>
                            @endif
                            @if($batch['status'] === 'in_transit')
                            <a href="/batches/{{ $batch['id'] }}/dashboard" 
                               class="text-purple-600 hover:text-purple-800 focus:outline-none flex items-center">
                                <svg class="w-4 h-4 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                                    <polyline points="9 22 9 12 15 12 15 22"/>
                                </svg>
                                View Dashboard
                            </a>
                            <button onclick="openSensorsModal('{{ $batch['id'] }}')" 
                                class="text-indigo-600 hover:text-indigo-800 focus:outline-none flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="3"></circle>
                                    <line x1="3" y1="12" x2="9" y2="12"></line>
                                    <line x1="15" y1="12" x2="21" y2="12"></line>
                                </svg>
                                <span class="ml-1">View Sensors</span>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center">No batches found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Status Update Modal --}}
<div id="statusModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="flex flex-col">
            <div class="flex justify-between items-center pb-3">
                <h3 class="text-xl font-bold">Update Batch Status</h3>
                <button onclick="closeStatusModal()" class="text-gray-400 hover:text-gray-500">&times;</button>
            </div>
            <form id="statusUpdateForm" class="mt-4">
                @csrf
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="status">
                        Status
                    </label>
                    <select id="batchStatusSelect" name="status" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="created">Created</option>
                        <option value="in_transit">In Transit</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        Update Status
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Details Modal --}}
<div id="detailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
    <div class="relative top-20 mx-auto p-5 border w-4/5 shadow-lg rounded-md bg-white">
        <div class="flex flex-col">
            <div class="flex justify-between items-center pb-3">
                <h3 class="text-xl font-bold">Batch Details</h3>
                <button onclick="closeDetailsModal()" class="text-gray-400 hover:text-gray-500">&times;</button>
            </div>
            <div class="mt-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <h4 class="font-semibold mb-2">Product Information</h4>
                        <p><strong>Name:</strong> <span id="productName"></span></p>
                        <p><strong>Description:</strong> <span id="productDescription"></span></p>
                        <p><strong>Manufacture Date:</strong> <span id="manufactureDate"></span></p>
                        <p><strong>Expiry Date:</strong> <span id="expiryDate"></span></p>
                    </div>
                    <div>
                        <h4 class="font-semibold mb-2">Requirements</h4>
                        <div id="requirements"></div>
                    </div>
                    <div>
                        <h4 class="font-semibold mb-2">Order Information</h4>
                        <p><strong>Order ID:</strong> <span id="orderId"></span></p>
                        <p><strong>Quantity:</strong> <span id="orderQuantity"></span></p>
                        <p><strong>Sender:</strong> <span id="senderId"></span></p>
                        <p><strong>Receiver:</strong> <span id="receiverId"></span></p>
                    </div>
                    <div>
                        <h4 class="font-semibold mb-2">Batch Information</h4>
                        <p><strong>Status:</strong> <span id="batchStatus"></span></p>
                        <p><strong>Created:</strong> <span id="batchCreated"></span></p>
                        <p><strong>Updated:</strong> <span id="batchUpdated"></span></p>
                        <p><strong>Notes:</strong> <span id="batchNotes"></span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Sensor Creation Modal --}}
<div id="sensorModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
    <div class="relative top-20 mx-auto p-5 border w-[600px] shadow-lg rounded-md bg-white">
        <div class="flex flex-col">
            <div class="flex justify-between items-center pb-3">
                <h3 class="text-xl font-bold">Add Sensors</h3>
                <button onclick="closeSensorModal()" class="text-gray-400 hover:text-gray-500">&times;</button>
            </div>
            <form id="sensorCreateForm" class="mt-4">
                @csrf
                <div id="sensorsContainer">
                    <!-- Sensor entries will be added here -->
                </div>
                <div class="flex justify-between mt-4">
                    <button type="button" 
                            onclick="addSensorEntry()" 
                            class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 flex items-center">
                        <svg class="w-4 h-4 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 5v14m-7-7h14"/>
                        </svg>
                        Add Another Sensor
                    </button>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        Create Sensors
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Sensors View Modal --}}
<div id="sensorsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
    <div class="relative top-20 mx-auto p-5 border w-3/4 shadow-lg rounded-md bg-white">
        <div class="flex flex-col">
            <div class="flex justify-between items-center pb-3 border-b">
                <h3 class="text-xl font-bold">Batch Sensors</h3>
                <button onclick="closeSensorsModal()" class="text-gray-400 hover:text-gray-500">&times;</button>
            </div>
            <div class="mt-4">
                <div id="sensorsList" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Sensors will be populated here -->
                </div>
                <div id="noSensorsMessage" class="hidden text-center py-4 text-gray-500">
                    No sensors found for this batch.
                </div>
            </div>
        </div>
    </div>
</div>


<script>
function openDetailsModal(batchId) {
    // Fetch batch details from your API endpoint
    fetch(`/batches/${batchId}`)
        .then(response => response.json())
        .then(data => {
            // Populate modal with batch data
            document.getElementById('productName').textContent = data.product.name;
            document.getElementById('productDescription').textContent = data.product.description;
            document.getElementById('manufactureDate').textContent = new Date(data.product.manufactureDate).toLocaleDateString();
            document.getElementById('expiryDate').textContent = new Date(data.product.expiryDate).toLocaleDateString();
            
            // Populate requirements
            const requirementsHtml = data.product.requirements.map(req => 
                `<p><strong>${req.type}:</strong> ${req.value} (Range: ${req.min}-${req.max})</p>`
            ).join('');
            document.getElementById('requirements').innerHTML = requirementsHtml;
            
            // Order information
            document.getElementById('orderId').textContent = data.order.id;
            document.getElementById('orderQuantity').textContent = data.order.quantity;
            document.getElementById('senderId').textContent = data.order.senderId;
            document.getElementById('receiverId').textContent = data.order.receiverId;
            
            // Batch information
            document.getElementById('batchStatus').textContent = data.status;
            document.getElementById('batchCreated').textContent = new Date(data.createdAt).toLocaleString();
            document.getElementById('batchUpdated').textContent = new Date(data.updatedAt).toLocaleString();
            document.getElementById('batchNotes').textContent = data.notes;
            
            // Show modal
            document.getElementById('detailsModal').classList.remove('hidden');
        });
}

function closeDetailsModal() {
    document.getElementById('detailsModal').classList.add('hidden');
}

let currentBatchId = null;

function openStatusModal(batchId) {
    currentBatchId = batchId;
    document.getElementById('statusModal').classList.remove('hidden');
    
    // Get current status and set it in the select
    fetch(`/batches/${batchId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('batchStatusSelect').value = data.status;
        });
}

function closeStatusModal() {
    document.getElementById('statusModal').classList.add('hidden');
    currentBatchId = null;
}

document.getElementById('statusUpdateForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!currentBatchId) return;

    const status = document.getElementById('batchStatusSelect').value;
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch(`/batches/${currentBatchId}/status`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token
        },
        body: JSON.stringify({ status: status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            alert(data.error);
            return;
        }
        
        // Update the status in the table
        const statusCell = document.querySelector(`tr[data-batch-id="${currentBatchId}"] .status-cell`);
        if (statusCell) {
            statusCell.innerHTML = `
                <span class="px-2 py-1 text-sm rounded-full 
                    ${status === 'created' ? 'bg-blue-100 text-blue-800' : 
                    (status === 'in_transit' ? 'bg-yellow-100 text-yellow-800' : 
                    'bg-green-100 text-green-800')}">
                    ${status.replace('_', ' ').charAt(0).toUpperCase() + status.slice(1)}
                </span>
            `;
        }
        
        closeStatusModal();
        
        // Show success message
        const successAlert = document.createElement('div');
        successAlert.className = 'bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4';
        successAlert.innerHTML = 'Status updated successfully';
        document.querySelector('.container').insertBefore(successAlert, document.querySelector('.bg-white'));
        
        // Remove success message after 3 seconds
        setTimeout(() => {
            successAlert.remove();
        }, 3000);
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the status');
    });
});

// Add error handling to the details modal fetch
function openDetailsModal(batchId) {
    fetch(`/batches/${batchId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            document.getElementById('productName').textContent = data.product.name;
            document.getElementById('productDescription').textContent = data.product.description;
            document.getElementById('manufactureDate').textContent = new Date(data.product.manufactureDate).toLocaleDateString();
            document.getElementById('expiryDate').textContent = new Date(data.product.expiryDate).toLocaleDateString();
            
            const requirementsHtml = data.product.requirements.map(req => 
                `<p><strong>${req.type}:</strong> ${req.value} (Range: ${req.min}-${req.max})</p>`
            ).join('');
            document.getElementById('requirements').innerHTML = requirementsHtml;
            
            document.getElementById('orderId').textContent = data.order.id;
            document.getElementById('orderQuantity').textContent = data.order.quantity;
            document.getElementById('senderId').textContent = data.order.senderId;
            document.getElementById('receiverId').textContent = data.order.receiverId;
            
            document.getElementById('batchStatus').textContent = data.status;
            document.getElementById('batchCreated').textContent = new Date(data.createdAt).toLocaleString();
            document.getElementById('batchUpdated').textContent = new Date(data.updatedAt).toLocaleString();
            document.getElementById('batchNotes').textContent = data.notes;
            
            document.getElementById('detailsModal').classList.remove('hidden');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while fetching batch details');
        });
}

let sensorBatchId = null;

function openSensorModal(batchId) {
    sensorBatchId = batchId;
    document.getElementById('sensorModal').classList.remove('hidden');
    // Reset form
    document.getElementById('sensorCreateForm').reset();
}

function closeSensorModal() {
    document.getElementById('sensorModal').classList.add('hidden');
    sensorBatchId = null;
}

function showAlert(message, type = 'success') {
    const alertDiv = document.createElement('div');
    alertDiv.className = type === 'success' 
        ? 'bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4'
        : 'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4';
    alertDiv.innerHTML = message;
    document.querySelector('.container').insertBefore(alertDiv, document.querySelector('.bg-white'));
    
    setTimeout(() => alertDiv.remove(), 3000);
}

document.getElementById('sensorCreateForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!sensorBatchId) return;

    const formData = {
        batch_id: sensorBatchId,
        type: document.getElementById('sensorType').value,
        location: document.getElementById('location').value
    };

    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch('/sensors', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(data => {
                throw new Error(data.error || 'Failed to create sensor');
            });
        }
        return response.json();
    })
    .then(data => {
        closeSensorModal();
        showAlert('Sensor created successfully');
        setTimeout(() => window.location.reload(), 1500);
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert(error.message, 'error');
    });
});

function openSensorsModal(batchId) {
    document.getElementById('sensorsModal').classList.remove('hidden');
    fetchAndDisplaySensors(batchId);
}

function closeSensorsModal() {
    document.getElementById('sensorsModal').classList.add('hidden');
    document.getElementById('sensorsList').innerHTML = '';
}

function openSensorsModal(batchId) {
    document.getElementById('sensorsModal').classList.remove('hidden');
    fetchAndDisplaySensors(batchId);
}

function fetchAndDisplaySensors(batchId) {
    const sensorsList = document.getElementById('sensorsList');
    const noSensorsMessage = document.getElementById('noSensorsMessage');
    
    // Show loading state
    sensorsList.innerHTML = `
        <div class="col-span-full flex justify-center items-center py-8">
            <svg class="animate-spin h-8 w-8 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>`;

    // Fetch sensors for specific batch
    fetch(`/sensors/batch/${encodeURIComponent(batchId)}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to fetch sensors');
            }
            return response.json();
        })
        .then(data => {
            sensorsList.innerHTML = '';
            
            if (data.sensors && data.sensors.length > 0) {
                noSensorsMessage.classList.add('hidden');
                data.sensors.forEach(sensor => {
                    sensorsList.innerHTML += createSensorCard(sensor);
                });
            } else {
                noSensorsMessage.classList.remove('hidden');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            sensorsList.innerHTML = `
                <div class="col-span-full text-center text-red-600 py-4">
                    Failed to load sensors. Please try again.
                </div>`;
        });
}

// createSensorCard function remains the same

function createSensorCard(sensor) {
    const typeIcons = {
        temperature: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 14.76V3.5a2.5 2.5 0 0 0-5 0v11.26a4.5 4.5 0 1 0 5 0z"></path></svg>`,
        humidity: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"></path></svg>`,
        pressure: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M8 12h8"></path></svg>`
    };

    const typeColors = {
        temperature: 'text-red-500',
        humidity: 'text-blue-500',
        pressure: 'text-purple-500'
    };

    return `
        <div class="bg-white rounded-lg shadow p-4 border">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                    <span class="${typeColors[sensor.type] || 'text-gray-500'} mr-2">
                        ${typeIcons[sensor.type] || ''}
                    </span>
                    <h4 class="font-semibold">${sensor.type.charAt(0).toUpperCase() + sensor.type.slice(1)} Sensor</h4>
                </div>
                <span class="text-sm text-gray-500">${sensor.id}</span>
            </div>
            <div class="space-y-2">
                <p class="text-sm"><span class="font-medium">Location:</span> ${sensor.location}°</p>
                <p class="text-sm"><span class="font-medium">Status:</span> 
                    <span class="px-2 py-1 text-xs rounded-full 
                        ${sensor.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">
                        ${sensor.status || 'N/A'}
                    </span>
                </p>
            </div>
        </div>
    `;
}

function createSensorEntry(index) {
    return `
        <div class="sensor-entry mb-6 p-4 border rounded-lg ${index > 0 ? 'mt-4' : ''}">
            <div class="flex justify-between items-center mb-2">
                <h4 class="font-semibold">Sensor ${index + 1}</h4>
                ${index > 0 ? `
                    <button type="button" onclick="removeSensorEntry(this)" 
                            class="text-red-500 hover:text-red-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>` : ''
                }
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        Sensor Type
                    </label>
                    <select name="sensors[${index}][type]" 
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="temperature">Temperature</option>
                        <option value="humidity">Humidity</option>
                        <option value="pressure">Pressure</option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        Location (angle)
                    </label>
                    <input type="number" 
                           name="sensors[${index}][location]" 
                           min="0" 
                           max="360"
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                           placeholder="Enter angle (0-360)"
                           required>
                </div>
            </div>
        </div>
    `;
}

function addSensorEntry() {
    const container = document.getElementById('sensorsContainer');
    const index = container.children.length;
    const sensorHtml = createSensorEntry(index);
    container.insertAdjacentHTML('beforeend', sensorHtml);
}

function removeSensorEntry(button) {
    button.closest('.sensor-entry').remove();
    // Renumber remaining sensors
    document.querySelectorAll('.sensor-entry h4').forEach((header, index) => {
        header.textContent = `Sensor ${index + 1}`;
    });
}

function openSensorModal(batchId) {
    sensorBatchId = batchId;
    document.getElementById('sensorModal').classList.remove('hidden');
    // Reset form and add initial sensor entry
    const container = document.getElementById('sensorsContainer');
    container.innerHTML = '';
    addSensorEntry();
}

// Modify the form submit handler
document.getElementById('sensorCreateForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!sensorBatchId) return;

    const formData = {
        sensors: Array.from(document.querySelectorAll('.sensor-entry')).map(entry => ({
            batch_id: sensorBatchId,
            type: entry.querySelector('select').value,
            location: entry.querySelector('input[type="number"]').value
        }))
    };

    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch('/sensors', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(data => {
                throw new Error(data.error || 'Failed to create sensors');
            });
        }
        return response.json();
    })
    .then(data => {
        closeSensorModal();
        showAlert('Sensors created successfully');
        setTimeout(() => window.location.reload(), 1500);
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert(error.message, 'error');
    });
});
</script>
@endsection
