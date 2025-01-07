{{-- resources/views/orders/create.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-2xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Create New Order</h1>
            <a href="{{ route('products.index') }}" class="text-gray-600 hover:text-gray-800">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
        </div>

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Product Information Card --}}
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Product Details</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm font-medium text-gray-500">Product Name</p>
                    <p class="mt-1">{{ $product['name'] }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Price</p>
                    <p class="mt-1">${{ number_format($product['price'], 2) }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Expiry Date</p>
                    <p class="mt-1">{{ date('Y-m-d', strtotime($product['expiryDate'])) }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Status</p>
                    <p class="mt-1">
                        <span class="px-2 py-1 text-sm rounded-full bg-green-100 text-green-800">
                            {{ ucfirst($product['status']) }}
                        </span>
                    </p>
                </div>
            </div>
        </div>

        <form action="{{ route('orders.store') }}" method="POST" class="space-y-6 bg-white shadow rounded-lg p-6">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product['id'] }}">

            <div>
                <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity</label>
                <input type="number" 
                       name="quantity" 
                       id="quantity" 
                       min="1" 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" 
                       required>
                <p class="mt-1 text-sm text-gray-500">Enter the number of units you want to order</p>
            </div>

            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                <textarea name="notes" 
                          id="notes" 
                          rows="3" 
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                          placeholder="Add any special handling instructions or notes"></textarea>
            </div>

            <div class="flex justify-end space-x-3">
                <a href="{{ route('products.index') }}" 
                   class="bg-gray-100 text-gray-700 px-4 py-2 rounded hover:bg-gray-200">
                    Cancel
                </a>
                <button type="submit" 
                        class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    Create Order
                </button>
            </div>
        </form>
    </div>
</div>
@endsection