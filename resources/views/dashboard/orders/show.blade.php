@extends('layouts.dashboard')

@section('title', 'Order #' . $order->number)
@section('breadcrump')
    @parent
    <li><a href="{{ route('orders.index') }}">Orders</a></li>
    <li>#{{ $order->number }}</li>
@endsection
@section('subtitle')
    Placed {{ $order->created_at->format('M j, Y \a\t H:i') }} ({{ $order->created_at->diffForHumans() }})
@endsection
@section('actions')
    <a href="{{ route('orders.index') }}" class="btn btn-light"><i class="fas fa-arrow-left me-2"></i>Back to orders</a>
@endsection

@section('content')
    @php
        $bill = $order->billingAddresses; $ship = $order->shippingAddresses;
        $guest = $order->user->name === 'Guest Customer';
        $name = $guest ? trim(($bill->first_name ?? '') . ' ' . ($bill->last_name ?? '')) ?: 'Guest' : $order->user->name;
        $countries = \Symfony\Component\Intl\Countries::getNames();
        $fmtAddr = fn ($a) => $a ? collect([$a->street_address, $a->city, $a->state, $a->postal_code, $countries[$a->country] ?? $a->country])->filter()->implode(', ') : '-';
    @endphp

    <div class="row g-3">
        <div class="col-xl-8">
            <section class="panel panel-flush">
                <div class="panel-head"><h2>Items <span class="small-muted">{{ $order->items->count() }} {{ str('product')->plural($order->items->count()) }}</span></h2></div>
                <div class="tbl-wrap">
                    <table class="tbl">
                        <thead><tr><th>Product</th><th class="num">Price</th><th class="num">Qty</th><th class="num">Subtotal</th></tr></thead>
                        <tbody>
                            @foreach ($order->items as $item)
                                <tr>
                                    <td>
                                        @if ($item->product_id && \App\Models\Product::withoutGlobalScopes()->whereKey($item->product_id)->exists())
                                            <a class="row-link" href="{{ route('products.edit', $item->product_id) }}">{{ $item->product_name }}</a>
                                        @else
                                            {{ $item->product_name }} <span class="small-muted">(removed)</span>
                                        @endif
                                    </td>
                                    <td class="num">${{ number_format($item->price, 2) }}</td>
                                    <td class="num">{{ $item->quantity }}</td>
                                    <td class="num fw-semibold">${{ number_format($item->price * $item->quantity, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr><td colspan="3" class="num" style="padding:14px 16px;font-weight:650">Order total</td><td class="num" style="padding:14px 16px;font-size:1.15rem;font-weight:700">${{ number_format($order->total, 2) }}</td></tr>
                        </tfoot>
                    </table>
                </div>
            </section>

            <div class="row g-3">
                <div class="col-md-6">
                    <section class="panel">
                        <div class="panel-head"><h2>Billing address</h2></div>
                        <div class="panel-body">
                            @if ($bill)
                                <div class="fw-semibold">{{ $bill->first_name }} {{ $bill->last_name }}</div>
                                <div>{{ $fmtAddr($bill) }}</div>
                                <div class="mt-2"><i class="far fa-envelope me-2 muted"></i>{{ $bill->email }}</div>
                                <div><i class="fas fa-phone me-2 muted"></i>{{ $bill->phone_number }}</div>
                            @else <span class="muted">Not provided.</span> @endif
                        </div>
                    </section>
                </div>
                <div class="col-md-6">
                    <section class="panel">
                        <div class="panel-head"><h2>Shipping address</h2></div>
                        <div class="panel-body">
                            @if ($ship)
                                <div class="fw-semibold">{{ $ship->first_name }} {{ $ship->last_name }}</div>
                                <div>{{ $fmtAddr($ship) }}</div>
                                <div class="mt-2"><i class="far fa-envelope me-2 muted"></i>{{ $ship->email }}</div>
                                <div><i class="fas fa-phone me-2 muted"></i>{{ $ship->phone_number }}</div>
                            @else <span class="muted">Not provided.</span> @endif
                        </div>
                    </section>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <section class="panel">
                <div class="panel-head"><h2>Fulfilment</h2></div>
                <form action="{{ route('orders.update', $order) }}" method="post"
                    @if (! in_array($order->status, ['cancelled', 'refunded'])) data-confirm="Cancelling or refunding returns the items to stock. Continue?" data-confirm-label="Save changes" data-confirm-only="cancelled,refunded" @endif>
                    @csrf @method('PUT')
                    <div class="panel-body">
                        <div class="mb-3">
                            <label class="form-label" for="status">Order status</label>
                            <select id="status" name="status" class="form-select">
                                @foreach (\App\Models\Order::STATUSES as $s)
                                    <option value="{{ $s }}" @selected(old('status', $order->status) === $s)>{{ ucfirst($s) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-1">
                            <label class="form-label" for="payment_status">Payment status</label>
                            <select id="payment_status" name="payment_status" class="form-select">
                                @foreach (\App\Models\Order::PAYMENT_STATUSES as $s)
                                    <option value="{{ $s }}" @selected(old('payment_status', $order->payment_status) === $s)>{{ ucfirst($s) }}</option>
                                @endforeach
                            </select>
                            <div class="help">Method: {{ str_replace('_', ' ', $order->payment_method) }}</div>
                        </div>
                    </div>
                    <div class="form-actions"><button class="btn btn-primary" type="submit">Save changes</button></div>
                </form>
            </section>

            <section class="panel">
                <div class="panel-head"><h2>Customer</h2></div>
                <div class="panel-body">
                    <div class="fw-semibold">{{ $name }}</div>
                    @if (! $guest)
                        <div class="muted">{{ $order->user->email }}</div>
                        <div class="mt-2 small-muted">{{ $customerOrders }} {{ str('order')->plural($customerOrders) }} in total</div>
                        <a class="d-inline-block mt-2 fw-semibold" href="{{ route('customers.show', $order->user_id) }}">View customer</a>
                    @else
                        <div class="small-muted">Guest checkout</div>
                    @endif
                </div>
            </section>

            <section class="panel">
                <div class="panel-head"><h2>Store</h2></div>
                <div class="panel-body">
                    @if ($order->store)
                        <a class="store-chip" style="--c: {{ $order->store->color }}" href="{{ route('stores.show', $order->store) }}"><i></i>{{ $order->store->name }}</a>
                    @else <span class="muted">No store recorded.</span> @endif
                </div>
            </section>
        </div>
    </div>
@endsection
