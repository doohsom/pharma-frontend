{{-- resources/views/batches/create.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-3xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Create Batch</h1>
            <a href="{{ route('orders.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                Back to Orders
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

        <!-- Order Details Section -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Order Details</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Order ID</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $order['id'] }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Product ID</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $order['productId'] }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Quantity</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $order['quantity'] }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Status</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $order['status'] }}</p>
                </div>
            </div>
        </div>

        <!-- Batch Creation Form -->
        <form action="{{ route('batches.store') }}" method="POST" class="space-y-6">
            @csrf
            <input type="hidden" name="order_id" value="{{ $order['id'] }}">
            <input type="hidden" name="product_id" value="{{ $order['productId'] }}">

            <!-- Batch Details Section -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Batch Details</h2>
                
                <!-- Logistics Assignment -->
                <div class="mb-4">
                    <label for="logistics_id" class="block text-sm font-medium text-gray-700">Assign Logistics Partner</label>
                    <select name="logistics_id" id="logistics_id" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-200">
                        <option value="">Select Logistics Partner</option>
                        @foreach($logisticsPartners as $partner)
                            <option value="{{ $partner['id'] }}">{{ $partner['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-6">
                    <label for="notes" class="block text-sm font-medium text-gray-700">Batch Notes</label>
                    <textarea name="notes" id="notes" rows="3" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-200"
                        placeholder="Add notes about this batch...">{{ old('notes') }}</textarea>
                </div>

            </div>

            <div class="flex justify-end space-x-4">
                <a href="{{ route('orders.index') }}" 
                   class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-500">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-4 py-2 bg-blue-500 text-white text-sm font-medium rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Create Batch
                </button>
            </div>
        </form>
    </div>
</div>

@endsection