<?php

require_once __DIR__ . '/helpers.php';

use App\Models\Product;
use App\Models\Store;

test('a store can be created with a unique slug', function () {
    signInAdmin();
    makeStore(['name' => 'Zaytoun Wear', 'slug' => 'zaytoun-wear']);

    $this->post('/admin/stores', ['name' => 'Zaytoun Wear', 'slug' => '', 'status' => 'active'])->assertRedirect();

    expect(Store::pluck('slug')->all())->toContain('zaytoun-wear', 'zaytoun-wear-2');
});

test('a store needs a name and a valid status', function () {
    signInAdmin();

    $this->post('/admin/stores', ['name' => '', 'status' => 'closed'])->assertSessionHasErrors(['name', 'status']);
});

test('stores can be deactivated and their products leave the storefront', function () {
    signInAdmin();
    $store = makeStore();
    $product = makeProduct($store);
    expect(Product::active()->count())->toBe(1);

    $this->patch("/admin/stores/{$store->id}/status", ['status' => 'inactive'])->assertRedirect();

    expect($store->fresh()->status)->toBe('inactive')->and(Product::active()->count())->toBe(0);
    $this->get("/productsF/{$product->slug}")->assertNotFound();
});

test('a store with orders cannot be deleted, an empty one can', function () {
    signInAdmin();
    $busy = makeStore();
    makeOrder($busy, [[makeProduct($busy), 1]]);
    $empty = makeStore();

    $this->delete("/admin/stores/{$busy->id}")->assertSessionHas('danger');
    expect(Store::find($busy->id))->not->toBeNull();

    $this->delete("/admin/stores/{$empty->id}")->assertRedirect('/admin/stores');
    expect(Store::find($empty->id))->toBeNull();
});

test('the store page shows revenue for that store only', function () {
    signInAdmin();
    $a = makeStore(['name' => 'Alpha']);
    $b = makeStore(['name' => 'Beta']);
    makeOrder($a, [[makeProduct($a, ['price' => 30]), 2]]);
    makeOrder($b, [[makeProduct($b, ['price' => 999]), 1]]);

    $this->get("/admin/stores/{$a->id}")->assertOk()->assertSee('$60')->assertDontSee('$999');
});
