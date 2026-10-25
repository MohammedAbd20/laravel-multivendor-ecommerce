<?php

require_once __DIR__ . '/helpers.php';

test('guests are sent to the admin login', function () {
    $this->get('/admin/dashboard')->assertRedirect();
    $this->get('/admin/orders')->assertRedirect();
});

test('dashboard shows revenue, orders and what needs attention', function () {
    signInAdmin();
    $store = makeStore(['name' => 'Nile Electronics']);
    $product = makeProduct($store, ['name' => 'Power Bank', 'price' => 25, 'quantity' => 2]);
    makeOrder($store, [[$product, 4]], ['status' => 'completed', 'payment_status' => 'paid']); // $100
    makeOrder($store, [[$product, 1]]);                                                         // $25, pending
    makeOrder($store, [[$product, 9]], ['status' => 'cancelled']);                              // ignored

    $this->get('/admin/dashboard')
        ->assertOk()
        ->assertSee('$125')                       // revenue ignores the cancelled order
        ->assertSee('1 order waiting to be accepted')
        ->assertSee('1 product with 5 or fewer left')
        ->assertSee('Nile Electronics')
        ->assertSee('Power Bank');
});

test('dashboard copes with an empty marketplace', function () {
    signInAdmin();

    $this->get('/admin/dashboard')
        ->assertOk()
        ->assertSee('No sales in this period')
        ->assertSee('Nothing needs your attention');
});
