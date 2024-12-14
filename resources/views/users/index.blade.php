{{-- index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Users Management</h1>
        <a href="{{ route('users.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded">Add New User</a>
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($users as $user)
                <tr>
                    <td class="px-6 py-4">{{ $user['name'] }}</td>
                    <td class="px-6 py-4">{{ $user['email'] }}</td>
                    <td class="px-6 py-4">{{ $user['role'] }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-sm rounded-full {{ $user['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $user['status'] }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <a href="{{ route('users.show', $user['id']) }}" class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                        <a href="{{ route('users.edit', $user['id']) }}" class="text-green-600 hover:text-green-900 mr-3">Edit</a>
                        <form action="{{ route('users.destroy', $user['id']) }}" method="POST" class="inline">
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