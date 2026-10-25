@extends('layouts.dashboard')

@section('title', 'Customers')
@section('breadcrump')
    @parent
    <li>Customers</li>
@endsection
@section('subtitle')
    {{ number_format($customers->total()) }} {{ str('customer')->plural($customers->total()) }}{{ request('q') ? ' match your search' : ' have an account' }}.
@endsection

@section('content')
    @php
        $sortUrl = fn ($col, $first = 'desc') => request()->fullUrlWithQuery(['sort' => $col, 'dir' => ($sort === $col ? ($dir === 'desc' ? 'asc' : 'desc') : $first), 'page' => null]);
        $arrow = fn ($col) => $sort === $col ? ($dir === 'asc' ? ' ↑' : ' ↓') : '';
    @endphp
    <section class="panel panel-flush">
        <form class="filters" method="get" action="{{ route('customers.index') }}">
            <div class="field grow">
                <label for="f-q">Search</label>
                <input id="f-q" class="form-control" type="search" name="q" value="{{ request('q') }}" placeholder="Name, email or phone">
            </div>
            <button class="btn btn-primary">Search</button>
            @if (request('q'))<a class="btn btn-ghost" href="{{ route('customers.index') }}">Clear</a>@endif
        </form>
        <div class="tbl-wrap">
            <table class="tbl">
                <thead>
                    <tr>
                        <th class="{{ $sort === 'name' ? 'sorted' : '' }}"><a href="{{ $sortUrl('name', 'asc') }}">Customer{{ $arrow('name') }}</a></th>
                        <th>Phone</th>
                        <th class="num {{ $sort === 'orders_count' ? 'sorted' : '' }}"><a href="{{ $sortUrl('orders_count') }}">Orders{{ $arrow('orders_count') }}</a></th>
                        <th class="num {{ $sort === 'spent' ? 'sorted' : '' }}"><a href="{{ $sortUrl('spent') }}">Spent{{ $arrow('spent') }}</a></th>
                        <th>Last order</th>
                        <th class="{{ $sort === 'created_at' ? 'sorted' : '' }}"><a href="{{ $sortUrl('created_at') }}">Joined{{ $arrow('created_at') }}</a></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($customers as $c)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <span class="avatar" style="background:var(--ink-2)">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($c->name, 0, 1)) }}</span>
                                    <div>
                                        <a class="row-link" href="{{ route('customers.show', $c) }}">{{ $c->name }}</a>
                                        <div class="small-muted">{{ $c->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="muted">{{ $c->phone_number ?: '-' }}</td>
                            <td class="num">{{ $c->orders_count }}</td>
                            <td class="num fw-semibold">${{ number_format($c->spent, 2) }}</td>
                            <td class="muted">{{ $c->last_order_at ? \Illuminate\Support\Carbon::parse($c->last_order_at)->diffForHumans() : 'Never' }}</td>
                            <td class="muted">{{ $c->created_at?->format('M j, Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="empty"><h3>No customers found</h3><p class="mb-0">Customers appear here when they register in the storefront.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($customers->hasPages())
            <div class="pager"><span class="small-muted">Showing {{ $customers->firstItem() }}-{{ $customers->lastItem() }} of {{ $customers->total() }}</span>{{ $customers->links('pagination::bootstrap-5') }}</div>
        @endif
    </section>
@endsection
