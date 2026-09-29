@extends('layouts.admin')

@section('title', $product->name)

@section('content')
    <div class="max-w-4xl">
        <a href="{{ route('products.index') }}" class="text-sm font-medium text-primary-800 hover:underline">← Kembali ke daftar</a>

        @if (session('success'))
            <div role="status" class="mt-5 rounded-md border border-primary-200 bg-primary-50 px-4 py-3 text-sm text-primary-900">{{ session('success') }}</div>
        @endif

        <div class="mt-5 grid gap-7 rounded-md border border-stone-200 bg-white p-5 sm:grid-cols-[220px_1fr] sm:p-7">
            <div>
                @if ($product->image)
                    <img src="{{ asset('storage/'.$product->image) }}" alt="Gambar {{ $product->name }}" class="aspect-square w-full rounded-md object-cover">
                @else
                    <div class="grid aspect-square w-full place-items-center rounded-md bg-stone-100 text-sm text-stone-500">Tidak ada gambar</div>
                @endif
            </div>
            <div>
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-primary-800">{{ $product->category }}</p>
                        <h1 class="mt-1 text-2xl font-semibold text-stone-950">{{ $product->name }}</h1>
                    </div>
                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $product->is_active ? 'bg-primary-100 text-primary-800' : 'bg-stone-100 text-stone-600' }}">{{ $product->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                </div>

                <dl class="mt-6 grid gap-4 border-y border-stone-200 py-5 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm text-stone-500">Harga</dt>
                        <dd class="mt-1 font-semibold text-stone-900">Rp {{ number_format((float) $product->price, 2, ',', '.') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-stone-500">Stok</dt>
                        <dd class="mt-1 font-semibold text-stone-900">{{ $product->stock }} unit</dd>
                    </div>
                </dl>

                <div class="py-5">
                    <h2 class="text-sm font-semibold text-stone-800">Deskripsi</h2>
                    <p class="mt-2 whitespace-pre-line text-sm leading-6 text-stone-600">{{ $product->description ?: 'Tidak ada deskripsi.' }}</p>
                </div>

                <a href="{{ route('products.edit', $product) }}" class="inline-flex rounded-md bg-primary-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-800">Edit produk</a>
            </div>
        </div>
    </div>
@endsection