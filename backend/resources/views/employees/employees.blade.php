@extends('layouts.app')

@section('content')
<div class="p-6">
  <div class="flex justify-between items-center mb-4">
    <h1 class="text-xl font-semibold text-gray-700">Employees</h1>
    <a href="#" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
      + Add Employee
    </a>
  </div>

  <div class="bg-white p-4 rounded-lg shadow mb-4">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div>
        <label class="text-sm text-gray-600">Role</label>
        <select class="w-full border rounded-md px-3 py-2 text-sm">
          <option>All</option>
          <option>Admin</option>
          <option>Professional</option>
          <option>Frontdesk</option>
        </select>
      </div>
      <div>
        <label class="text-sm text-gray-600">Status</label>
        <select class="w-full border rounded-md px-3 py-2 text-sm">
          <option>All</option>
          <option>Active</option>
          <option>Inactive</option>
        </select>
      </div>
      <div>
        <label class="text-sm text-gray-600">Search</label>
        <input type="text" placeholder="Type employee name" 
               class="w-full border rounded-md px-3 py-2 text-sm">
      </div>
    </div>
  </div>

  <div class="bg-white rounded-lg shadow overflow-x-auto">
    <table class="min-w-full text-sm text-left">
      <thead class="bg-gray-100">
        <tr>
          <th class="px-4 py-2 font-medium text-gray-700">Name</th>
          <th class="px-4 py-2 font-medium text-gray-700">Email</th>
          <th class="px-4 py-2 font-medium text-gray-700">Role</th>
          <th class="px-4 py-2 font-medium text-gray-700">Status</th>
          <th class="px-4 py-2 font-medium text-gray-700">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($usuarios as $usuario)
          <tr class="border-b hover:bg-gray-50">
            <td class="px-4 py-2">{{ $usuario->name }}</td>
            <td class="px-4 py-2">{{ $usuario->email }}</td>
            <td class="px-4 py-2 capitalize">{{ $usuario->role }}</td>
            <td class="px-4 py-2">
              @if($usuario->active)
                <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-700 rounded">Active</span>
              @else
                <span class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-700 rounded">Inactive</span>
              @endif
            </td>
            <td class="px-4 py-2 flex space-x-2">
              <a href="#" class="text-blue-600 hover:underline text-sm">Edit</a>
              <a href="#" class="text-red-600 hover:underline text-sm">Delete</a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="text-center text-gray-500 py-4">No employees found.</td>
          </tr>
        @endforelse
      </tbody>
    </table>

    <div class="p-4">
      {{ $usuarios->links() }}
    </div>
  </div>
</div>
@endsection
