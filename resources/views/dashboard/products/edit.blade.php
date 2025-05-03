@extends('layouts.dashboard')
@section('title','Edit products')
@section('breadcrump')
    @parent
    <li class="breadcrumb-item"><a href="{{ route('products.index') }}">products</a></li>
    <li class="breadcrumb-item active">edit</li>
@endsection

@section('content')

    <form action="{{ route('products.update',$product->id) }}" method="post" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('dashboard.products._form',[
            'button'=> "Update"
        ])
    </form>

@endsection
