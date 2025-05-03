@extends('layouts.dashboard')
@section('title','Trash products')
@section('breadcrump')
    @parent
    <li class="breadcrumb-item"><a href="{{ route('products.index') }}">products</a></li>
    <li class="breadcrumb-item active">Trash</li>
@endsection

@section('content')


    <div class="my-3">
        <a href="{{ route('products.index') }}" class="btn btn-outline-primary">Back</a>
        @if ($products->count() > 1)
            <button type="button" class="btn btn-outline-success action" data-bs-toggle="modal" data-bs-target="#restoreAllModal">
                Restore All
            </button>
            <button type="button" class="btn btn-outline-danger action" data-bs-toggle="modal" data-bs-target="#deleteAllModal">
                Delete All
            </button>
        @endif
    </div>
    <x-alert type="success" />
    <x-alert type="info"/>

    {{-- @if ($products->count() > 1) --}}
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
                <th>Status</th>
                <th>Created At</th>
                <th colspan="2"></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($products as $product)
                <tr>
                    <td><img src="{{ asset('storage/'.$product->image) }}" alt="" height="70"></td>
                    <td>{{ $product->id }}</td>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->parent_name }}</td>
                    <td>{{ $product->status }}</td>
                    <td>{{ $product->created_at }}</td>
                    {{-- <td><a href="{{ route('products.restore', $product->id) }}" class="btn btn-sm btn-outline-success">Restore</a></td> --}}
                    <td>
                        <!-- Button trigger modal -->
                        <button type="button" class="btn btn-outline-success action" data-bs-toggle="modal" data-bs-target="#restoreModal{{ $product->id }}">
                            Restore
                        </button>
                    </td>
                    <td>
                        <!-- Button trigger modal -->
                        <button type="button" class="btn btn-outline-danger action" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $product->id }}">
                            Delete
                        </button>
                    </td>
                </tr>

                <!-- Modal لكل فئة -->
                <div class="modal fade" id="deleteModal{{ $product->id }}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h3 class="modal-title" id="exampleModalLabel">Delete Confirmation</h3>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <h4>Are you sure you want to <span class="font-weight-bold">delete</span> this product?</h4>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">No</button>
                                <form action="{{ route('products.force-delete', $product->id) }}" method="post">
                                    @csrf
                                    @method('delete')
                                    <button type="submit" class="btn btn-outline-danger">Yes, Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal fade" id="restoreModal{{ $product->id }}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h3 class="modal-title" id="exampleModalLabel">Restore Confirmation</h3>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <h4>Are you sure you want to <span class="font-weight-bold">restore</span> this product?</h4>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">No</button>
                                <a href="{{ route('products.restore', $product->id) }}" class="btn btn-sm btn-outline-success py-2">Restore</a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <tr>
                    <td colspan="8">No products defined</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{ $products->withQueryString() ->links() }}



        <!-- Modal لكل فئة -->
        <div class="modal fade" id="restoreAllModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title" id="exampleModalLabel">Restore Confirmation</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <h4>Are you sure you want to <span class="font-weight-bold">Restore All</span> this product?</h4>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">No</button>
                        <form action="{{ route('products.restoreAll') }}" method="post">
                            @csrf
                            {{-- @method('delete') --}}
                            <button type="submit" class="btn btn-outline-danger">Yes, Restore</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="deleteAllModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title" id="exampleModalLabel">Delete Confirmation</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <h4>Are you sure you want to <span class="font-weight-bold">Delete All</span> this product?</h4>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">No</button>
                        <form action="{{ route('products.forceDeleteAll') }}" method="post">
                            @csrf
                            @method('delete')
                            <button type="submit" class="btn btn-outline-danger">Yes, Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
@endsection

