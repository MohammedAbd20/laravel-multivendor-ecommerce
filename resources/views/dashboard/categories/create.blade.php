@extends('layouts.dashboard')
@section('title','categories')
@section('breadcrump')
    @parent
    <li class="breadcrumb-item"><a href="{{ route('categories.index') }}">categories</a></li>
    <li class="breadcrumb-item active">create</li>
@endsection

@section('content')

    <form action="{{ route('categories.store') }}" method="post" enctype="multipart/form-data">
        @csrf
        @include('dashboard.categories._form')
    </form>

@endsection
