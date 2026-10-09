<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CashierController extends Controller
{
    public function index(Request $request): View
    {
        $heldTransactions = Transaction::query()
            ->where('user_id', $request->user()->id)
            ->where('status', 'held')
            ->withCount('details')
            ->latest('updated_at')
            ->limit(10)
            ->get();

        return view('cashier.index', compact('heldTransactions'));
    }

    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'max:100'],
        ]);

        $term = trim($validated['q']);

        $products = Product::query()
            ->where('is_active', true)
            ->where(function (Builder $query) use ($term): void {
                $query->where('name', 'like', '%'.$term.'%')
                    ->orWhere('barcode', 'like', '%'.$term.'%');
            })
            ->orderBy('name')
            ->limit(12)
            ->get(['id', 'name', 'barcode', 'category', 'price', 'stock']);

        return response()->json([
            'products' => $products->map(fn (Product $product): array => [
                'id' => $product->id,
                'name' => $product->name,
                'barcode' => $product->barcode,
                'category' => $product->category,
                'price' => (float) $product->price,
                'stock' => $product->stock,
            ]),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['held', 'paid'])],
            'transaction_id' => [
                'nullable',
                'integer',
                Rule::exists('transactions', 'id')
                    ->where('user_id', $request->user()->id)
                    ->where('status', 'held'),
            ],
            'customer_id' => ['nullable', 'integer', 'min:1'],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.product_id' => ['required', 'integer', 'distinct'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:9999'],
            'discount_percent' => ['required', 'numeric', 'min:0', 'max:100', 'decimal:0,2'],
            'discount_amount' => ['required', 'numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
            'tax_percent' => ['required', 'numeric', 'min:0', 'max:100', 'decimal:0,2'],
            'other_fee' => ['required', 'numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
            'paid_amount' => ['required_if:action,paid', 'nullable', 'numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
            'payment_method' => ['required_if:action,paid', 'nullable', Rule::in(['cash', 'qris', 'debit', 'credit', 'e_wallet', 'transfer'])],
        ]);

        $transaction = DB::transaction(function () use ($request, $validated): Transaction {
            $transaction = isset($validated['transaction_id'])
                ? Transaction::query()
                    ->where('user_id', $request->user()->id)
                    ->where('status', 'held')
                    ->lockForUpdate()
                    ->findOrFail($validated['transaction_id'])
                : new Transaction;

            $items = collect($validated['items'])->keyBy('product_id');
            $products = Product::query()
                ->whereIn('id', $items->keys())
                ->where('is_active', true)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($products->count() !== $items->count()) {
                throw ValidationException::withMessages([
                    'items' => 'Satu atau lebih produk sudah tidak tersedia.',
                ]);
            }

            $subtotalCents = 0;
            $lines = [];

            foreach ($items as $item) {
                $product = $products->get((int) $item['product_id']);
                $quantity = (int) $item['qty'];

                if ($product->stock < $quantity) {
                    throw ValidationException::withMessages([
                        'items' => "Stok {$product->name} tidak mencukupi. Sisa stok: {$product->stock}.",
                    ]);
                }

                $priceCents = $this->toCents($product->price);
                $lineSubtotalCents = $priceCents * $quantity;
                $subtotalCents += $lineSubtotalCents;
                $lines[] = [
                    'product' => $product,
                    'qty' => $quantity,
                    'price_cents' => $priceCents,
                    'subtotal_cents' => $lineSubtotalCents,
                ];
            }

            $percentageDiscountCents = (int) round($subtotalCents * (float) $validated['discount_percent'] / 100);
            $fixedDiscountCents = $this->toCents($validated['discount_amount']);
            $discountCents = min($subtotalCents, $percentageDiscountCents + $fixedDiscountCents);
            $taxableCents = $subtotalCents - $discountCents;
            $taxCents = (int) round($taxableCents * (float) $validated['tax_percent'] / 100);
            $otherFeeCents = $this->toCents($validated['other_fee']);
            $grandTotalCents = $taxableCents + $taxCents + $otherFeeCents;
            $paidCents = $validated['action'] === 'paid' ? $this->toCents($validated['paid_amount']) : 0;

            if ($validated['action'] === 'paid' && $paidCents < $grandTotalCents) {
                throw ValidationException::withMessages([
                    'paid_amount' => 'Jumlah uang dibayar masih kurang dari total transaksi.',
                ]);
            }

            $transaction->fill([
                'transaction_number' => $transaction->transaction_number ?: 'TRX-'.now()->format('Ymd').'-'.Str::upper(Str::random(8)),
                'user_id' => $request->user()->id,
                'customer_id' => $validated['customer_id'] ?? null,
                'subtotal' => $this->fromCents($subtotalCents),
                'discount' => $this->fromCents($discountCents),
                'discount_percent' => $validated['discount_percent'],
                'discount_amount' => $this->fromCents($fixedDiscountCents),
                'tax' => $this->fromCents($taxCents),
                'tax_percent' => $validated['tax_percent'],
                'other_fee' => $this->fromCents($otherFeeCents),
                'grand_total' => $this->fromCents($grandTotalCents),
                'paid_amount' => $this->fromCents($paidCents),
                'change_amount' => $this->fromCents(max(0, $paidCents - $grandTotalCents)),
                'payment_method' => $validated['action'] === 'paid' ? $validated['payment_method'] : null,
                'status' => $validated['action'],
            ])->save();

            $transaction->details()->delete();

            foreach ($lines as $line) {
                $transaction->details()->create([
                    'product_id' => $line['product']->id,
                    'product_code' => $line['product']->barcode,
                    'product_name' => $line['product']->name,
                    'price' => $this->fromCents($line['price_cents']),
                    'qty' => $line['qty'],
                    'discount' => '0.00',
                    'subtotal' => $this->fromCents($line['subtotal_cents']),
                ]);

                if ($validated['action'] === 'paid') {
                    $line['product']->decrement('stock', $line['qty']);
                }
            }

            return $transaction;
        });

        if ($transaction->status === 'held') {
            return response()->json([
                'message' => 'Transaksi berhasil ditahan.',
                'transaction_id' => $transaction->id,
            ]);
        }

        return response()->json([
            'message' => 'Pembayaran berhasil.',
            'receipt_url' => route('cashier.receipt', $transaction),
        ]);
    }

    public function resume(Request $request, Transaction $transaction): JsonResponse
    {
        $transaction = Transaction::query()
            ->where('user_id', $request->user()->id)
            ->where('status', 'held')
            ->with('details.product:id,stock')
            ->findOrFail($transaction->id);

        return response()->json([
            'transaction' => [
                'id' => $transaction->id,
                'customer_id' => $transaction->customer_id,
                'discount_percent' => (float) $transaction->discount_percent,
                'discount_amount' => (float) $transaction->discount_amount,
                'tax_percent' => (float) $transaction->tax_percent,
                'details' => $transaction->details->map(fn ($detail): array => [
                    'product_id' => $detail->product_id,
                    'name' => $detail->product_name,
                    'barcode' => $detail->product_code,
                    'price' => (float) $detail->price,
                    'qty' => $detail->qty,
                    'stock' => $detail->product->stock,
                ]),
            ],
        ]);
    }

    public function destroyHeld(Request $request, Transaction $transaction): JsonResponse
    {
        $transaction = Transaction::query()
            ->where('user_id', $request->user()->id)
            ->where('status', 'held')
            ->findOrFail($transaction->id);
        $transaction->delete();

        return response()->json(['message' => 'Transaksi ditahan dibatalkan.']);
    }

    public function receipt(Request $request, Transaction $transaction): View
    {
        $transaction = Transaction::query()
            ->where('user_id', $request->user()->id)
            ->where('status', 'paid')
            ->with('details')
            ->findOrFail($transaction->id);

        return view('cashier.receipt', compact('transaction'));
    }

    private function toCents(string|int|float $amount): int
    {
        return (int) round((float) $amount * 100);
    }

    private function fromCents(int $amount): string
    {
        return number_format($amount / 100, 2, '.', '');
    }
}
