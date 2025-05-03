@extends('layouts.dashboard')
@section('title','categories')
@section('breadcrump')
    @parent
    <li class="breadcrumb-item active">categories</li>
@endsection

@section('content')


    <div class="my-3">
        <a href="{{ route('categories.create') }}" class="btn btn-outline-primary">create</a>
        <a href="{{ route('categories.trash') }}" class="btn btn-outline-primary">trash</a>
        {{-- <a href="{{ route('categories.trash') }}" class="btn btn-outline-primary">delete all</a> --}}
        @if ($categories->count() > 1)
            <button type="button" class="btn btn-outline-danger action" data-bs-toggle="modal" data-bs-target="#deleteAllModal">
                delete all
            </button>
        @endif
    </div>
    <x-alert type="success" />
    <x-alert type="info"/>
    <x-alert type="danger"/>

    {{-- @if ($categories->count() > 1) --}}
        <form action="{{ URL::current() }}" method="get" class="d-flex justify-content-between mb-3">
            <x-form.input name="name" placeholder="Name" class="mx-2" :value="request('name')"/>
            <select name="status" class="form-control mx-2">
                <option value="">All</option>
                <option value="active" @selected(request('status')=='active')>Active</option>
                <option value="archived" @selected(request('status')=='archived')>Archived</option>
            </select>
            <button class="btn btn-dark">Filter</button>
        </form>
    {{-- @endif --}}
    <table class="table">
        <thead>
            <tr>
                <th></th>
                <th>ID</th>
                <th>Name</th>
                <th>Parent</th>
                <th>Count Product</th>
                <th>Status</th>
                <th>Created At</th>
                <th colspan="2"></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($categories as $category)
                <tr>
                    <td><img src="{{ file_exists(public_path('storage/'.$category->image)) ? asset('storage/'.$category->image) : $category->image }}" alt="" height="70"></td>
                    <td>{{ $category->id }}</td>
                    <td>{{ $category->name }}</td>
                    <td>{{ $category->parent->name }}</td>
                    <td>{{ $category->products_count}}</td>
                    <td>{{ $category->status }}</td>
                    <td>{{ $category->created_at }}</td>
                    <td><a href="{{ route('categories.edit', $category->id) }}" class="btn btn-sm btn-outline-success">Edit</a></td>
                    <td>
                        <!-- Button trigger modal -->
                        <button type="button" class="btn btn-outline-danger action" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $category->id }}">
                            Delete
                        </button>
                    </td>
                </tr>

                <!-- Modal لكل فئة -->
                <div class="modal fade" id="deleteModal{{ $category->id }}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h3 class="modal-title" id="exampleModalLabel">Delete Confirmation</h3>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <h4>Are you sure you want to <span class="font-weight-bold">delete</span> this category?</h4>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">No</button>
                                <form action="{{ route('categories.destroy', $category->id) }}" method="post">
                                    @csrf
                                    @method('delete')
                                    <button type="submit" class="btn btn-outline-danger">Yes, Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <tr>
                    <td colspan="8">No categories defined</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="d-flex justify-content-between  align-items-center">
        {{ $categories->withQueryString() ->links() }}
        <b>All Categories: {{ $categories->total() }}</b>
    </div>


    <!-- Modal لكل فئة -->
    <div class="modal fade" id="deleteAllModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title" id="exampleModalLabel">Delete Confirmation</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h4>Are you sure you want to <span class="font-weight-bold">Delete All</span> this category?</h4>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">No</button>
                    <form action="{{ route('categories.destroyAll') }}" method="post">
                        @csrf
                        @method('delete')
                        <button type="submit" class="btn btn-outline-danger">Yes, Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

