@extends('layouts.admin')

@section('title', 'Masuk')

@section('content')
    <div class="mx-auto max-w-md rounded-md border border-stone-200 bg-white p-6 sm:p-8">
        <p class="text-sm font-semibold uppercase text-primary-800">Akses akun</p>
        <h1 class="mt-2 text-2xl font-semibold text-stone-950">Masuk ke Minimarket</h1>
        <p class="mt-1 text-sm text-stone-600">Gunakan akun admin atau kasir yang terdaftar.</p>

        <form action="{{ route('login.store') }}" method="POST" class="mt-6 space-y-5">
            @csrf
            <div>
                <label for="email" class="mb-2 block text-sm font-medium text-stone-800">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus class="w-full rounded-md border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-primary-700 focus:ring-2 focus:ring-primary-700/20">
                @error('email') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="password" class="mb-2 block text-sm font-medium text-stone-800">Password</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required class="w-full rounded-md border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-primary-700 focus:ring-2 focus:ring-primary-700/20">
                @error('password') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
            </div>
            <label class="inline-flex items-center gap-2 text-sm text-stone-700">
                <input type="checkbox" name="remember" value="1" class="size-4 rounded border-stone-300 text-primary-700 focus:ring-primary-700">
                Ingat saya
            </label>
            <button type="submit" class="w-full rounded-md bg-primary-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-800">Masuk</button>
        </form>
    </div>
@endsection