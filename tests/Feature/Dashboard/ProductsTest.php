<?php

require_once __DIR__ . '/helpers.php';

use App\Models\Product;
use App\Models\Tag;

function validProduct(array $override = []): array
{
    $store = makeStore();

    return $override + [
        'name' => 'Wireless Earbuds', 'store_id' => $store->id, 'price' => 49.9, 'compare_price' => 69.9,
        'quantity' => 12, 'status' => 'active', 'tags' => 'New, Gift idea',
    ];
}

test('a product is saved with stock, tags and a unique slug', function () {
    signInAdmin();
    $payload = validProduct();

    $this->post('/admin/products', $payload)->assertRedirect('/admin/products');
    $this->post('/admin/products', $payload)->assertRedirect('/admin/products');

    $products = Product::orderBy('id')->get();
    expect($products)->toHaveCount(2)
        ->and($products[0]->quantity)->toBe(12)
        ->and($products[0]->slug)->toBe('wireless-earbuds')
        ->and($products[1]->slug)->toBe('wireless-earbuds-2')
        ->and($products[0]->tags->pluck('name')->sort()->values()->all())->toBe(['Gift idea', 'New'])
        ->and(Tag::count())->toBe(2);
});

test('product rules are enforced', function () {
    signInAdmin();

    $this->post('/admin/products', validProduct(['name' => '', 'price' => -1, 'quantity' => -5, 'status' => 'gone']))
        ->assertSessionHasErrors(['name', 'price', 'quantity', 'status']);

    $this->post('/admin/products', validProduct(['price' => 50, 'compare_price' => 40]))
        ->assertSessionHasErrors('compare_price');
});

test('editing a product updates stock and replaces its tags', function () {
    signInAdmin();
    $product = makeProduct(null, ['name' => 'Kettle', 'quantity' => 1]);
    $product->tags()->attach(Tag::create(['name' => 'Old', 'slug' => 'old'])->id);

    $this->put("/admin/products/{$product->id}", [
        'name' => 'Kettle', 'store_id' => $product->store_id, 'price' => 10, 'quantity' => 40,
        'status' => 'draft', 'tags' => 'Fresh',
    ])->assertRedirect();

    $product->refresh();
    expect($product->quantity)->toBe(40)->and($product->status)->toBe('draft')
        ->and($product->tags->pluck('name')->all())->toBe(['Fresh']);
});

test('the stock filter finds low and out-of-stock products', function () {
    signInAdmin();
    $store = makeStore();
    makeProduct($store, ['name' => 'Plenty', 'quantity' => 50]);
    makeProduct($store, ['name' => 'Almost Gone', 'quantity' => 3]);
    makeProduct($store, ['name' => 'Sold Out', 'quantity' => 0]);

    $this->get('/admin/products?stock=low')->assertSee('Almost Gone')->assertDontSee('Plenty')->assertDontSee('Sold Out');
    $this->get('/admin/products?stock=out')->assertSee('Sold Out')->assertDontSee('Almost Gone');
});

test('bulk actions change status or move products to the trash', function () {
    signInAdmin();
    $store = makeStore();
    $a = makeProduct($store);
    $b = makeProduct($store);

    $this->post('/admin/products/bulk', ['ids' => [$a->id, $b->id], 'action' => 'archived'])->assertRedirect();
    expect(Product::whereIn('id', [$a->id, $b->id])->pluck('status')->unique()->all())->toBe(['archived']);

    $this->post('/admin/products/bulk', ['ids' => [$a->id], 'action' => 'delete'])->assertRedirect();
    expect(Product::find($a->id))->toBeNull()->and(Product::onlyTrashed()->count())->toBe(1);
});

test('trashed products can be restored, and "empty trash" only affects trashed ones', function () {
    signInAdmin();
    $store = makeStore();
    $keep = makeProduct($store);
    $gone = makeProduct($store);
    $gone->delete();

    $this->post("/admin/products/{$gone->id}/restore")->assertRedirect();
    expect(Product::find($gone->id))->not->toBeNull();

    $gone->delete();
    $this->delete('/admin/products/force-delete-all')->assertRedirect();
    expect(Product::withTrashed()->find($gone->id))->toBeNull()->and(Product::find($keep->id))->not->toBeNull();
});
