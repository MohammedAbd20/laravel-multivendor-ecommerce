@extends('layouts.dashboard')

@section('title', 'Admins')
@section('breadcrump')
    @parent
    <li>Admins</li>
@endsection
@section('subtitle')
    People who can sign in to this dashboard. Only super admins can manage this list.
@endsection
@section('actions')
    <a href="{{ route('admins.create') }}" class="btn btn-primary"><i class="fas fa-plus me-2"></i>New admin</a>
@endsection

@section('content')
    <section class="panel panel-flush">
        <div class="tbl-wrap">
            <table class="tbl">
                <thead><tr><th>Admin</th><th>Username</th><th>Phone</th><th>Role</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @foreach ($admins as $a)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <span class="avatar">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($a->name, 0, 1)) }}</span>
                                    <div>
                                        <div class="fw-semibold">{{ $a->name }} @if ($a->id === Auth::id())<span class="small-muted">(you)</span>@endif</div>
                                        <div class="small-muted">{{ $a->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $a->username }}</td>
                            <td class="muted">{{ $a->phone_number }}</td>
                            <td>{{ $a->super_admin ? 'Super admin' : 'Admin' }}</td>
                            <td><span class="status status-{{ $a->status }}">{{ ucfirst($a->status) }}</span></td>
                            <td class="cell-actions">
                                <a class="btn btn-sm btn-light" href="{{ route('admins.edit', $a) }}">Edit</a>
                                @if ($a->id !== Auth::id())
                                    <form class="d-inline" action="{{ route('admins.destroy', $a) }}" method="post" data-confirm="Delete {{ $a->name }}? They will no longer be able to sign in." data-confirm-label="Delete admin">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($admins->hasPages())
            <div class="pager"><span class="small-muted">{{ $admins->total() }} admins</span>{{ $admins->links('pagination::bootstrap-5') }}</div>
        @endif
    </section>
@endsection
