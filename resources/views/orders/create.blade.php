{-- resources/views/orders/create.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold mb-6">Create New Order</h1>

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('orders.store') }}" method="POST" class="space-y-6">
            @csrf
            <div>
                <label for="product_id" class="block text-sm font-medium text-gray-700">Product</label>
                <select name="product_id" id="product_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }} - ${{ number_format($product->price, 2) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="receiver_id" class="block text-sm font-medium text-gray-700">Receiver</label>
                <select name="receiver_id" id="receiver_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity</label>
                <input type="number" name="quantity" id="quantity" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="pending">Pending</option>
                    <option value="processing">Processing</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Create Order</button>
            </div>
        </form>
    </div>
</div>
@endsection