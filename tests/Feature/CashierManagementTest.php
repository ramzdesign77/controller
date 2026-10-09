<?php

use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function cashierProduct(array $attributes = []): Product
{
    return Product::query()->create(array_merge([
        'name' => 'Indomie Goreng',
        'barcode' => 'POS-001',
        'category' => 'Makanan Instan',
        'price' => '10000.00',
        'stock' => 10,
        'is_active' => true,
    ], $attributes));
}

it('requires the cashier role to access the point of sale', function () {
    $this->get(route('cashier.index'))->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->get(route('cashier.index'))
        ->assertForbidden();

    $this->actingAs(User::factory()->create(['role' => 'kasir']))
        ->get(route('cashier.index'))
        ->assertSee('Pembayaran Kasir');
});

it('searches active products by barcode', function () {
    $cashier = User::factory()->create(['role' => 'kasir']);
    cashierProduct();

    $this->actingAs($cashier)
        ->getJson(route('cashier.products.search', ['q' => 'POS-001']))
        ->assertJsonPath('products.0.name', 'Indomie Goreng')
        ->assertJsonPath('products.0.barcode', 'POS-001');
});

it('recalculates totals and reduces stock when a transaction is paid', function () {
    $cashier = User::factory()->create(['role' => 'kasir']);
    $product = cashierProduct();

    $response = $this->actingAs($cashier)
        ->postJson(route('cashier.transactions.store'), [
            'action' => 'paid',
            'items' => [['product_id' => $product->id, 'qty' => 2]],
            'discount_percent' => 10,
            'discount_amount' => 500,
            'tax_percent' => 11,
            'other_fee' => 1000,
            'paid_amount' => 25000,
            'payment_method' => 'cash',
        ])
        ->assertOk()
        ->assertJsonStructure(['receipt_url']);

    $transaction = Transaction::query()->with('details')->firstOrFail();
    expect($transaction->subtotal)->toBe('20000.00')
        ->and($transaction->discount)->toBe('2500.00')
        ->and($transaction->tax)->toBe('1925.00')
        ->and($transaction->grand_total)->toBe('20425.00')
        ->and($transaction->change_amount)->toBe('4575.00')
        ->and($transaction->status)->toBe('paid')
        ->and($transaction->details)->toHaveCount(1);
    $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 8]);

    $this->actingAs($cashier)
        ->get($response->json('receipt_url'))
        ->assertSee($transaction->transaction_number)
        ->assertSee('Indomie Goreng')
        ->assertSee('Rp 20.425');
});

it('does not charge or deduct stock when the cart is held', function () {
    $cashier = User::factory()->create(['role' => 'kasir']);
    $product = cashierProduct();

    $response = $this->actingAs($cashier)
        ->postJson(route('cashier.transactions.store'), [
            'action' => 'held',
            'items' => [['product_id' => $product->id, 'qty' => 2]],
            'discount_percent' => 15,
            'discount_amount' => 0,
            'tax_percent' => 8,
            'other_fee' => 0,
        ])
        ->assertOk();

    $heldId = $response->json('transaction_id');
    $this->assertDatabaseHas('transactions', ['id' => $heldId, 'status' => 'held', 'paid_amount' => '0.00']);
    $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 10]);

    $resumeResponse = $this->actingAs($cashier)
        ->getJson(route('cashier.resume', $heldId))
        ->assertJsonPath('transaction.discount_percent', 15)
        ->assertJsonPath('transaction.discount_amount', 0)
        ->assertJsonPath('transaction.tax_percent', 8);

    $this->actingAs($cashier)
        ->postJson(route('cashier.transactions.store'), [
            'action' => 'paid',
            'transaction_id' => $heldId,
            'items' => [['product_id' => $product->id, 'qty' => 2]],
            'discount_percent' => $resumeResponse->json('transaction.discount_percent'),
            'discount_amount' => $resumeResponse->json('transaction.discount_amount'),
            'tax_percent' => $resumeResponse->json('transaction.tax_percent'),
            'other_fee' => 0,
            'paid_amount' => 20000,
            'payment_method' => 'qris',
        ])
        ->assertOk();

    $this->assertDatabaseHas('transactions', [
        'id' => $heldId,
        'status' => 'paid',
        'payment_method' => 'qris',
        'discount' => '3000.00',
        'tax' => '1360.00',
        'grand_total' => '18360.00',
    ]);
    $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 8]);
});

it('rejects underpayment and insufficient stock without persisting a transaction', function () {
    $cashier = User::factory()->create(['role' => 'kasir']);
    $product = cashierProduct(['stock' => 1]);

    $this->actingAs($cashier)
        ->postJson(route('cashier.transactions.store'), [
            'action' => 'paid',
            'items' => [['product_id' => $product->id, 'qty' => 2]],
            'discount_percent' => 0,
            'discount_amount' => 0,
            'tax_percent' => 0,
            'other_fee' => 0,
            'paid_amount' => 20000,
            'payment_method' => 'cash',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('items');

    $this->assertDatabaseCount('transactions', 0);
    $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 1]);

    $this->actingAs($cashier)
        ->postJson(route('cashier.transactions.store'), [
            'action' => 'paid',
            'items' => [['product_id' => $product->id, 'qty' => 1]],
            'discount_percent' => 0,
            'discount_amount' => 0,
            'tax_percent' => 0,
            'other_fee' => 0,
            'paid_amount' => 9999,
            'payment_method' => 'cash',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('paid_amount');

    $this->assertDatabaseCount('transactions', 0);
    $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 1]);
});

it('prevents another cashier from resuming or cancelling a held transaction', function () {
    $firstCashier = User::factory()->create(['role' => 'kasir']);
    $otherCashier = User::factory()->create(['role' => 'kasir']);
    $product = cashierProduct();
    $transaction = Transaction::query()->create([
        'transaction_number' => 'TRX-TEST-HELD',
        'user_id' => $firstCashier->id,
        'subtotal' => 10000,
        'discount' => 0,
        'tax' => 0,
        'other_fee' => 0,
        'grand_total' => 10000,
        'paid_amount' => 0,
        'change_amount' => 0,
        'status' => 'held',
    ]);
    $transaction->details()->create([
        'product_id' => $product->id,
        'product_code' => $product->barcode,
        'product_name' => $product->name,
        'price' => $product->price,
        'qty' => 1,
        'discount' => 0,
        'subtotal' => $product->price,
    ]);

    $this->actingAs($otherCashier)
        ->getJson(route('cashier.resume', $transaction))
        ->assertNotFound();
    $this->actingAs($otherCashier)
        ->deleteJson(route('cashier.transactions.destroy', $transaction))
        ->assertNotFound();

    $this->assertModelExists($transaction);
});
