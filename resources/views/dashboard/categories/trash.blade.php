@extends('layouts.dashboard')

@section('title', 'Category trash')
@section('breadcrump')
    @parent
    <li><a href="{{ route('categories.index') }}">Categories</a></li>
    <li>Trash</li>
@endsection
@section('subtitle')
    Deleted categories stay here until you restore them or delete them permanently.
@endsection
@section('actions')
    <a href="{{ route('categories.index') }}" class="btn btn-light"><i class="fas fa-arrow-left me-2"></i>Back to categories</a>
    @if ($categories->total() > 0)
        <form action="{{ route('categories.restoreAll') }}" method="post" class="d-inline">@csrf<button class="btn btn-light">Restore all</button></form>
        <form action="{{ route('categories.forceDeleteAll') }}" method="post" class="d-inline" data-confirm="Permanently delete every category in the trash? This cannot be undone." data-confirm-label="Empty trash">@csrf @method('DELETE')<button class="btn btn-outline-danger">Empty trash</button></form>
    @endif
@endsection

@section('content')
    <section class="panel panel-flush">
        <div class="tbl-wrap">
            <table class="tbl">
                <thead><tr><th>Category</th><th>Deleted</th><th></th></tr></thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr>
                            <td class="fw-semibold">{{ $category->name }}</td>
                            <td class="muted">{{ $category->deleted_at->diffForHumans() }}</td>
                            <td class="cell-actions">
                                <form class="d-inline" action="{{ route('categories.restore', $category->id) }}" method="post">@csrf<button class="btn btn-sm btn-light">Restore</button></form>
                                <form class="d-inline" action="{{ route('categories.force-delete', $category->id) }}" method="post" data-confirm="Permanently delete &quot;{{ $category->name }}&quot;? This cannot be undone." data-confirm-label="Delete permanently">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Delete permanently</button></form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3"><div class="empty"><h3>The trash is empty</h3><p class="mb-0">Categories you delete will appear here.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($categories->hasPages())
            <div class="pager"><span class="small-muted">{{ $categories->total() }} in trash</span>{{ $categories->links('pagination::bootstrap-5') }}</div>
        @endif
    </section>
@endsection
