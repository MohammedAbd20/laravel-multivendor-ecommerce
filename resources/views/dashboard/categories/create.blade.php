@extends('layouts.dashboard')
@section('title', 'New category')
@section('breadcrump')
    @parent
    <li><a href="{{ route('categories.index') }}">Categories</a></li>
    <li>New</li>
@endsection
@section('content')
    <form class="panel" style="max-width:860px" action="{{ route('categories.store') }}" method="post" enctype="multipart/form-data">
        @csrf
        @include('dashboard.categories._form')
        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Create category</button>
            <a class="btn btn-light" href="{{ route('categories.index') }}">Cancel</a>
        </div>
    </form>
@endsection
