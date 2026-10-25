@extends('layouts.dashboard')

@section('title', 'Orders')
@section('breadcrump')
    @parent
    <li>Orders</li>
@endsection
@section('subtitle')
    {{ number_format($orders->total()) }} {{ str('order')->plural($orders->total()) }}{{ request()->hasAny(['q','status','payment_status','store_id','from','to']) ? ' match your filters' : ' in total' }}.
@endsection
@section('actions')
    <a href="{{ route('orders.export', request()->query()) }}" class="btn btn-light"><i class="fas fa-file-download me-2"></i>Export CSV</a>
@endsection

@section('content')
    @php
        $sortUrl = fn ($col) => request()->fullUrlWithQuery(['sort' => $col, 'dir' => ($sort === $col && $dir === 'desc') ? 'asc' : 'desc', 'page' => null]);
        $arrow = fn ($col) => $sort === $col ? ($dir === 'asc' ? ' ↑' : ' ↓') : '';
        $tabs = ['' => 'All'] + collect(\App\Models\Order::STATUSES)->mapWithKeys(fn ($s) => [$s => ucfirst($s)])->all();
    @endphp

    <section class="panel panel-flush">
        <div class="px-3 pt-2 border-bottom" style="display:flex;gap:4px;flex-wrap:wrap">
            @foreach ($tabs as $value => $label)
                @php $n = $value === '' ? $counts->sum() : ($counts[$value] ?? 0); $on = (string) request('status') === (string) $value; @endphp
                <a href="{{ request()->fullUrlWithQuery(['status' => $value ?: null, 'page' => null]) }}"
                    class="px-3 py-2 {{ $on ? 'fw-bold' : 'muted' }}"
                    style="border-bottom:2px solid {{ $on ? 'var(--brand)' : 'transparent' }};color:{{ $on ? 'var(--text)' : '' }}">
                    {{ $label }} <span class="small-muted">{{ $n }}</span>
                </a>
            @endforeach
        </div>

        <form class="filters" method="get" action="{{ route('orders.index') }}">
            @if (request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
            <div class="field grow">
                <label for="f-q">Search</label>
                <input id="f-q" class="form-control" type="search" name="q" value="{{ request('q') }}" placeholder="Order number, customer name or email">
            </div>
            <div class="field">
                <label for="f-store">Store</label>
                <select id="f-store" class="form-select" name="store_id">
                    <option value="">All stores</option>
                    @foreach ($stores as $s)
                        <option value="{{ $s->id }}" @selected(request('store_id') == $s->id)>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="f-pay">Payment</label>
                <select id="f-pay" class="form-select" name="payment_status">
                    <option value="">Any</option>
                    @foreach (\App\Models\Order::PAYMENT_STATUSES as $p)
                        <option value="{{ $p }}" @selected(request('payment_status') == $p)>{{ ucfirst($p) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field"><label for="f-from">From</label><input id="f-from" class="form-control" type="date" name="from" value="{{ request('from') }}"></div>
            <div class="field"><label for="f-to">To</label><input id="f-to" class="form-control" type="date" name="to" value="{{ request('to') }}"></div>
            <button class="btn btn-primary">Apply</button>
            @if (request()->hasAny(['q','store_id','payment_status','from','to']))
                <a class="btn btn-ghost" href="{{ route('orders.index', array_filter(['status' => request('status')])) }}">Clear</a>
            @endif
        </form>

        <form id="bulkForm" action="{{ route('orders.bulk') }}" method="post" data-confirm="Change the status of the selected orders? Cancelling or refunding returns their items to stock." data-confirm-label="Update orders">@csrf</form>
        <div class="px-3 py-2 border-bottom" data-bulk-bar="orders" hidden style="background:var(--brand-tint)">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <strong data-bulk-count></strong>
                <select class="form-select form-select-sm" name="status" form="bulkForm" style="width:auto" aria-label="New status">
                    @foreach (\App\Models\Order::STATUSES as $s)<option value="{{ $s }}">Mark as {{ $s }}</option>@endforeach
                </select>
                <button class="btn btn-sm btn-primary" form="bulkForm">Update</button>
            </div>
        </div>

        <div class="tbl-wrap">
            <table class="tbl">
                <thead>
                    <tr>
                        <th style="width:40px"><input class="form-check-input" type="checkbox" data-check-all="orders" aria-label="Select all on this page"></th>
                        <th class="{{ $sort === 'number' ? 'sorted' : '' }}"><a href="{{ $sortUrl('number') }}">Order{{ $arrow('number') }}</a></th>
                        <th>Customer</th>
                        <th>Store</th>
                        <th class="{{ $sort === 'status' ? 'sorted' : '' }}"><a href="{{ $sortUrl('status') }}">Status{{ $arrow('status') }}</a></th>
                        <th>Payment</th>
                        <th class="num {{ $sort === 'total' ? 'sorted' : '' }}"><a href="{{ $sortUrl('total') }}">Total{{ $arrow('total') }}</a></th>
                        <th class="{{ $sort === 'created_at' ? 'sorted' : '' }}"><a href="{{ $sortUrl('created_at') }}">Placed{{ $arrow('created_at') }}</a></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        @php
                            $bill = $order->billingAddresses;
                            $guest = $order->user->name === 'Guest Customer';
                            $name = $guest ? trim(($bill->first_name ?? '') . ' ' . ($bill->last_name ?? '')) ?: 'Guest' : $order->user->name;
                            $email = $guest ? ($bill->email ?? '') : $order->user->email;
                        @endphp
                        <tr>
                            <td><input class="form-check-input" type="checkbox" name="ids[]" value="{{ $order->id }}" form="bulkForm" data-check="orders" aria-label="Select order {{ $order->number }}"></td>
                            <td><a class="row-link" href="{{ route('orders.show', $order) }}">#{{ $order->number }}</a></td>
                            <td>
                                <div>{{ $name }}</div>
                                <div class="small-muted">{{ $email }}</div>
                            </td>
                            <td>@if ($order->store)<a class="store-chip" style="--c: {{ $order->store->color }}" href="{{ route('stores.show', $order->store) }}"><i></i>{{ $order->store->name }}</a>@else<span class="muted">-</span>@endif</td>
                            <td><span class="status status-{{ $order->status }}">{{ ucfirst($order->status) }}</span></td>
                            <td>
                                <span class="status status-{{ $order->payment_status }}">{{ ucfirst($order->payment_status) }}</span>
                                <div class="small-muted mt-1">{{ str_replace('_', ' ', ucfirst($order->payment_method)) }}</div>
                            </td>
                            <td class="num fw-semibold">${{ number_format($order->total, 2) }}</td>
                            <td class="muted" title="{{ $order->created_at }}">{{ $order->created_at->format('M j, Y') }}<div class="small-muted">{{ $order->created_at->format('H:i') }}</div></td>
                        </tr>
                    @empty
                        <tr><td colspan="8">
                            <div class="empty">
                                <h3>No orders found</h3>
                                <p class="mb-0">Try a different status or clear the filters.</p>
                            </div>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($orders->hasPages())
            <div class="pager">
                <span class="small-muted">Showing {{ $orders->firstItem() }}-{{ $orders->lastItem() }} of {{ $orders->total() }}</span>
                {{ $orders->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </section>
@endsection
