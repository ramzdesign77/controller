<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Minimarket') | Minimarket</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-100 text-stone-900 antialiased">
    <header class="border-b border-stone-200 bg-white">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="flex items-center gap-3 font-semibold tracking-normal text-stone-900">
                <span class="grid size-10 place-items-center rounded-md bg-primary-700 text-lg text-white">M</span>
                <span>Minimarket <span class="font-normal text-stone-500">/ Admin</span></span>
            </a>
            <div class="flex items-center gap-4">
                @auth
                    @if (auth()->user()->role === 'admin')
                        <a href="{{ route('products.index') }}" class="text-sm font-medium text-stone-700 hover:text-primary-800">Produk</a>
                    @endif
                    <span class="hidden text-sm text-stone-500 sm:inline">{{ auth()->user()->name }} · {{ auth()->user()->role }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-md border border-stone-300 px-3 py-2 text-sm font-medium text-stone-700 hover:bg-stone-50">Keluar</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-medium text-primary-800 hover:text-primary-900">Masuk</a>
                @endauth
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @yield('content')
    </main>
</body>
</html>