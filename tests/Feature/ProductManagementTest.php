<?php

use App\Models\Product;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('redirects guests to login and forbids cashiers from managing products', function () {
    $this->get(route('products.index'))
        ->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create(['role' => 'kasir']))
        ->get(route('products.index'))
        ->assertForbidden();
});

it('sends administrators to products and cashiers to welcome from home', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->get(route('home'))
        ->assertRedirect(route('products.index'));

    $this->actingAs(User::factory()->create(['role' => 'kasir']))
        ->get(route('home'))
        ->assertOk()
        ->assertViewIs('welcome');
});

it('allows an admin to create view update and delete a product', function () {
    Storage::fake('public');
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin);

    $this->get(route('products.index'))->assertSee('Daftar Produk');
    $this->get(route('products.create'))->assertOk();

    $this->post(route('products.store'), [
        'name' => 'Susu UHT',
        'category' => 'Minuman',
        'image' => UploadedFile::fake()->image('susu.jpg'),
        'price' => '18500.00',
        'stock' => 24,
        'description' => 'Susu kemasan.',
        'is_active' => '1',
    ])->assertRedirect();

    $product = Product::query()->firstOrFail();
    expect(Storage::disk('public')->exists($product->image))->toBeTrue();
    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'name' => 'Susu UHT',
        'category' => 'Minuman',
        'stock' => 24,
        'is_active' => true,
    ]);
    $this->get(route('products.show', $product))->assertSee('Susu UHT');
    $this->get(route('products.edit', $product))->assertOk();

    $this->put(route('products.update', $product), [
        'name' => 'Susu UHT Cokelat',
        'category' => 'Minuman',
        'price' => '19000.00',
        'stock' => 18,
        'description' => 'Rasa cokelat.',
        'is_active' => '0',
    ])->assertRedirect();

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'name' => 'Susu UHT Cokelat',
        'stock' => 18,
        'is_active' => false,
    ]);
    expect(Storage::disk('public')->exists($product->image))->toBeTrue();

    $this->delete(route('products.destroy', $product))->assertRedirect(route('products.index'));

    $this->assertModelMissing($product);
    expect(Storage::disk('public')->exists($product->image))->toBeFalse();
});

it('rejects invalid product data without saving it', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->post(route('products.store'), [
            'name' => '',
            'category' => '',
            'price' => '-1',
            'stock' => '-2',
            'is_active' => '1',
        ])
        ->assertSessionHasErrors(['name', 'category', 'price', 'stock']);

    $this->assertDatabaseCount('products', 0);
});

it('authenticates an administrator with the seeded-account credentials', function () {
    User::factory()->create([
        'email' => 'admin@minimarket.test',
        'password' => 'password',
        'role' => 'admin',
    ]);

    $this->post(route('login.store'), [
        'email' => 'admin@minimarket.test',
        'password' => 'password',
    ])->assertRedirect(route('products.index'));

    $this->assertAuthenticated();
});

it('seeds accounts and 200 demo products without duplicating records', function () {
    $this->seed(DatabaseSeeder::class);
    $this->seed(DatabaseSeeder::class);

    $this->assertDatabaseCount('users', 2);
    $this->assertDatabaseHas('users', [
        'email' => 'admin@minimarket.test',
        'role' => 'admin',
    ]);
    $this->assertDatabaseHas('users', [
        'email' => 'kasir@minimarket.test',
        'role' => 'kasir',
    ]);
    $this->assertDatabaseCount('suppliers', 3);
    $this->assertDatabaseCount('products', 200);
    $this->assertDatabaseHas('products', [
        'name' => 'Demo 001 - Beras Premium',
        'category' => 'Sembako',
    ]);

    expect(Hash::check('password', User::query()->where('email', 'admin@minimarket.test')->value('password')))->toBeTrue();
});
