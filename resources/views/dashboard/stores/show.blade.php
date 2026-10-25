@extends('layouts.dashboard')
@section('title', $store->name)
@section('breadcrump')
    @parent
    <li><a href="{{ route('stores.index') }}">Stores</a></li>
    <li>{{ $store->name }}</li>
@endsection
@section('actions')
    <a href="{{ route('stores.edit', $store) }}" class="btn btn-light"><i class="fas fa-pen me-2"></i>Edit</a>
    <a href="{{ route('products.create', ['store_id' => $store->id]) }}" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Add product</a>
@endsection

@section('content')
    <section class="panel">
        <div class="cover" style="--c: {{ $store->color }}; @if ($store->cover_image) background-image:url('{{ Str::startsWith($store->cover_image, ['http://','https://']) ? $store->cover_image : asset('storage/'.$store->cover_image) }}') @endif"></div>
        <div class="panel-body">
            <div class="d-flex gap-3 align-items-center flex-wrap" style="margin-top:-52px">
                @if ($store->logo_url)
                    <img class="store-logo lg" src="{{ $store->logo_url }}" alt="" style="border:3px solid #fff">
                @else
                    <span class="store-logo lg" style="--c: {{ $store->color }};border:3px solid #fff">{{ $store->initials }}</span>
                @endif
                <div style="margin-top:34px">
                    <span class="status status-{{ $store->status }}">{{ ucfirst($store->status) }}</span>
                </div>
            </div>
            <p class="muted mt-3 mb-4" style="max-width:640px">{{ $store->description ?: 'No description yet.' }}</p>
            <div class="stat-line">
                <div><b>${{ number_format($revenue, 0) }}</b><span>Revenue</span></div>
                <div><b>{{ number_format($orderCount) }}</b><span>Orders</span></div>
                <div><b>${{ number_format($aov, 2) }}</b><span>Average order</span></div>
                <div><b>{{ number_format($productCount) }}</b><span>Products</span></div>
            </div>
        </div>
    </section>

    <div class="row g-3">
        <div class="col-xl-7">
            <section class="panel panel-flush">
                <div class="panel-head"><h2>Recent orders</h2><a class="fw-semibold" href="{{ route('orders.index', ['store_id' => $store->id]) }}">All orders</a></div>
                <div class="tbl-wrap">
                    <table class="tbl">
                        <thead><tr><th>Order</th><th>Customer</th><th>Status</th><th class="num">Total</th></tr></thead>
                        <tbody>
                            @forelse ($recentOrders as $o)
                                <tr>
                                    <td><a class="row-link" href="{{ route('orders.show', $o) }}">#{{ $o->number }}</a><div class="small-muted">{{ $o->created_at->diffForHumans() }}</div></td>
                                    <td>{{ $o->user->name }}</td>
                                    <td><span class="status status-{{ $o->status }}">{{ ucfirst($o->status) }}</span></td>
                                    <td class="num">${{ number_format($o->total, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4"><div class="empty"><h3>No orders yet</h3></div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
        <div class="col-xl-5">
            <section class="panel panel-flush">
                <div class="panel-head"><h2>Stock to watch</h2><a class="fw-semibold" href="{{ route('products.index', ['store_id' => $store->id]) }}">All products</a></div>
                <ul class="rank">
                    @forelse ($lowStock as $p)
                        <li>
                            <a class="row-link fw-semibold" style="color:var(--text)" href="{{ route('products.edit', $p->id) }}">{{ $p->name }}</a>
                            <span class="status {{ $p->quantity <= 0 ? 'status-cancelled' : 'status-pending' }}">{{ $p->quantity <= 0 ? 'Out of stock' : $p->quantity . ' left' }}</span>
                        </li>
                    @empty
                        <li><span class="muted">All active products are well stocked.</span></li>
                    @endforelse
                </ul>
            </section>
        </div>
    </div>
@endsection
