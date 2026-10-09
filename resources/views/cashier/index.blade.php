@extends('layouts.admin')

@section('title', 'Pembayaran Kasir')

@section('content')
    <div
        id="cashier-app"
        data-search-url="{{ route('cashier.products.search') }}"
        data-store-url="{{ route('cashier.transactions.store') }}"
        data-resume-url="{{ route('cashier.resume', ['transaction' => '__TRANSACTION__']) }}"
        data-destroy-url="{{ route('cashier.transactions.destroy', ['transaction' => '__TRANSACTION__']) }}"
        data-csrf="{{ csrf_token() }}"
        class="space-y-6"
    >
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase text-primary-800">Point of Sale</p>
                <h1 class="mt-1 text-2xl font-semibold text-stone-950">Pembayaran Kasir</h1>
                <p class="mt-1 text-sm text-stone-600">{{ now()->translatedFormat('l, d F Y') }} · {{ auth()->user()->name }}</p>
            </div>
            <a href="{{ route('home') }}" class="rounded-md border border-stone-300 bg-white px-3 py-2 text-sm font-medium text-stone-700 hover:bg-stone-50">Kembali</a>
        </div>

        <div id="cashier-message" role="status" aria-live="polite" class="hidden rounded-md border px-4 py-3 text-sm"></div>

        <div class="grid min-w-0 grid-cols-[minmax(0,1fr)] items-start gap-5 xl:grid-cols-[minmax(0,1.55fr)_minmax(340px,0.85fr)]">
            <section class="min-w-0 space-y-5">
                <div class="rounded-md border border-stone-200 bg-white p-5">
                    <label for="product-search" class="mb-2 block text-sm font-semibold text-stone-800">Cari barang atau scan barcode</label>
                    <div class="relative">
                        <input id="product-search" type="search" autocomplete="off" autofocus placeholder="Indomie, Aqua, Teh Botol, barcode..." class="w-full rounded-md border border-stone-300 bg-white px-4 py-3 pr-16 text-sm outline-none focus:border-primary-700 focus:ring-2 focus:ring-primary-700/20">
                        <kbd class="absolute right-3 top-1/2 -translate-y-1/2 rounded border border-stone-200 bg-stone-50 px-2 py-1 text-xs text-stone-500">F2</kbd>
                        <div id="product-results" role="listbox" class="absolute z-20 mt-1 hidden max-h-80 w-full overflow-auto rounded-md border border-stone-200 bg-white shadow-lg"></div>
                    </div>
                    <p id="search-hint" class="mt-2 text-xs text-stone-500">Ketik nama produk atau pindai barcode untuk menambahkan barang.</p>
                </div>

                <div class="rounded-md border border-stone-200 bg-white p-5">
                    <div class="grid gap-4 sm:grid-cols-[minmax(0,1fr)_220px]">
                        <div>
                            <label for="customer-id" class="mb-2 block text-sm font-semibold text-stone-800">ID pelanggan <span class="font-normal text-stone-500">(opsional)</span></label>
                            <input id="customer-id" type="number" min="1" placeholder="Masukkan ID pelanggan" class="w-full rounded-md border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-primary-700 focus:ring-2 focus:ring-primary-700/20">
                        </div>
                        <div class="flex items-end">
                            <p class="text-xs leading-5 text-stone-500">Data pelanggan belum dikelola di aplikasi ini. ID dicatat sebagai referensi transaksi.</p>
                        </div>
                    </div>
                </div>

                @if ($heldTransactions->isNotEmpty())
                    <div class="rounded-md border border-amber-200 bg-amber-50 p-4">
                        <div class="mb-3 flex items-center justify-between gap-3">
                            <h2 class="text-sm font-semibold text-amber-950">Transaksi ditahan</h2>
                            <span class="text-xs text-amber-800">{{ $heldTransactions->count() }} transaksi</span>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($heldTransactions as $heldTransaction)
                                <button type="button" data-resume-id="{{ $heldTransaction->id }}" class="rounded-md border border-amber-300 bg-white px-3 py-2 text-left text-xs font-medium text-amber-950 hover:bg-amber-100">
                                    {{ $heldTransaction->transaction_number }} · {{ $heldTransaction->details_count }} barang
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="overflow-hidden rounded-md border border-stone-200 bg-white">
                    <div class="flex items-center justify-between border-b border-stone-200 px-5 py-4">
                        <div>
                            <h2 class="font-semibold text-stone-900">Keranjang belanja</h2>
                            <p id="cart-count" class="mt-1 text-xs text-stone-500">0 barang</p>
                        </div>
                        <button id="clear-cart" type="button" class="rounded-md px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-50">Kosongkan</button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[580px] text-left text-sm">
                            <thead class="bg-stone-50 text-xs uppercase text-stone-600">
                                <tr>
                                    <th scope="col" class="px-4 py-3 font-semibold">Produk</th>
                                    <th scope="col" class="px-4 py-3 font-semibold">Harga</th>
                                    <th scope="col" class="px-4 py-3 font-semibold">Jumlah</th>
                                    <th scope="col" class="px-4 py-3 text-right font-semibold">Subtotal</th>
                                    <th scope="col" class="px-4 py-3 text-right font-semibold">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="cart-lines" class="divide-y divide-stone-100">
                                <tr id="empty-cart-row">
                                    <td colspan="5" class="px-4 py-12 text-center text-sm text-stone-500">Keranjang masih kosong. Cari atau scan produk untuk memulai.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <aside class="min-w-0 rounded-md border border-stone-200 bg-white p-5 xl:sticky xl:top-5">
                <div class="flex items-center justify-between border-b border-stone-200 pb-4">
                    <h2 class="text-lg font-semibold text-stone-950">Ringkasan pembayaran</h2>
                    <span class="rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-800">Siap</span>
                </div>

                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between gap-4"><dt class="text-stone-600">Subtotal barang</dt><dd id="summary-subtotal" class="font-medium tabular-nums text-stone-900">Rp 0</dd></div>
                    <div class="grid grid-cols-[1fr_96px] items-center gap-3">
                        <label for="discount-percent" class="text-stone-600">Diskon (%)</label>
                        <div class="relative"><input id="discount-percent" type="number" min="0" max="100" step="0.01" value="0" class="w-full rounded-md border border-stone-300 py-2 pl-3 pr-7 text-right text-sm tabular-nums outline-none focus:border-primary-700 focus:ring-2 focus:ring-primary-700/20"><span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-stone-500">%</span></div>
                    </div>
                    <div class="grid grid-cols-[1fr_140px] items-center gap-3">
                        <label for="discount-amount" class="text-stone-600">Diskon nominal</label>
                        <input id="discount-amount" type="number" min="0" step="1" value="0" class="w-full rounded-md border border-stone-300 px-3 py-2 text-right text-sm tabular-nums outline-none focus:border-primary-700 focus:ring-2 focus:ring-primary-700/20">
                    </div>
                    <div class="flex justify-between gap-4 text-red-700"><dt>Total diskon</dt><dd id="summary-discount" class="font-medium tabular-nums">− Rp 0</dd></div>
                    <div class="grid grid-cols-[1fr_96px] items-center gap-3">
                        <label for="tax-percent" class="text-stone-600">Pajak / PPN</label>
                        <div class="relative"><input id="tax-percent" type="number" min="0" max="100" step="0.01" value="11" class="w-full rounded-md border border-stone-300 py-2 pl-3 pr-7 text-right text-sm tabular-nums outline-none focus:border-primary-700 focus:ring-2 focus:ring-primary-700/20"><span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-stone-500">%</span></div>
                    </div>
                    <div class="flex justify-between gap-4"><dt class="text-stone-600">Pajak</dt><dd id="summary-tax" class="font-medium tabular-nums text-stone-900">Rp 0</dd></div>
                    <div class="grid grid-cols-[1fr_140px] items-center gap-3">
                        <label for="other-fee" class="text-stone-600">Biaya lain</label>
                        <input id="other-fee" type="number" min="0" step="1" value="0" class="w-full rounded-md border border-stone-300 px-3 py-2 text-right text-sm tabular-nums outline-none focus:border-primary-700 focus:ring-2 focus:ring-primary-700/20">
                    </div>
                    <div class="flex justify-between gap-4 border-t border-stone-200 pt-4 text-base"><dt class="font-semibold text-stone-900">Total bayar</dt><dd id="summary-total" class="font-bold tabular-nums text-stone-950">Rp 0</dd></div>
                </dl>

                <div class="mt-5 space-y-4 border-t border-stone-200 pt-5">
                    <div>
                        <label for="payment-method" class="mb-2 block text-sm font-medium text-stone-800">Metode pembayaran</label>
                        <select id="payment-method" class="w-full rounded-md border border-stone-300 bg-white px-3 py-2.5 text-sm outline-none focus:border-primary-700 focus:ring-2 focus:ring-primary-700/20">
                            <option value="cash">Tunai</option>
                            <option value="qris">QRIS</option>
                            <option value="debit">Debit</option>
                            <option value="credit">Kredit</option>
                            <option value="e_wallet">E-Wallet</option>
                            <option value="transfer">Transfer</option>
                        </select>
                    </div>
                    <div>
                        <label for="paid-amount" class="mb-2 flex items-center justify-between text-sm font-medium text-stone-800"><span>Uang dibayar</span><kbd class="rounded border border-stone-200 bg-stone-50 px-2 py-1 text-xs text-stone-500">F4</kbd></label>
                        <input id="paid-amount" type="number" min="0" step="1" placeholder="Masukkan nominal" class="w-full rounded-md border border-stone-300 px-3 py-3 text-right text-lg font-semibold tabular-nums outline-none focus:border-primary-700 focus:ring-2 focus:ring-primary-700/20">
                    </div>
                    <div class="flex items-center justify-between rounded-md bg-stone-50 px-3 py-3 text-sm">
                        <span class="text-stone-600">Kembalian</span>
                        <span id="summary-change" class="font-semibold tabular-nums text-stone-900">Rp 0</span>
                    </div>
                    <p id="payment-status" aria-live="polite" class="text-sm text-stone-500">Tambahkan barang untuk memulai transaksi.</p>
                </div>

                <div class="mt-5 grid grid-cols-2 gap-3">
                    <button id="hold-transaction" type="button" class="rounded-md border border-amber-300 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-950 hover:bg-amber-100">Tahan</button>
                    <button id="cancel-transaction" type="button" class="rounded-md border border-stone-300 bg-white px-4 py-3 text-sm font-semibold text-stone-700 hover:bg-stone-50">Batal</button>
                    <button id="pay-transaction" type="button" class="col-span-2 rounded-md bg-primary-700 px-4 py-3.5 text-sm font-semibold text-white hover:bg-primary-800 disabled:cursor-not-allowed disabled:opacity-50">Bayar &amp; Cetak</button>
                </div>
            </aside>
        </div>
    </div>

    <script defer src="{{ asset('js/cashier.js') }}"></script>
@endsection