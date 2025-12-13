<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="corporate">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Procurement Status</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="bg-base-200 min-h-screen font-sans antialiased text-base-content">
    <x-confirm-modal />
    
    <x-notification />
    
    <div class="navbar bg-base-100 shadow-sm border-b border-base-300">
        <div class="navbar-start">
             <div class="dropdown lg:hidden">
              <div tabindex="0" role="button" class="btn btn-ghost lg:hidden">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16" /></svg>
              </div>
              <ul tabindex="0" class="menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow bg-base-100 rounded-box w-52">
                @auth
                    @if(Auth::user()->isAdmin())
                        <li><a href="{{ route('admin.users.index') }}">Management Users</a></li>
                    @endif
                    <li><a href="{{ route('profile.password') }}">Ubah Password</a></li>
                @endauth
              </ul>
            </div>
            <a href="/dashboard" class="btn btn-ghost text-xl text-primary">Procurement Status</a>
        </div>
        <div class="navbar-center hidden lg:flex">
             <!-- Centered menu if needed -->
        </div>
        <div class="navbar-end gap-2">
            @auth
                @if(Auth::user()->isAdmin())
                    <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-ghost hidden md:inline-flex">Management Users</a>
                @endif
                <div class="dropdown dropdown-end">
                    <div tabindex="0" role="button" class="btn btn-ghost btn-sm">
                        <span class="hidden md:inline">{{ Auth::user()->name ?? Auth::user()->email }}</span>
                        <span class="badge badge-sm {{ Auth::user()->isAdmin() ? 'badge-error' : 'badge-info' }}">{{ Auth::user()->role }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </div>
                    <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-52">
                        <li class="menu-title"><span class="text-xs opacity-50">{{ Auth::user()->email }}</span></li>
                        <li><a href="{{ route('profile.password') }}">Ubah Password</a></li>
                        <li class="text-error">
                            <form method="POST" action="{{ route('logout') }}" class="w-full">
                                @csrf
                                <button type="submit" class="w-full text-left text-error">Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            @endauth
        </div>
    </div>

    <main class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
        @if(session('success'))
            <div role="alert" class="alert alert-success mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif
         @if(session('error'))
            <div role="alert" class="alert alert-error mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @yield('content')
    </main>
    @stack('scripts')
</body>
</html>
