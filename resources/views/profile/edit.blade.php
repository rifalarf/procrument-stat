@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto py-10">
    <h1 class="text-2xl font-bold mb-6 text-base-content">My Profile</h1>

    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <div class="flex items-center gap-4 mb-6">
                <div class="avatar placeholder">
                    <div class="bg-neutral-focus text-neutral-content rounded-full w-16 h-16 flex items-center justify-center text-xl font-bold bg-gray-300">
                        <span>{{ substr($user->name, 0, 1) }}</span>
                    </div>
                </div>
                <div>
                    <h2 class="text-xl font-semibold">{{ $user->name }}</h2>
                    <p class="text-gray-500">{{ $user->email }}</p>
                    <p class="text-xs text-gray-400 uppercase mt-1">{{ $user->role }}</p>
                </div>
            </div>

            <hr class="my-4 border-base-200">

            <form action="{{ route('profile.update') }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <h3 class="text-lg font-medium">Change Password</h3>

                @if(!empty($user->password))
                <div class="form-control">
                    <label class="label"><span class="label-text">Current Password</span></label>
                    <input type="password" name="current_password" class="input input-bordered">
                    @error('current_password') <span class="text-error text-sm">{{ $message }}</span> @enderror
                </div>
                @else
                <div class="alert alert-info shadow-sm text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>You haven't set a password yet (Google Login only). You can set one below.</span>
                </div>
                @endif

                <div class="form-control">
                    <label class="label"><span class="label-text">New Password</span></label>
                    <input type="password" name="password" class="input input-bordered">
                    @error('password') <span class="text-error text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="form-control">
                    <label class="label"><span class="label-text">Confirm New Password</span></label>
                    <input type="password" name="password_confirmation" class="input input-bordered">
                </div>

                <div class="card-actions justify-end mt-4">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
