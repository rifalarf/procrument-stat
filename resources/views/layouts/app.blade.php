<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="corporate">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Procurement Tracker</title>
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
                    <li><a href="{{ route('history.index') }}">History</a></li>
                    @if(Auth::user()->isAdmin())
                        <li><a href="{{ route('admin.users.index') }}">Kelola User</a></li>
                    @endif
                @endauth
              </ul>
            </div>
            <a href="/dashboard" class="btn btn-ghost text-xl text-primary">Procurement Tracker</a>
        </div>
        <div class="navbar-center hidden lg:flex">
             <!-- Centered menu if needed -->
        </div>
        <div class="navbar-end gap-2">
            @auth
                <span class="text-sm font-medium opacity-70 hidden md:inline-block">{{ Auth::user()->email }} ({{ Auth::user()->role }})</span>
                <a href="{{ route('history.index') }}" class="btn btn-sm btn-ghost hidden md:inline-flex">History</a>
                @if(Auth::user()->isAdmin())
                    <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-ghost hidden md:inline-flex">Kelola User</a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-ghost text-error">Logout</button>
                </form>
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
