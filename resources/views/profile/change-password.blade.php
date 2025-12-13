@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto py-10" x-data>
    <div class="mb-6 flex items-center justify-between">
         <h1 class="text-2xl font-bold text-base-content">Ubah Password</h1>
         <a href="{{ route('dashboard') }}" class="btn btn-ghost">Kembali</a>
    </div>

    <div class="card bg-base-100 shadow-xl">
        <form action="{{ route('profile.password.update') }}" method="POST" class="card-body">
            @csrf
            
            <div class="form-control w-full">
                <label class="label"><span class="label-text font-medium">Password Saat Ini</span></label>
                <input type="password" name="current_password" required class="input input-bordered w-full" placeholder="Masukkan password saat ini">
                @error('current_password')
                    <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                @enderror
            </div>

            <div class="form-control w-full mt-4">
                <label class="label"><span class="label-text font-medium">Password Baru</span></label>
                <input type="password" name="password" required class="input input-bordered w-full" placeholder="Minimal 6 karakter" minlength="6">
                @error('password')
                    <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                @enderror
            </div>

            <div class="form-control w-full mt-4">
                <label class="label"><span class="label-text font-medium">Konfirmasi Password Baru</span></label>
                <input type="password" name="password_confirmation" required class="input input-bordered w-full" placeholder="Ulangi password baru">
            </div>

            <div class="card-actions justify-end mt-6">
                <button type="submit" class="btn btn-primary">Ubah Password</button>
            </div>
        </form>
    </div>
</div>
@endsection
