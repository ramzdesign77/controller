@extends('layouts.admin')

@section('title', 'Struk '.$transaction->transaction_number)

@section('content')
    <style>
        @media print {
            body { background: #fff !important; }
            body > header, .receipt-actions { display: none !important; }
            body > main { max-width: 80mm !important; padding: 0 !important; }
            .receipt-paper { border: 0 !important; box-shadow: none !important; }
        }
    </style>

    <div class="mx-auto max-w-sm">
        <div class="receipt-paper rounded-md border border-stone-200 bg-white p-6 font-mono text-sm text-stone-900">
            <div class="text-center">
                <h1 class="text-lg font-bold">Minimarket</h1>
                <p class="mt-1 text-xs text-stone-600">Struk pembayaran</p>
            </div>

            <div class="my-4 border-y border-dashed border-stone-400 py-3 text-xs leading-5">
                <div class="flex justify-between gap-3"><span>No. transaksi</span><span class="text-right">{{ $transaction->transaction_number }}</span></div>
                <div class="flex justify-between gap-3"><span>Tanggal</span><span>{{ $transaction->created_at->format('d/m/Y H:i') }}</span></div>
                <div class="flex justify-between gap-3"><span>Kasir</span><span>{{ $transaction->user->name }}</span></div>
            </div>

            <div class="space-y-3">
                @foreach ($transaction->details as $detail)
                    <div>
                        <div class="font-semibold">{{ $detail->product_name }}</div>
                        @if ($detail->product_code)
                            <div class="text-xs text-stone-500">{{ $detail->product_code }}</div>
                        @endif
                        <div class="mt-1 flex justify-between gap-3 text-xs">
                            <span>{{ $detail->qty }} × Rp {{ number_format((float) $detail->price, 0, ',', '.') }}</span>
                            <span>Rp {{ number_format((float) $detail->subtotal, 0, ',', '.') }}</span>
                        </div>
                    </div>
                @endforeach
            </div>

            <dl class="mt-4 space-y-2 border-t border-dashed border-stone-400 pt-3 text-xs">
                <div class="flex justify-between gap-3"><dt>Subtotal</dt><dd>Rp {{ number_format((float) $transaction->subtotal, 0, ',', '.') }}</dd></div>
                <div class="flex justify-between gap-3"><dt>Diskon</dt><dd>− Rp {{ number_format((float) $transaction->discount, 0, ',', '.') }}</dd></div>
                <div class="flex justify-between gap-3"><dt>Pajak / PPN</dt><dd>Rp {{ number_format((float) $transaction->tax, 0, ',', '.') }}</dd></div>
                <div class="flex justify-between gap-3"><dt>Biaya lain</dt><dd>Rp {{ number_format((float) $transaction->other_fee, 0, ',', '.') }}</dd></div>
                <div class="flex justify-between gap-3 border-t border-stone-300 pt-2 text-sm font-bold"><dt>Total</dt><dd>Rp {{ number_format((float) $transaction->grand_total, 0, ',', '.') }}</dd></div>
                <div class="flex justify-between gap-3"><dt>Dibayar · {{ strtoupper(str_replace('_', ' ', $transaction->payment_method)) }}</dt><dd>Rp {{ number_format((float) $transaction->paid_amount, 0, ',', '.') }}</dd></div>
                <div class="flex justify-between gap-3"><dt>Kembalian</dt><dd>Rp {{ number_format((float) $transaction->change_amount, 0, ',', '.') }}</dd></div>
            </dl>

            <p class="mt-5 border-t border-dashed border-stone-400 pt-3 text-center text-xs">Terima kasih telah berbelanja.</p>
        </div>

        <div class="receipt-actions mt-4 flex gap-3">
            <a href="{{ route('cashier.index') }}" class="flex-1 rounded-md border border-stone-300 bg-white px-4 py-2.5 text-center text-sm font-semibold text-stone-700 hover:bg-stone-50">Kembali ke kasir</a>
            <button type="button" onclick="window.print()" class="flex-1 rounded-md bg-primary-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-800">Cetak ulang</button>
        </div>
    </div>

    <script>
        window.addEventListener('load', () => window.print());
    </script>
@endsection