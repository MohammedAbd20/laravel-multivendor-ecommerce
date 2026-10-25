<?php

require_once __DIR__ . '/Dashboard/helpers.php';

use App\Models\Cart;
use App\Models\Order;
use App\Models\User;

test('checkout creates one order per store with store, items and addresses, and empties the cart', function () {
    $nile = makeStore(['name' => 'Nile']);
    $casa = makeStore(['name' => 'Casa']);
    $a = makeProduct($nile, ['price' => 10, 'quantity' => 20]);
    $b = makeProduct($casa, ['price' => 5, 'quantity' => 8]);
    $user = User::factory()->create();

    $shopper = $this->actingAs($user)->withCookie('cart_id', 'cart-test');
    foreach ([[$a, 2], [$b, 3]] as [$product, $qty]) {
        $shopper->post('/cart', ['product_id' => $product->id, 'quantity' => $qty])->assertRedirect('/cart');
    }

    $address = [
        'first_name' => 'Lina', 'last_name' => 'Said', 'email' => 'lina@example.com', 'phone_number' => '0591111111',
        'street_address' => 'Al-Rimal St 5', 'city' => 'Gaza', 'country' => 'PS',
    ];

    $shopper->post('/checkout', ['addr' => ['billing' => $address, 'shipping' => $address]])
        ->assertRedirect('/');

    $orders = Order::with(['items', 'addresses'])->orderBy('id')->get();
    expect($orders)->toHaveCount(2)
        ->and($orders->pluck('store_id')->sort()->values()->all())->toBe([$nile->id, $casa->id])
        ->and($orders[0]->addresses->pluck('type')->sort()->values()->all())->toBe(['billing', 'shipping'])
        ->and($a->fresh()->quantity)->toBe(18)
        ->and($b->fresh()->quantity)->toBe(5)
        ->and(Cart::withoutGlobalScopes()->where('cookie_id', 'cart-test')->count())->toBe(0);
});
