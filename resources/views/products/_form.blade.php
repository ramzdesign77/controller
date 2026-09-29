<form
    action="{{ $product->exists ? route('products.update', $product) : route('products.store') }}"
    method="POST"
    enctype="multipart/form-data"
    class="space-y-6"
>
    @csrf
    @if ($product->exists)
        @method('PUT')
    @endif

    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <label for="name" class="mb-2 block text-sm font-medium text-stone-800">Nama produk</label>
            <input id="name" name="name" value="{{ old('name', $product->name) }}" required maxlength="255" class="w-full rounded-md border border-stone-300 bg-white px-3 py-2.5 text-sm outline-none focus:border-primary-700 focus:ring-2 focus:ring-primary-700/20">
            @error('name') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="category" class="mb-2 block text-sm font-medium text-stone-800">Kategori</label>
            <input id="category" name="category" value="{{ old('category', $product->category) }}" required maxlength="100" class="w-full rounded-md border border-stone-300 bg-white px-3 py-2.5 text-sm outline-none focus:border-primary-700 focus:ring-2 focus:ring-primary-700/20">
            @error('category') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="price" class="mb-2 block text-sm font-medium text-stone-800">Harga (Rp)</label>
            <input id="price" name="price" type="number" min="0" step="0.01" value="{{ old('price', $product->price) }}" required class="w-full rounded-md border border-stone-300 bg-white px-3 py-2.5 text-sm outline-none focus:border-primary-700 focus:ring-2 focus:ring-primary-700/20">
            @error('price') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="stock" class="mb-2 block text-sm font-medium text-stone-800">Stok</label>
            <input id="stock" name="stock" type="number" min="0" step="1" value="{{ old('stock', $product->stock) }}" required class="w-full rounded-md border border-stone-300 bg-white px-3 py-2.5 text-sm outline-none focus:border-primary-700 focus:ring-2 focus:ring-primary-700/20">
            @error('stock') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
        </div>

        <div class="sm:col-span-2">
            <label for="image" class="mb-2 block text-sm font-medium text-stone-800">Gambar produk</label>
            <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" class="block w-full rounded-md border border-stone-300 bg-white text-sm file:mr-4 file:border-0 file:bg-stone-100 file:px-4 file:py-2.5 file:font-medium file:text-stone-700 hover:file:bg-stone-200">
            @if ($product->image)
                <img src="{{ asset('storage/'.$product->image) }}" alt="Gambar {{ $product->name }}" class="mt-3 size-24 rounded-md border border-stone-200 object-cover">
            @endif
            @error('image') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
        </div>

        <div class="sm:col-span-2">
            <label for="description" class="mb-2 block text-sm font-medium text-stone-800">Deskripsi</label>
            <textarea id="description" name="description" rows="4" class="w-full rounded-md border border-stone-300 bg-white px-3 py-2.5 text-sm outline-none focus:border-primary-700 focus:ring-2 focus:ring-primary-700/20">{{ old('description', $product->description) }}</textarea>
            @error('description') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
        </div>

        <div class="sm:col-span-2">
            <input type="hidden" name="is_active" value="0">
            <label for="is_active" class="inline-flex items-center gap-2 text-sm font-medium text-stone-800">
                <input id="is_active" name="is_active" type="checkbox" value="1" @checked(old('is_active', $product->is_active ?? true)) class="size-4 rounded border-stone-300 text-primary-700 focus:ring-primary-700">
                Produk aktif
            </label>
            @error('is_active') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="flex flex-wrap gap-3 border-t border-stone-200 pt-5">
        <button type="submit" class="rounded-md bg-primary-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-800">{{ $product->exists ? 'Simpan perubahan' : 'Simpan produk' }}</button>
        <a href="{{ $product->exists ? route('products.show', $product) : route('products.index') }}" class="rounded-md border border-stone-300 px-4 py-2.5 text-sm font-semibold text-stone-700 hover:bg-stone-50">Batal</a>
    </div>
</form>