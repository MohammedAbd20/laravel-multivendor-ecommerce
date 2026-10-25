@extends('layouts.dashboard')

@section('title', 'Categories')
@section('breadcrump')
    @parent
    <li>Categories</li>
@endsection
@section('subtitle')
    Group products so shoppers can find them. Sub-categories sit under a parent.
@endsection
@section('actions')
    <a href="{{ route('categories.trash') }}" class="btn btn-light"><i class="fas fa-trash-alt me-2"></i>Trash @if ($trashed)<span class="small-muted">{{ $trashed }}</span>@endif</a>
    <a href="{{ route('categories.create') }}" class="btn btn-primary"><i class="fas fa-plus me-2"></i>New category</a>
@endsection

@section('content')
    @php
        $sortUrl = fn ($col, $first = 'asc') => request()->fullUrlWithQuery(['sort' => $col, 'dir' => ($sort === $col ? ($dir === 'desc' ? 'asc' : 'desc') : $first), 'page' => null]);
        $arrow = fn ($col) => $sort === $col ? ($dir === 'asc' ? ' ↑' : ' ↓') : '';
    @endphp
    <section class="panel panel-flush">
        <form class="filters" method="get" action="{{ route('categories.index') }}">
            <div class="field grow">
                <label for="f-q">Search</label>
                <input id="f-q" class="form-control" type="search" name="q" value="{{ request('q') }}" placeholder="Category name">
            </div>
            <div class="field">
                <label for="f-status">Status</label>
                <select id="f-status" class="form-select" name="status">
                    <option value="">All</option>
                    @foreach (\App\Models\Category::STATUSES as $s)<option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>@endforeach
                </select>
            </div>
            <button class="btn btn-primary">Apply</button>
            @if (request()->hasAny(['q', 'status']))<a class="btn btn-ghost" href="{{ route('categories.index') }}">Clear</a>@endif
        </form>
        <div class="tbl-wrap">
            <table class="tbl">
                <thead>
                    <tr>
                        <th class="{{ $sort === 'name' ? 'sorted' : '' }}"><a href="{{ $sortUrl('name') }}">Category{{ $arrow('name') }}</a></th>
                        <th>Parent</th>
                        <th class="num {{ $sort === 'products_count' ? 'sorted' : '' }}"><a href="{{ $sortUrl('products_count', 'desc') }}">Products{{ $arrow('products_count') }}</a></th>
                        <th>Status</th>
                        <th class="{{ $sort === 'created_at' ? 'sorted' : '' }}"><a href="{{ $sortUrl('created_at', 'desc') }}">Created{{ $arrow('created_at') }}</a></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr>
                            <td>
                                <a class="row-link" href="{{ route('categories.edit', $category->id) }}">{{ $category->name }}</a>
                                @if ($category->children_count)<div class="small-muted">{{ $category->children_count }} {{ str('sub-category')->plural($category->children_count) }}</div>@endif
                            </td>
                            <td class="muted">{{ $category->parent_id ? $category->parent->name : '-' }}</td>
                            <td class="num"><a href="{{ route('products.index', ['category_id' => $category->id]) }}">{{ number_format($category->products_count) }}</a></td>
                            <td><span class="status status-{{ $category->status }}">{{ ucfirst($category->status) }}</span></td>
                            <td class="muted">{{ $category->created_at?->format('M j, Y') }}</td>
                            <td class="cell-actions">
                                <a class="btn btn-sm btn-light" href="{{ route('categories.edit', $category->id) }}">Edit</a>
                                <form class="d-inline" action="{{ route('categories.destroy', $category->id) }}" method="post" data-confirm="Move &quot;{{ $category->name }}&quot; to the trash? Its {{ $category->products_count }} {{ str('product')->plural($category->products_count) }} will lose this category while it is in the trash." data-confirm-label="Move to trash">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-ghost" title="Move to trash" aria-label="Move {{ $category->name }} to trash"><i class="fas fa-trash-alt"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="empty"><h3>No categories found</h3><p class="mb-0">Create the first category to organise your catalog.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($categories->hasPages())
            <div class="pager"><span class="small-muted">Showing {{ $categories->firstItem() }}-{{ $categories->lastItem() }} of {{ $categories->total() }}</span>{{ $categories->links('pagination::bootstrap-5') }}</div>
        @endif
    </section>
@endsection
