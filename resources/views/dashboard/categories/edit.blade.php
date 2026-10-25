@extends('layouts.dashboard')
@section('title', 'Edit category')
@section('breadcrump')
    @parent
    <li><a href="{{ route('categories.index') }}">Categories</a></li>
    <li>{{ $category->name }}</li>
@endsection
@section('content')
    <form class="panel" style="max-width:860px" action="{{ route('categories.update', $category->id) }}" method="post" enctype="multipart/form-data">
        @csrf @method('PUT')
        @include('dashboard.categories._form')
        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Save changes</button>
            <a class="btn btn-light" href="{{ route('categories.index') }}">Cancel</a>
        </div>
    </form>
@endsection
