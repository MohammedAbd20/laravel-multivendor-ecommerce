@extends('layouts.dashboard')
@section('title', 'New store')
@section('breadcrump')
    @parent
    <li><a href="{{ route('stores.index') }}">Stores</a></li>
    <li>New</li>
@endsection
@section('content')
    <form class="panel" action="{{ route('stores.store') }}" method="post" enctype="multipart/form-data" style="max-width:860px">
        @csrf
        @include('dashboard.stores._form')
        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Create store</button>
            <a class="btn btn-light" href="{{ route('stores.index') }}">Cancel</a>
        </div>
    </form>
@endsection
