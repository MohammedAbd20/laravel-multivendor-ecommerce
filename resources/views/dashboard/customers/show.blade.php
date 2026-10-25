@extends('layouts.dashboard')
@section('title', $customer->name)
@section('breadcrump')
    @parent
    <li><a href="{{ route('customers.index') }}">Customers</a></li>
    <li>{{ $customer->name }}</li>
@endsection
@section('actions')
    <a href="{{ route('customers.index') }}" class="btn btn-light"><i class="fas fa-arrow-left me-2"></i>All customers</a>
@endsection

@section('content')
    <div class="row g-3">
        <div class="col-xl-4">
            <section class="panel">
                <div class="panel-head"><h2>Details</h2></div>
                <div class="panel-body">
                    <dl class="dl">
                        <dt>Email</dt>
                        <dd>{{ $customer->email }} @if ($customer->email_verified_at)<span class="status status-active ms-1">Verified</span>@else<span class="status status-pending ms-1">Unverified</span>@endif</dd>
                        <dt>Phone</dt><dd>{{ $customer->phone_number ?: '-' }}</dd>
                        <dt>Joined</dt><dd>{{ $customer->created_at?->format('M j, Y') }} <span class="small-muted">({{ $customer->created_at?->diffForHumans() }})</span></dd>
                        <dt>Two-factor</dt><dd>{{ $customer->two_factor_secret ? 'Enabled' : 'Off' }}</dd>
                    </dl>
                </div>
            </section>
            <section class="panel">
                <div class="panel-body stat-line">
                    <div><b>${{ number_format($spent, 2) }}</b><span>Total spent</span></div>
                    <div><b>{{ $orders->count() }}</b><span>Orders</span></div>
                    <div><b>${{ number_format($average, 2) }}</b><span>Average order</span></div>
                </div>
            </section>
        </div>
        <div class="col-xl-8">
            <section class="panel panel-flush">
                <div class="panel-head"><h2>Orders</h2></div>
                <div class="tbl-wrap">
                    <table class="tbl">
                        <thead><tr><th>Order</th><th>Store</th><th>Status</th><th class="num">Total</th><th>Placed</th></tr></thead>
                        <tbody>
                            @forelse ($orders as $o)
                                <tr>
                                    <td><a class="row-link" href="{{ route('orders.show', $o) }}">#{{ $o->number }}</a></td>
                                    <td>@if ($o->store)<span class="store-chip" style="--c: {{ $o->store->color }}"><i></i>{{ $o->store->name }}</span>@endif</td>
                                    <td><span class="status status-{{ $o->status }}">{{ ucfirst($o->status) }}</span></td>
                                    <td class="num">${{ number_format($o->total, 2) }}</td>
                                    <td class="muted">{{ $o->created_at->format('M j, Y') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5"><div class="empty"><h3>No orders yet</h3></div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
@endsection
