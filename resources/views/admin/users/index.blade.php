@extends('layouts.app')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto" x-data="{ showAddModal: false }">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-base-content">Admin: User Management</h1>
        <div class="flex gap-2">
            <button @click="showAddModal = true" class="btn btn-primary btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Tambah User
            </button>
            <a href="{{ route('dashboard') }}" class="btn btn-ghost btn-sm">Back to Dashboard</a>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card bg-base-100 shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table table-zebra w-full">
                <thead class="bg-base-200">
                    <tr>
                        <th>Username</th>
                        <th>Name</th>
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
                            <td class="font-medium">{{ $user->username ?? '-' }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email ?? '-' }}</td>
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
                                @elseif($user->bagian_access)
                                     <span class="badge badge-info badge-outline badge-sm">{{ $user->bagian_access }}</span>
                                @else
                                    <span class="text-xs opacity-50">All</span>
                                @endif
                            </td>
                            <td class="opacity-70 text-sm">{{ $user->google_id ? 'Linked' : 'Not Linked' }}</td>
                            <td class="flex gap-2">
                                <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-ghost btn-xs text-info">Edit</a>
                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" id="delete-user-{{ $user->id }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" onclick="confirmModal('Delete User', 'Are you sure you want to remove this user?', 'delete-user-{{ $user->id }}')" class="btn btn-ghost btn-xs text-error">Remove</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add User Modal -->
    <div x-show="showAddModal" x-cloak class="modal modal-open" @keydown.escape.window="showAddModal = false">
        <div class="modal-box max-w-2xl">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-lg">Tambah User Baru</h3>
                <button @click="showAddModal = false" class="btn btn-sm btn-circle btn-ghost">✕</button>
            </div>
            
            <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-4">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-control w-full">
                        <label class="label"><span class="label-text font-medium">Username <span class="text-error">*</span></span></label>
                        <input type="text" name="username" required placeholder="username untuk login" class="input input-bordered w-full" value="{{ old('username') }}">
                    </div>
                    <div class="form-control w-full">
                        <label class="label"><span class="label-text font-medium">Name <span class="text-error">*</span></span></label>
                        <input type="text" name="name" required placeholder="Nama lengkap" class="input input-bordered w-full" value="{{ old('name') }}">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-control w-full">
                        <label class="label"><span class="label-text font-medium">Email Address <span class="text-gray-400">(opsional)</span></span></label>
                        <input type="email" name="email" placeholder="email@example.com" class="input input-bordered w-full" value="{{ old('email') }}">
                    </div>
                    <div class="form-control w-full">
                        <label class="label"><span class="label-text font-medium">Password <span class="text-error">*</span></span></label>
                        <input type="password" name="password" required placeholder="Min. 6 karakter" class="input input-bordered w-full" minlength="6">
                    </div>
                </div>

                <div class="form-control w-full">
                    <label class="label"><span class="label-text font-medium">Role</span></label>
                    <select name="role" class="select select-bordered w-full">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <div class="form-control w-full">
                    <label class="label"><span class="label-text font-bold">Bagian Access</span></label>
                    <div class="label-text-alt mb-2 text-gray-500">Pilih bagian mana yang dapat diakses user ini. Kosongkan untuk akses semua bagian.</div>
                    
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2 p-4 bg-base-200 rounded-lg">
                        @foreach($bagians as $bagian)
                            <label class="cursor-pointer label justify-start gap-3 rounded-lg p-2 hover:bg-base-300">
                                <input type="checkbox" name="bagian_access[]" value="{{ $bagian->value }}" class="checkbox checkbox-primary checkbox-sm">
                                <span class="label-text">{{ $bagian->label() }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="modal-action">
                    <button type="button" @click="showAddModal = false" class="btn btn-ghost">Batal</button>
                    <button type="submit" class="btn btn-primary">Tambah User</button>
                </div>
            </form>
        </div>
        <div class="modal-backdrop bg-black/50" @click="showAddModal = false"></div>
    </div>
</div>
@endsection
