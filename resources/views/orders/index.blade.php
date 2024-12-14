{{-- resources/views/orders/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Orders Management</h1>
        <a href="{{ route('orders.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded">Create New Order</a>
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
                @foreach($orders as $order)
                <tr>
                    <td class="px-6 py-4">{{ $order->id }}</td>
                    <td class="px-6 py-4">{{ $order->product->name }}</td>
                    <td class="px-6 py-4">{{ $order->sender->name }}</td>
                    <td class="px-6 py-4">{{ $order->receiver->name }}</td>
                    <td class="px-6 py-4">{{ $order->quantity }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-sm rounded-full 
                            {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $order->status === 'processing' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $order->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $order->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        @if($order->status === 'BATCHED')
                            <a href="{{ route('batches.create', $order->id) }}" class="bg-green-500 text-white px-4 py-2 rounded">
                                Create Batch
                            </a>
                        @endif
                        <a href="{{ route('orders.show', $order->id) }}" class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                        <a href="{{ route('orders.edit', $order->id) }}" class="text-green-600 hover:text-green-900 mr-3">Edit</a>
                        <form action="{{ route('orders.destroy', $order->id) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
