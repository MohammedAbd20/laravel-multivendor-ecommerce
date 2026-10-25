<?php

/*
 | Dashboard sidebar. Each group has a title and its items.
 |  - route:        named route
 |  - active:       URL pattern (without leading slash) that marks the item active
 |  - badge:        key resolved in App\View\Components\Nav (e.g. pending order count)
 |  - super_admin:  only shown to super admins
 */
return [
    [
        'title' => 'Overview',
        'items' => [
            ['icon' => 'fas fa-tachometer-alt', 'route' => 'dashboard', 'title' => 'Dashboard', 'active' => 'admin/dashboard'],
        ],
    ],
    [
        'title' => 'Sales',
        'items' => [
            ['icon' => 'fas fa-receipt', 'route' => 'orders.index', 'title' => 'Orders', 'active' => 'admin/orders*', 'badge' => 'pending_orders'],
        ],
    ],
    [
        'title' => 'Catalog',
        'items' => [
            ['icon' => 'fas fa-box-open', 'route' => 'products.index', 'title' => 'Products', 'active' => 'admin/products*'],
            ['icon' => 'fas fa-folder-open', 'route' => 'categories.index', 'title' => 'Categories', 'active' => 'admin/categories*'],
        ],
    ],
    [
        'title' => 'Marketplace',
        'items' => [
            ['icon' => 'fas fa-store', 'route' => 'stores.index', 'title' => 'Stores', 'active' => 'admin/stores*'],
        ],
    ],
    [
        'title' => 'People',
        'items' => [
            ['icon' => 'fas fa-users', 'route' => 'customers.index', 'title' => 'Customers', 'active' => 'admin/customers*'],
            ['icon' => 'fas fa-user-shield', 'route' => 'admins.index', 'title' => 'Admins', 'active' => 'admin/admins*', 'super_admin' => true],
        ],
    ],
];
