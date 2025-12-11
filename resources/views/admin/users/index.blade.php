@extends('layouts.app')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto" x-data>
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-base-content">Admin: User Whitelist Management</h1>
        <a href="{{ route('dashboard') }}" class="btn btn-error btn-sm text-white">Back to Dashboard</a>
    </div>

    <!-- Add User Form -->
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <h2 class="card-title text-lg font-bold mb-2">Add New User</h2>
            <form action="{{ route('admin.users.store') }}" method="POST" class="flex flex-col md:flex-row gap-4 items-end">
                @csrf
                <div class="form-control w-full md:w-1/2">
                    <label class="label"><span class="label-text font-medium">Email Address</span></label>
                    <input type="email" name="email" required class="input input-bordered w-full">
                </div>
                <div class="form-control w-full md:w-1/4">
                    <label class="label"><span class="label-text font-medium">Role</span></label>
                    <select name="role" class="select select-bordered w-full">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <div class="form-control w-full md:w-auto">
                    <button type="submit" class="btn btn-primary w-full md:w-auto">Add User</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card bg-base-100 shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table table-zebra w-full">
                <thead class="bg-base-200">
                    <tr>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Bagian Access</th>
                        <th>Google ID</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td class="font-medium">{{ $user->email }}</td>
                            <td>
                                 @if($user->role === 'admin')
                                    <span class="badge badge-error text-white badge-sm">Admin</span>
                                 @else
                                    <span class="badge badge-success text-white badge-sm">User</span>
                                 @endif
                            </td>
                            <td>
                                @if(is_array($user->bagian_access) && count($user->bagian_access) > 0)
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($user->bagian_access as $access)
                                            <span class="badge badge-info badge-outline badge-sm">{{ $access }}</span>
                                        @endforeach
                                    </div>
                                @elseif($user->bagian_access) <!-- Legacy or string fallback -->
                                     <span class="badge badge-info badge-outline badge-sm">{{ $user->bagian_access }}</span>
                                @else
                                    <span class="text-xs opacity-50">All</span>
                                @endif
                            </td>
                            <td class="opacity-70 text-sm">{{ $user->google_id ?? 'Not Linked' }}</td>
                            <td class="flex gap-2">
                                <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-ghost btn-xs text-info">Edit</a>
                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" id="delete-user-{{ $user->id }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" onclick="confirmModal('Delete User', 'Are you sure you want to remove this user?', 'delete-user-{{ $user->id }}')" class="btn btn-ghost btn-xs text-error">Remove</button>
                                </form>
                            </td>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
