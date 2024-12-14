{{-- resources/views/batches/create.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-3xl mx-auto">
        <h1 class="text-2xl font-bold mb-6">Create Batch from Order #{{ $order->id }}</h1>

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white shadow-md rounded-lg p-6 mb-6">
            <h2 class="text-lg font-semibold mb-4">Order Details</h2>
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <p class="text-sm text-gray-600">Product</p>
                    <p class="font-medium">{{ $order->product->name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Quantity</p>
                    <p class="font-medium">{{ $order->quantity }}</p>
                </div>
            </div>
        </div>

        <form action="{{ route('batches.store') }}" method="POST" class="space-y-6">
            @csrf
            <input type="hidden" name="order_id" value="{{ $order->id }}">

            <div class="bg-white shadow-md rounded-lg p-6">
                <h2 class="text-lg font-semibold mb-4">Batch Details</h2>
                
                <div class="space-y-4">
                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700">Delivery Address</label>
                        <textarea name="address" id="address" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required></textarea>
                    </div>

                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                        <textarea name="notes" id="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-md rounded-lg p-6">
                <h2 class="text-lg font-semibold mb-4">Storage Requirements</h2>
                
                <div id="storage-requirements" class="space-y-4">
                    <div class="storage-requirement border rounded p-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Type</label>
                                <select name="requirements[0][type]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="temperature">Temperature</option>
                                    <option value="humidity">Humidity</option>
                                    <option value="pressure">Pressure</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Unit</label>
                                <select name="requirements[0][unit]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="celsius">Celsius</option>
                                    <option value="fahrenheit">Fahrenheit</option>
                                    <option value="percentage">Percentage</option>
                                    <option value="pascal">Pascal</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4 mt-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Minimum Value</label>
                                <input type="number" step="0.01" name="requirements[0][min_value]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Maximum Value</label>
                                <input type="number" step="0.01" name="requirements[0][max_value]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="button" onclick="addStorageRequirement()" class="mt-4 text-blue-600 hover:text-blue-800">
                    + Add Another Requirement
                </button>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Create Batch</button>
            </div>
        </form>
    </div>
</div>

<script>
let requirementCount = 1;

function addStorageRequirement() {
    const template = `
        <div class="storage-requirement border rounded p-4 mt-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Type</label>
                    <select name="requirements[${requirementCount}][type]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="temperature">Temperature</option>
                        <option value="humidity">Humidity</option>
                        <option value="pressure">Pressure</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Unit</label>
                    <select name="requirements[${requirementCount}][unit]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="celsius">Celsius</option>
                        <option value="fahrenheit">Fahrenheit</option>
                        <option value="percentage">Percentage</option>
                        <option value="pascal">Pascal</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Minimum Value</label>
                    <input type="number" step="0.01" name="requirements[${requirementCount}][min_value]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Maximum Value</label>
                    <input type="number" step="0.01" name="requirements[${requirementCount}][max_value]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                </div>
            </div>
            <button type="button" onclick="removeStorageRequirement(this)" class="mt-2 text-red-600 hover:text-red-800">
                Remove
            </button>
        </div>
    `;
    
    document.getElementById('storage-requirements').insertAdjacentHTML('beforeend', template);
    requirementCount++;
}

function removeStorageRequirement(button) {
    button.closest('.storage-requirement').remove();
}
</script>
@endsection