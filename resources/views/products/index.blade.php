{{-- resources/views/products/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Products Management</h1>
        <a href="{{ route('products.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded">Add New Product</a>
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($products as $product)
                <tr>
                    <td class="px-6 py-4">{{ $product['name'] }}</td>
                    <td class="px-6 py-4">{{ $product['description'] }}</td>
                    <td class="px-6 py-4">${{ number_format($product['price'], 2) }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-sm rounded-full {{ $product['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $product['status'] }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <button onclick="openDetailsModal('{{ $product['id'] }}')" 
                            class="text-blue-600 hover:text-blue-800 focus:outline-none">
                            View Details
                        </button>
                        <button onclick="openStatusModal('{{ $product['id'] }}')" 
                            class="text-blue-600 hover:text-blue-800 focus:outline-none">
                            Update Status
                        </button>
                        <form action="{{ route('products.destroy', $product['id']) }}" method="POST" class="inline">
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
@include('products.partials.status-modal')
@include('products.partials.details-modal')
@endsection