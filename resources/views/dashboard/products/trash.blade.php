@extends('layouts.dashboard')

@section('title', 'Product trash')
@section('breadcrump')
    @parent
    <li><a href="{{ route('products.index') }}">Products</a></li>
    <li>Trash</li>
@endsection
@section('subtitle')
    Deleted products stay here until you restore them or delete them permanently.
@endsection
@section('actions')
    <a href="{{ route('products.index') }}" class="btn btn-light"><i class="fas fa-arrow-left me-2"></i>Back to products</a>
    @if ($products->total() > 0)
        <form action="{{ route('products.restoreAll') }}" method="post" class="d-inline">@csrf<button class="btn btn-light">Restore all</button></form>
        <form action="{{ route('products.forceDeleteAll') }}" method="post" class="d-inline" data-confirm="Permanently delete every product in the trash? This cannot be undone." data-confirm-label="Empty trash">@csrf @method('DELETE')<button class="btn btn-outline-danger">Empty trash</button></form>
    @endif
@endsection

@section('content')
    <section class="panel panel-flush">
        <div class="tbl-wrap">
            <table class="tbl">
                <thead><tr><th>Product</th><th>Store</th><th class="num">Price</th><th>Deleted</th><th></th></tr></thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td><div class="cell-product"><img class="thumb" src="{{ $product->image_url }}" alt=""><div class="fw-semibold">{{ $product->name }}<div class="sub">{{ $product->category?->name }}</div></div></div></td>
                            <td>@if ($product->store)<span class="store-chip" style="--c: {{ $product->store->color }}"><i></i>{{ $product->store->name }}</span>@endif</td>
                            <td class="num">${{ number_format($product->price, 2) }}</td>
                            <td class="muted">{{ $product->deleted_at->diffForHumans() }}</td>
                            <td class="cell-actions">
                                <form class="d-inline" action="{{ route('products.restore', $product->id) }}" method="post">@csrf<button class="btn btn-sm btn-light">Restore</button></form>
                                <form class="d-inline" action="{{ route('products.force-delete', $product->id) }}" method="post" data-confirm="Permanently delete &quot;{{ $product->name }}&quot;? This cannot be undone." data-confirm-label="Delete permanently">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Delete permanently</button></form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="empty"><h3>The trash is empty</h3><p class="mb-0">Products you delete will appear here.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($products->hasPages())
            <div class="pager"><span class="small-muted">{{ $products->total() }} in trash</span>{{ $products->links('pagination::bootstrap-5') }}</div>
        @endif
    </section>
@endsection
