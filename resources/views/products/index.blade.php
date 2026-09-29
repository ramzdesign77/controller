@extends('layouts.admin')

@section('title', 'Daftar Produk')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-semibold uppercase text-primary-800">Inventaris</p>
            <h1 class="mt-1 text-2xl font-semibold text-stone-950">Daftar Produk</h1>
            <p class="mt-1 text-sm text-stone-600">Kelola informasi, harga, dan stok produk minimarket.</p>
        </div>
        <a href="{{ route('products.create') }}" class="rounded-md bg-primary-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-800">+ Tambah produk</a>
    </div>

    @if (session('success'))
        <div role="status" class="mt-6 rounded-md border border-primary-200 bg-primary-50 px-4 py-3 text-sm text-primary-900">{{ session('success') }}</div>
    @endif

    <div class="mt-6 overflow-hidden rounded-md border border-stone-200 bg-white">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] divide-y divide-stone-200 text-left text-sm">
                <thead class="bg-stone-50 text-xs uppercase text-stone-600">
                    <tr>
                        <th scope="col" class="px-4 py-3 font-semibold">Produk</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Kategori</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Harga</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Stok</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Status</th>
                        <th scope="col" class="px-4 py-3 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($products as $product)
                        <tr class="align-middle hover:bg-stone-50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    @if ($product->image)
                                        <img src="{{ asset('storage/'.$product->image) }}" alt="" class="size-11 rounded-md object-cover">
                                    @else
                                        <span class="grid size-11 place-items-center rounded-md bg-stone-100 text-xs font-semibold text-stone-500">IMG</span>
                                    @endif
                                    <span class="font-medium text-stone-900">{{ $product->name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-stone-600">{{ $product->category }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">Rp {{ number_format((float) $product->price, 2, ',', '.') }}</td>
                            <td class="px-4 py-3">{{ $product->stock }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $product->is_active ? 'bg-primary-100 text-primary-800' : 'bg-stone-100 text-stone-600' }}">{{ $product->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-3 whitespace-nowrap">
                                    <a href="{{ route('products.show', $product) }}" class="font-medium text-primary-800 hover:underline">Detail</a>
                                    <a href="{{ route('products.edit', $product) }}" class="font-medium text-stone-700 hover:underline">Edit</a>
                                    <form action="{{ route('products.destroy', $product) }}" method="POST" onsubmit="return confirm('Hapus produk ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="font-medium text-red-700 hover:underline">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center">
                                <p class="font-medium text-stone-800">Belum ada produk</p>
                                <p class="mt-1 text-sm text-stone-500">Tambahkan produk pertama untuk mengisi inventaris.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($products->hasPages())
            <div class="border-t border-stone-200 px-4 py-3">{{ $products->links() }}</div>
        @endif
    </div>
@endsection