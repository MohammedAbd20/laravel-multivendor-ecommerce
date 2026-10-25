@extends('layouts.dashboard')
@section('title', 'Edit product')
@section('breadcrump')
    @parent
    <li><a href="{{ route('products.index') }}">Products</a></li>
    <li>{{ $product->name }}</li>
@endsection
@section('subtitle')
    {{ $product->name }}. Last updated {{ $product->updated_at?->diffForHumans() }}.
@endsection
@section('actions')
    @if ($product->status === 'active' && $product->store?->status === 'active')
        <a class="btn btn-light" target="_blank" rel="noopener" href="{{ route('productsF.show', $product->slug) }}"><i class="fas fa-external-link-alt me-2"></i>View in storefront</a>
    @endif
@endsection
@section('content')
    <form action="{{ route('products.update', $product->id) }}" method="post" enctype="multipart/form-data">
        @csrf @method('PUT')
        @include('dashboard.products._form')
    </form>
@endsection
