@extends('layouts.dashboard')
@section('title','products')
@section('breadcrump')
    @parent
    <li class="breadcrumb-item active">products</li>
@endsection

@section('content')


    <div class="my-3">
        <a href="{{ route('products.create') }}" class="btn btn-outline-primary">create</a>
        <a href="{{ route('products.trash') }}" class="btn btn-outline-primary">trash</a>
        {{-- <a href="{{ route('products.trash') }}" class="btn btn-outline-primary">delete all</a> --}}
        @if ($products->count() > 1)
            <button type="button" class="btn btn-outline-danger action" data-bs-toggle="modal" data-bs-target="#deleteAllModal">
                delete all
            </button>
        @endif
    </div>
    <x-alert type="success" />
    <x-alert type="info"/>
    <x-alert type="danger"/>

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
                @if (Auth::user()->store_id)
                    <th>#</th>
                @endif
                <th></th>
                <th>ID</th>
                <th>Name</th>
                <th>Category</th>
                @if (!Auth::user()->store_id)
                    <th>Store</th>
                @endif
                <th>Price</th>
                <th>Status</th>
                <th>Created At</th>
                <th colspan="2"></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($products as $index => $product)
                <tr>
                    @if (Auth::user()->store_id)
                        <td>{{ $index + 1 + ($products->currentPage() - 1) * $products->perPage() }}</td>
                    @endif
                    <td><img src="{{ file_exists(public_path('storage/'.$product->image)) ? asset('storage/'.$product->image) : $product->image }}  " alt="" height="70"></td>
                    <td>{{ $product->id }}</td>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->category->name}}</td>
                    @if (!Auth::user()->store_id)
                        <td>{{ $product->store->name }}</td>
                    @endif
                    <td>{{ $product->price }}</td>
                    <td>{{ $product->status }}</td>
                    <td>{{ $product->created_at }}</td>
                    <td><a href="{{ route('products.edit', $product->id) }}" class="btn btn-sm btn-outline-success">Edit</a></td>
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
                                <form action="{{ route('products.destroy', $product->id) }}" method="post">
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
                    <td colspan="8">No products defined</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="d-flex justify-content-between  align-items-center">
        {{ $products->withQueryString() ->links() }}
        <b>All Products: {{ $products->total() }}</b>
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
                    <h4>Are you sure you want to <span class="font-weight-bold">Delete All</span> this product?</h4>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">No</button>
                    <form action="{{ route('products.destroyAll') }}" method="post">
                        @csrf
                        @method('delete')
                        <button type="submit" class="btn btn-outline-danger">Yes, Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

