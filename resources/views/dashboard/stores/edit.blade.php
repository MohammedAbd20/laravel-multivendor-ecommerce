@extends('layouts.dashboard')
@section('title', 'Edit ' . $store->name)
@section('breadcrump')
    @parent
    <li><a href="{{ route('stores.index') }}">Stores</a></li>
    <li><a href="{{ route('stores.show', $store) }}">{{ $store->name }}</a></li>
    <li>Edit</li>
@endsection
@section('content')
    <form class="panel" action="{{ route('stores.update', $store) }}" method="post" enctype="multipart/form-data" style="max-width:860px">
        @csrf @method('PUT')
        @include('dashboard.stores._form')
        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Save changes</button>
            <a class="btn btn-light" href="{{ route('stores.show', $store) }}">Cancel</a>
        </div>
    </form>

    <section class="panel" style="max-width:860px">
        <div class="panel-head"><h2>Delete store</h2></div>
        <div class="panel-body d-flex justify-content-between align-items-center gap-3 flex-wrap">
            <p class="muted mb-0" style="max-width:520px">Deleting a store also deletes its products. Stores that have orders cannot be deleted; archive them instead.</p>
            <form action="{{ route('stores.destroy', $store) }}" method="post" data-confirm="Delete &quot;{{ $store->name }}&quot; and all of its products? This cannot be undone." data-confirm-label="Delete store">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger">Delete store</button>
            </form>
        </div>
    </section>
@endsection
