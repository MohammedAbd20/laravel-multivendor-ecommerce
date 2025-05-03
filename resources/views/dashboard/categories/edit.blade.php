@extends('layouts.dashboard')
@section('title','Edit categories')
@section('breadcrump')
    @parent
    <li class="breadcrumb-item"><a href="{{ route('categories.index') }}">categories</a></li>
    <li class="breadcrumb-item active">edit</li>
@endsection

@section('content')

    <form action="{{ route('categories.update',$category->id) }}" method="post" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('dashboard.categories._form',[
            'button'=> "Update"
        ])
    </form>

@endsection
