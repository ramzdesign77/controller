@extends('layouts.admin')

@section('title', 'Edit Produk')

@section('content')
    <div class="max-w-3xl">
        <a href="{{ route('products.show', $product) }}" class="text-sm font-medium text-primary-800 hover:underline">← Kembali ke detail</a>
        <h1 class="mt-3 text-2xl font-semibold text-stone-950">Edit Produk</h1>
        <p class="mt-1 text-sm text-stone-600">Perbarui informasi {{ $product->name }}.</p>
        <div class="mt-6 rounded-md border border-stone-200 bg-white p-5 sm:p-7">
            @include('products._form')
        </div>
    </div>
@endsection