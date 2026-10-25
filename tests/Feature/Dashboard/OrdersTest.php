<?php

require_once __DIR__ . '/helpers.php';

use App\Models\Order;

test('orders can be filtered by status and searched', function () {
    signInAdmin();
    $store = makeStore();
    $product = makeProduct($store);
    $pending = makeOrder($store, [[$product, 1]]);
    $done = makeOrder($store, [[$product, 1]], ['status' => 'completed']);

    $this->get('/admin/orders?status=completed')
        ->assertOk()->assertSee('#' . $done->number)->assertDontSee('#' . $pending->number);

    $this->get('/admin/orders?q=' . $pending->number)
        ->assertOk()->assertSee('#' . $pending->number)->assertDontSee('#' . $done->number);
});

test('order details show items and the total', function () {
    signInAdmin();
    $store = makeStore();
    $order = makeOrder($store, [[makeProduct($store, ['price' => 12.5]), 3]]);

    $this->get("/admin/orders/{$order->id}")->assertOk()->assertSee('$37.50');
});

test('cancelling an order returns its items to stock and re-opening takes them out again', function () {
    signInAdmin();
    $store = makeStore();
    $product = makeProduct($store, ['quantity' => 10]);
    $order = makeOrder($store, [[$product, 3]]);

    $this->put("/admin/orders/{$order->id}", ['status' => 'cancelled', 'payment_status' => 'failed'])->assertRedirect();
    expect($product->fresh()->quantity)->toBe(13)->and($order->fresh()->status)->toBe('cancelled');

    $this->put("/admin/orders/{$order->id}", ['status' => 'processing', 'payment_status' => 'paid']);
    expect($product->fresh()->quantity)->toBe(10);

    // moving between two open statuses never touches stock
    $this->put("/admin/orders/{$order->id}", ['status' => 'delivering', 'payment_status' => 'paid']);
    expect($product->fresh()->quantity)->toBe(10);
});

test('invalid statuses are rejected', function () {
    signInAdmin();
    $store = makeStore();
    $order = makeOrder($store, [[makeProduct($store), 1]]);

    $this->put("/admin/orders/{$order->id}", ['status' => 'teleported', 'payment_status' => 'paid'])
        ->assertSessionHasErrors('status');
});

test('several orders can be updated at once', function () {
    signInAdmin();
    $store = makeStore();
    $product = makeProduct($store);
    $a = makeOrder($store, [[$product, 1]]);
    $b = makeOrder($store, [[$product, 1]]);

    $this->post('/admin/orders/bulk', ['ids' => [$a->id, $b->id], 'status' => 'processing'])->assertRedirect();

    expect(Order::whereIn('id', [$a->id, $b->id])->pluck('status')->unique()->all())->toBe(['processing']);
});

test('orders export as csv', function () {
    signInAdmin();
    $store = makeStore(['name' => 'Casa Bayt']);
    $order = makeOrder($store, [[makeProduct($store, ['price' => 20]), 2]]);

    $response = $this->get('/admin/orders/export')->assertOk();
    expect($response->headers->get('content-disposition'))->toContain('orders-')
        ->and($response->streamedContent())->toContain($order->number)->toContain('Casa Bayt')->toContain('40.00');
});
