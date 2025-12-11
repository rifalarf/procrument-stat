@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto py-10" x-data>
    <div class="mb-6 flex items-center justify-between">
         <h1 class="text-2xl font-bold text-base-content">Edit User Access</h1>
         <a href="{{ route('admin.users.index') }}" class="btn btn-ghost">Back</a>
    </div>

    <div class="card bg-base-100 shadow-xl">
        <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="card-body">
            @csrf
            @method('PUT')
            
            <div class="form-control w-full">
                <label class="label"><span class="label-text font-medium">Email</span></label>
                <input type="email" value="{{ $user->email }}" disabled class="input input-bordered w-full opacity-70">
            </div>

            <div class="form-control w-full">
                <label class="label"><span class="label-text font-medium">Role</span></label>
                <select name="role" class="select select-bordered w-full">
                    <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>User</option>
                    <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
            </div>

            <div class="form-control w-full mt-4">
                <label class="label"><span class="label-text font-bold">Bagian Access</span></label>
                <div class="label-text-alt mb-2">Select which divisions this user can access. Leave all unchecked for no restrictions (or strict 'none' depending on policy). Currently assuming unchecked = no explicit restrictions if handling null, but let's be explicit.</div>
                
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    @foreach($bagians as $bagian)
                        <label class="cursor-pointer label justify-start gap-3 rounded-lg p-3 hover:bg-base-200">
                            <input type="checkbox" name="bagian_access[]" value="{{ $bagian->value }}" 
                                class="checkbox checkbox-primary"
                                {{ in_array($bagian->value, $user->bagian_access ?? []) ? 'checked' : '' }}
                            >
                            <span class="label-text font-medium">{{ $bagian->label() }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="card-actions justify-end mt-6">
                <button type="submit" class="btn btn-primary">Update User</button>
            </div>
        </form>
    </div>
</div>
@endsection
