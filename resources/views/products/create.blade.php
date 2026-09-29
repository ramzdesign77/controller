@extends('layouts.admin')

@section('title', 'Tambah Produk')

@section('content')
    <div class="max-w-3xl">
        <a href="{{ route('products.index') }}" class="text-sm font-medium text-primary-800 hover:underline">← Kembali ke produk</a>
        <h1 class="mt-3 text-2xl font-semibold text-stone-950">Tambah Produk</h1>
        <p class="mt-1 text-sm text-stone-600">Masukkan informasi produk yang akan dikelola.</p>
        <div class="mt-6 rounded-md border border-stone-200 bg-white p-5 sm:p-7">
            @include('products._form')
        </div>
    </div>
@endsection