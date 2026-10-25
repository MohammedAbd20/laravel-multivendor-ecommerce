@extends('layouts.dashboard')
@section('title', 'New product')
@section('breadcrump')
    @parent
    <li><a href="{{ route('products.index') }}">Products</a></li>
    <li>New</li>
@endsection
@section('content')
    <form action="{{ route('products.store') }}" method="post" enctype="multipart/form-data">
        @csrf
        @include('dashboard.products._form')
    </form>
@endsection
