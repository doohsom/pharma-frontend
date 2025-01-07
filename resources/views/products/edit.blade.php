{{-- resources/views/products/edit.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-2xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Edit Product</h1>
            <a href="{{ route('products.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                Back to List
            </a>
        </div>

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('products.update', $product['id']) }}" method="POST" class="space-y-6" id="productForm">
            @csrf
            @method('PUT')
            
            <div class="bg-white rounded-lg shadow p-6 space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Product Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $product['name']) }}" 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200" required>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" id="description" rows="3" 
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">{{ old('description', $product['description']) }}</textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="manufacture_date" class="block text-sm font-medium text-gray-700">Manufacture Date</label>
                        <input type="date" name="manufacture_date" id="manufacture_date" 
                               value="{{ old('manufacture_date', date('Y-m-d', strtotime($product['manufactureDate']))) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200" required>
                    </div>

                    <div>
                        <label for="expiry_date" class="block text-sm font-medium text-gray-700">Expiry Date</label>
                        <input type="date" name="expiry_date" id="expiry_date" 
                               value="{{ old('expiry_date', $product['expiryDate'] ? date('Y-m-d', strtotime($product['expiryDate'])) : '') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="price" class="block text-sm font-medium text-gray-700">Price</label>
                        <input type="number" step="0.01" name="price" id="price" value="{{ old('price', $product['price']) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200" required>
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status" id="status" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                            <option value="pending" {{ old('status', $product['status']) == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="for_approval" {{ old('status', $product['status']) == 'for_approval' ? 'selected' : '' }}>For Approval</option>
                            <option value="approved" {{ old('status', $product['status']) == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ old('status', $product['status']) == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            <option value="inactive" {{ old('status', $product['status']) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                    <textarea name="notes" id="notes" rows="2" 
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">{{ old('notes', $product['notes']) }}</textarea>
                </div>
            </div>

            <!-- Storage Requirements Section -->
            <div class="bg-white rounded-lg shadow p-6 space-y-4">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-700">Storage Requirements</h3>
                    <button type="button" id="addRequirement" 
                            class="bg-green-500 text-white px-3 py-1 rounded text-sm hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500">
                        Add Requirement
                    </button>
                </div>

                <div id="requirementsContainer" class="space-y-4">
                    <!-- Requirements will be dynamically added here -->
                </div>

                <input type="hidden" name="requirements" id="requirementsJson" value="{{ json_encode($product['requirements']) }}">
            </div>

            <div class="flex justify-end space-x-4">
                <a href="{{ route('products.index') }}" 
                   class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    Cancel
                </a>
                <button type="submit" 
                        class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Update Product
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const requirementsContainer = document.getElementById('requirementsContainer');
    const addButton = document.getElementById('addRequirement');
    const form = document.getElementById('productForm');
    let requirementCount = 0;
    const usedTypes = new Set();

    const requirementConfig = {
        'temperature': {
            label: 'Temperature',
            units: [
                { label: 'Celsius (°C)', value: 'celsius' },
                { label: 'Fahrenheit (°F)', value: 'fahrenheit' }
            ],
            defaultMin: 2,
            defaultMax: 8
        },
        'humidity': {
            label: 'Humidity',
            units: [
                { label: 'Relative Humidity (%)', value: 'relative_humidity' },
                { label: 'Absolute Humidity (g/m³)', value: 'absolute_humidity' }
            ],
            defaultMin: 45,
            defaultMax: 55
        },
        'pressure': {
            label: 'Pressure',
            units: [
                { label: 'Hectopascals (hPa)', value: 'hectopascals' },
                { label: 'Millibars (mbar)', value: 'millibars' },
                { label: 'PSI', value: 'psi' }
            ],
            defaultMin: 900,
            defaultMax: 1100
        }
    };

    function createRequirementFields(requirement = null) {
        if (requirementCount >= 3) {
            alert('Maximum 3 storage requirements allowed');
            return;
        }

        const availableTypes = Object.keys(requirementConfig).filter(type => !usedTypes.has(type));
        if (availableTypes.length === 0 && !requirement) {
            alert('All requirement types have been used');
            return;
        }

        const requirementDiv = document.createElement('div');
        requirementDiv.className = 'p-4 border rounded-lg space-y-3 relative bg-gray-50';
        requirementDiv.dataset.requirement = 'true';

        const removeButton = document.createElement('button');
        removeButton.type = 'button';
        removeButton.className = 'absolute top-2 right-2 text-red-500 hover:text-red-700 focus:outline-none';
        removeButton.innerHTML = '×';
        removeButton.onclick = function() {
            const typeSelect = requirementDiv.querySelector('.requirement-type');
            usedTypes.delete(typeSelect.value);
            requirementDiv.remove();
            requirementCount--;
            updateRequirementsJson();
        };

        requirementDiv.innerHTML = `
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-6">
                    <label class="block text-sm font-medium text-gray-700">Type</label>
                    <select class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 requirement-type" required>
                        <option value="">Select type</option>
                        ${(requirement ? [requirement.type] : availableTypes).map(type => 
                            `<option value="${type}" ${requirement && requirement.type === type ? 'selected' : ''}>${requirementConfig[type].label}</option>`
                        ).join('')}
                    </select>
                </div>
                <div class="col-span-6">
                    <label class="block text-sm font-medium text-gray-700">Unit</label>
                    <select class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 requirement-unit" required>
                        <option value="">Select unit</option>
                        ${requirement ? requirementConfig[requirement.type].units.map(unit => 
                            `<option value="${unit.value}" ${requirement.value === unit.value ? 'selected' : ''}>${unit.label}</option>`
                        ).join('') : ''}
                    </select>
                </div>
                <div class="col-span-6">
                    <label class="block text-sm font-medium text-gray-700">Min</label>
                    <input type="number" step="0.1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 requirement-min" 
                           value="${requirement ? requirement.min : ''}" required>
                </div>
                <div class="col-span-6">
                    <label class="block text-sm font-medium text-gray-700">Max</label>
                    <input type="number" step="0.1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 requirement-max" 
                           value="${requirement ? requirement.max : ''}" required>
                </div>
            </div>
        `;

        requirementDiv.appendChild(removeButton);

        const typeSelect = requirementDiv.querySelector('.requirement-type');
        const unitSelect = requirementDiv.querySelector('.requirement-unit');
        const minInput = requirementDiv.querySelector('.requirement-min');
        const maxInput = requirementDiv.querySelector('.requirement-max');

        if (requirement) {
            usedTypes.add(requirement.type);
            typeSelect.previousValue = requirement.type;
        }

        typeSelect.addEventListener('change', function() {
            if (this.previousValue) {
                usedTypes.delete(this.previousValue);
            }

            const selectedType = this.value;
            unitSelect.innerHTML = '<option value="">Select unit</option>';

            if (selectedType) {
                usedTypes.add(selectedType);
                this.previousValue = selectedType;

                requirementConfig[selectedType].units.forEach(unit => {
                    const option = new Option(unit.label, unit.value);
                    unitSelect.add(option);
                });

                minInput.value = requirementConfig[selectedType].defaultMin;
                maxInput.value = requirementConfig[selectedType].defaultMax;
            } else {
                minInput.value = '';
                maxInput.value = '';
            }
            updateRequirementsJson();
        });

        [unitSelect, minInput, maxInput].forEach(input => {
            input.addEventListener('change', updateRequirementsJson);
        });

        requirementsContainer.appendChild(requirementDiv);
        requirementCount++;
    }

    function updateRequirementsJson() {
        const requirements = [];
        document.querySelectorAll('[data-requirement="true"]').forEach(div => {
            const type = div.querySelector('.requirement-type').value;
            const unit = div.querySelector('.requirement-unit').value;
            const min = parseFloat(div.querySelector('.requirement-min').value);
            const max = parseFloat(div.querySelector('.requirement-max').value);

            if (type && unit && !isNaN(min) && !isNaN(max)) {
                requirements.push({ 
                    type,
                    min,
                    max,
                    value: unit
                });
            }
        });

        document.getElementById('requirementsJson').value = JSON.stringify(requirements);
    }

    // Load existing requirements
    const existingRequirements = JSON.parse(document.getElementById('requirementsJson').value);
    if (existingRequirements && Array.isArray(existingRequirements)) {
        existingRequirements.forEach(requirement => {
            createRequirementFields(requirement);
        });
    }

    addButton.addEventListener('click', () => createRequirementFields());

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        updateRequirementsJson();
        
        const requirements = JSON.parse(document.getElementById('requirementsJson').value);
        if (requirements.length === 0) {
            alert('Please add at least one storage requirement');
            return;
        }

        this.submit();
    });
});
</script>
@endpush
@endsection