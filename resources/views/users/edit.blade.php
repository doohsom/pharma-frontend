@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold mb-6">Edit User</h1>

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('users.update', $user['id']) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')
            
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                <input type="text" name="name" id="name" value="{{ $user['name'] }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" id="email" value="{{ $user['email'] }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
            </div>

            <div>
                <label for="phone_number" class="block text-sm font-medium text-gray-700">Phone Number</label>
                <input type="tel" name="phoneNumber" id="phoneNumber" value="{{ $user['phoneNumber'] }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
            </div>

            <div>
                <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                <select name="role" id="role" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="user" {{ $user['role'] === 'manufacturer' ? 'selected' : '' }}>Manufacturer</option>
                    <option value="admin" {{ $user['role'] === 'logistics' ? 'selected' : '' }}>Logistics</option>
                    <option value="manager" {{ $user['role'] === 'vendor' ? 'selected' : '' }}>Vendor</option>
                    <option value="manager" {{ $user['role'] === 'regulator' ? 'selected' : '' }}>Regulator</option>
                </select>
            </div>

            <div>
                <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                <textarea name="address" id="address" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ $user['address'] }}</textarea>
            </div>

            <div>
                <label for="complianceDocument" class="block text-sm font-medium text-gray-700">Compliance Document</label>
                @if($user['complianceDocument'])
                    <p class="text-sm text-gray-500 mb-2">Current document: {{ basename($user['complianceDocument']) }}</p>
                @endif
                <input type="file" name="complianceDocument" id="complianceDocument" class="mt-1 block w-full">
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="active" {{ $user['status'] === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ $user['status'] === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="pending" {{ $user['status'] === 'pending' ? 'selected' : '' }}>Pending</option>
                </select>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Update User</button>
            </div>
        </form>
    </div>
</div>
@endsection