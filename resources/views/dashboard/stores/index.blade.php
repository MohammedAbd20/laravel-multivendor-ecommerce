@extends('layouts.dashboard')

@section('title', 'Stores')
@section('breadcrump')
    @parent
    <li>Stores</li>
@endsection
@section('subtitle')
    Every vendor selling on the marketplace. Inactive and archived stores are hidden from the storefront.
@endsection
@section('actions')
    <a href="{{ route('stores.create') }}" class="btn btn-primary"><i class="fas fa-plus me-2"></i>New store</a>
@endsection

@section('content')
    <section class="panel panel-flush">
        <form class="filters" method="get" action="{{ route('stores.index') }}">
            <div class="field grow">
                <label for="f-name">Store name</label>
                <input id="f-name" class="form-control" type="search" name="name" value="{{ request('name') }}" placeholder="Search stores">
            </div>
            <div class="field">
                <label for="f-status">Status</label>
                <select id="f-status" class="form-select" name="status">
                    <option value="">All</option>
                    @foreach (\App\Models\Store::STATUSES as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <button class="btn btn-primary">Apply</button>
            @if (request()->hasAny(['name', 'status']))<a class="btn btn-ghost" href="{{ route('stores.index') }}">Clear</a>@endif
        </form>

        <div class="tbl-wrap">
            <table class="tbl">
                <thead>
                    <tr><th>Store</th><th>Status</th><th class="num">Products</th><th class="num">Orders</th><th class="num">Revenue</th><th>Created</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse ($stores as $store)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    @if ($store->logo_url)
                                        <img class="store-logo" src="{{ $store->logo_url }}" alt="">
                                    @else
                                        <span class="store-logo" style="--c: {{ $store->color }}">{{ $store->initials }}</span>
                                    @endif
                                    <div>
                                        <a class="row-link" href="{{ route('stores.show', $store) }}">{{ $store->name }}</a>
                                        <div class="small-muted">/{{ $store->slug }}</div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="status status-{{ $store->status }}">{{ ucfirst($store->status) }}</span></td>
                            <td class="num"><a href="{{ route('products.index', ['store_id' => $store->id]) }}">{{ number_format($store->products_count) }}</a></td>
                            <td class="num"><a href="{{ route('orders.index', ['store_id' => $store->id]) }}">{{ number_format($store->orders_count) }}</a></td>
                            <td class="num fw-semibold">${{ number_format($store->revenue, 0) }}</td>
                            <td class="muted">{{ $store->created_at?->format('M j, Y') }}</td>
                            <td class="cell-actions">
                                <form class="d-inline" action="{{ route('stores.status', $store) }}" method="post"
                                    @if ($store->status === 'active') data-confirm="Deactivating &quot;{{ $store->name }}&quot; hides its products from the storefront." data-confirm-label="Deactivate" @endif>
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="{{ $store->status === 'active' ? 'inactive' : 'active' }}">
                                    <button class="btn btn-sm btn-light">{{ $store->status === 'active' ? 'Deactivate' : 'Activate' }}</button>
                                </form>
                                <a class="btn btn-sm btn-light" href="{{ route('stores.edit', $store) }}">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><div class="empty"><h3>No stores found</h3><p class="mb-0">Add the first vendor to start selling.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($stores->hasPages())
            <div class="pager"><span class="small-muted">{{ $stores->total() }} stores</span>{{ $stores->links('pagination::bootstrap-5') }}</div>
        @endif
    </section>
@endsection
