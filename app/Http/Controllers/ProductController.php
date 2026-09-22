<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        return 'menampilkan semua produk';
    }

    public function create()
    {
        return 'Form tambah produk';
    }

    public function store(Request $request)
    {
        return 'Menyimpan produk baru';
    }

    public function show(string $id)
    {
        return 'Menampilan produk dengan ID: '.$id;
    }

    public function edit(string $id)
    {
        return 'Form edit produk dengan ID: '.$id;
    }

    public function update(Request $request, string $id)
    {
        return 'Mengupdate produk dengan ID: '.$id;
    }

    public function destroy(string $id)
    {
        return 'Menghapus produk dengan ID: '.$id;
    }
}
