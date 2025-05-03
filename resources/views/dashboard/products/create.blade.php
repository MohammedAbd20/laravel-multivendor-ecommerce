@extends('layouts.dashboard')
@section('title','products')
@section('breadcrump')
    @parent
    <li class="breadcrumb-item"><a href="{{ route('products.index') }}">products</a></li>
    <li class="breadcrumb-item active">create</li>
@endsection

@section('content')

    <form action="{{ route('products.store') }}" method="post" enctype="multipart/form-data">
        @csrf
        @include('dashboard.products._form')
    </form>

@endsection
