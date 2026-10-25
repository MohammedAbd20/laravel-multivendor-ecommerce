<?php

use App\Models\Admin;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;

function signInAdmin(array $attributes = []): Admin
{
    $admin = Admin::factory()->create($attributes + ['super_admin' => true, 'status' => 'active']);
    test()->actingAs($admin, 'admin');

    return $admin;
}

function makeStore(array $attributes = []): Store
{
    static $n = 0;
    $n++;

    return Store::create($attributes + ['name' => "Store {$n}", 'slug' => "store-{$n}", 'status' => 'active']);
}

function makeProduct(?Store $store = null, array $attributes = []): Product
{
    static $n = 0;
    $n++;
    $store ??= makeStore();
    $category = Category::firstOrCreate(['slug' => 'general'], ['name' => 'General', 'status' => 'active']);

    return Product::create($attributes + [
        'name' => "Product {$n}", 'store_id' => $store->id, 'category_id' => $category->id,
        'price' => 10, 'quantity' => 20, 'status' => 'active',
    ]);
}

/** @param array<int, array{0: Product, 1: int}> $lines */
function makeOrder(Store $store, array $lines, array $attributes = []): Order
{
    $order = Order::create($attributes + [
        'store_id' => $store->id, 'user_id' => User::factory()->create()->id,
        'payment_method' => 'cod', 'status' => 'pending', 'payment_status' => 'pending',
    ]);
    foreach ($lines as [$product, $qty]) {
        OrderItem::create([
            'order_id' => $order->id, 'product_id' => $product->id, 'product_name' => $product->name,
            'price' => $product->price, 'quantity' => $qty,
        ]);
    }

    return $order;
}
