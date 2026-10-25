@extends('layouts.dashboard')

@section('title', 'Products')
@section('breadcrump')
    @parent
    <li>Products</li>
@endsection
@section('subtitle')
    {{ number_format($products->total()) }} {{ str('product')->plural($products->total()) }}{{ request()->hasAny(['q','store_id','category_id','stock','tag_id','status']) ? ' match your filters' : ' across all stores' }}.
@endsection
@section('actions')
    <a href="{{ route('products.trash') }}" class="btn btn-light"><i class="fas fa-trash-alt me-2"></i>Trash @if ($trashed)<span class="small-muted">{{ $trashed }}</span>@endif</a>
    <a href="{{ route('products.create') }}" class="btn btn-primary"><i class="fas fa-plus me-2"></i>New product</a>
@endsection

@section('content')
    @php
        $sortUrl = fn ($col, $first = 'desc') => request()->fullUrlWithQuery(['sort' => $col, 'dir' => ($sort === $col ? ($dir === 'desc' ? 'asc' : 'desc') : $first), 'page' => null]);
        $arrow = fn ($col) => $sort === $col ? ($dir === 'asc' ? ' ↑' : ' ↓') : '';
        $tabs = ['' => 'All', 'active' => 'Active', 'draft' => 'Draft', 'archived' => 'Archived'];
    @endphp

    <section class="panel panel-flush">
        <div class="px-3 pt-2 border-bottom" style="display:flex;gap:4px;flex-wrap:wrap">
            @foreach ($tabs as $value => $label)
                @php $n = $value === '' ? $counts->sum() : ($counts[$value] ?? 0); $on = (string) request('status') === (string) $value; @endphp
                <a href="{{ request()->fullUrlWithQuery(['status' => $value ?: null, 'page' => null]) }}" class="px-3 py-2 {{ $on ? 'fw-bold' : 'muted' }}"
                    style="border-bottom:2px solid {{ $on ? 'var(--brand)' : 'transparent' }};color:{{ $on ? 'var(--text)' : '' }}">
                    {{ $label }} <span class="small-muted">{{ $n }}</span>
                </a>
            @endforeach
        </div>

        <form class="filters" method="get" action="{{ route('products.index') }}">
            @if (request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
            <div class="field grow">
                <label for="f-q">Search</label>
                <input id="f-q" class="form-control" type="search" name="q" value="{{ request('q') }}" placeholder="Product name">
            </div>
            <div class="field">
                <label for="f-store">Store</label>
                <select id="f-store" class="form-select" name="store_id">
                    <option value="">All stores</option>
                    @foreach ($stores as $s)<option value="{{ $s->id }}" @selected(request('store_id') == $s->id)>{{ $s->name }}</option>@endforeach
                </select>
            </div>
            <div class="field">
                <label for="f-cat">Category</label>
                <select id="f-cat" class="form-select" name="category_id">
                    <option value="">All categories</option>
                    @foreach ($categories as $c)<option value="{{ $c->id }}" @selected(request('category_id') == $c->id)>{{ $c->name }}</option>@endforeach
                </select>
            </div>
            <div class="field">
                <label for="f-stock">Stock</label>
                <select id="f-stock" class="form-select" name="stock">
                    <option value="">Any</option>
                    <option value="in" @selected(request('stock') === 'in')>In stock</option>
                    <option value="low" @selected(request('stock') === 'low')>Low (5 or fewer)</option>
                    <option value="out" @selected(request('stock') === 'out')>Out of stock</option>
                </select>
            </div>
            <button class="btn btn-primary">Apply</button>
            @if (request()->hasAny(['q','store_id','category_id','stock','tag_id']))
                <a class="btn btn-ghost" href="{{ route('products.index', array_filter(['status' => request('status')])) }}">Clear</a>
            @endif
        </form>

        <form id="bulkForm" action="{{ route('products.bulk') }}" method="post" data-confirm="Apply this change to the selected products?" data-confirm-label="Apply" data-confirm-only="delete" data-confirm-field="action">@csrf</form>
        <div class="px-3 py-2 border-bottom" data-bulk-bar="products" hidden style="background:var(--brand-tint)">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <strong data-bulk-count></strong>
                <select class="form-select form-select-sm" name="action" form="bulkForm" style="width:auto" aria-label="Bulk action">
                    <option value="active">Set as active</option>
                    <option value="draft">Set as draft</option>
                    <option value="archived">Archive</option>
                    <option value="delete">Move to trash</option>
                </select>
                <button class="btn btn-sm btn-primary" form="bulkForm">Apply</button>
            </div>
        </div>

        <div class="tbl-wrap">
            <table class="tbl">
                <thead>
                    <tr>
                        <th style="width:40px"><input class="form-check-input" type="checkbox" data-check-all="products" aria-label="Select all on this page"></th>
                        <th class="{{ $sort === 'name' ? 'sorted' : '' }}"><a href="{{ $sortUrl('name', 'asc') }}">Product{{ $arrow('name') }}</a></th>
                        <th>Store</th>
                        <th>Category</th>
                        <th class="num {{ $sort === 'price' ? 'sorted' : '' }}"><a href="{{ $sortUrl('price') }}">Price{{ $arrow('price') }}</a></th>
                        <th class="{{ $sort === 'quantity' ? 'sorted' : '' }}"><a href="{{ $sortUrl('quantity', 'asc') }}">Stock{{ $arrow('quantity') }}</a></th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        @php $q = (int) $product->quantity; $level = $q <= 0 ? 'out' : ($q <= \App\Models\Product::LOW_STOCK ? 'low' : ''); @endphp
                        <tr>
                            <td><input class="form-check-input" type="checkbox" name="ids[]" value="{{ $product->id }}" form="bulkForm" data-check="products" aria-label="Select {{ $product->name }}"></td>
                            <td>
                                <div class="cell-product">
                                    <img class="thumb" src="{{ $product->image_url }}" alt="">
                                    <div>
                                        <a class="row-link" href="{{ route('products.edit', $product->id) }}">{{ $product->name }}</a>
                                        <div class="sub">
                                            @if ($product->featured)<i class="fas fa-star" style="color:var(--warn)" title="Featured"></i> @endif
                                            {{ $product->tags->pluck('name')->take(3)->implode(', ') }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>@if ($product->store)<a class="store-chip" style="--c: {{ $product->store->color }}" href="{{ route('stores.show', $product->store) }}"><i></i>{{ $product->store->name }}</a>@endif</td>
                            <td class="muted">{{ $product->category?->name ?? '-' }}</td>
                            <td class="num">
                                <span class="fw-semibold">${{ number_format($product->price, 2) }}</span>
                                @if ($product->compare_price)<div class="small-muted"><s>${{ number_format($product->compare_price, 2) }}</s></div>@endif
                            </td>
                            <td>
                                <div class="meter {{ $level }}">
                                    <div class="meter-bar"><span style="width: {{ min(100, max($q > 0 ? 6 : 0, $q / 60 * 100)) }}%"></span></div>
                                    <span class="meter-n">{{ $q }}</span>
                                </div>
                            </td>
                            <td><span class="status status-{{ $product->status }}">{{ ucfirst($product->status) }}</span></td>
                            <td class="cell-actions">
                                <a class="btn btn-sm btn-light" href="{{ route('products.edit', $product->id) }}">Edit</a>
                                <form class="d-inline" action="{{ route('products.destroy', $product->id) }}" method="post" data-confirm="Move &quot;{{ $product->name }}&quot; to the trash? You can restore it later." data-confirm-label="Move to trash">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-ghost" title="Move to trash" aria-label="Move {{ $product->name }} to trash"><i class="fas fa-trash-alt"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8"><div class="empty"><h3>No products found</h3><p class="mb-0">Change the filters, or add a new product.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($products->hasPages())
            <div class="pager"><span class="small-muted">Showing {{ $products->firstItem() }}-{{ $products->lastItem() }} of {{ $products->total() }}</span>{{ $products->links('pagination::bootstrap-5') }}</div>
        @endif
    </section>
@endsection
