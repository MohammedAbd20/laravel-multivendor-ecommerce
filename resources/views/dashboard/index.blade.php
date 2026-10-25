@extends('layouts.dashboard')

@section('title', 'Dashboard')
@section('breadcrump')
@endsection
@section('subtitle')
    How the marketplace performed over the last {{ $days }} days, compared with the {{ $days }} days before.
@endsection
@section('actions')
    <a href="{{ route('orders.index') }}" class="btn btn-light"><i class="fas fa-receipt me-2"></i>All orders</a>
    <a href="{{ route('products.create') }}" class="btn btn-primary"><i class="fas fa-plus me-2"></i>New product</a>
@endsection

@section('content')

    {{-- KPI band --}}
    <section class="kpis" aria-label="Key figures">
        @foreach ($kpis as $kpi)
            <div class="kpi">
                <div class="kpi-label">{{ $kpi['label'] }}</div>
                <div class="kpi-value">
                    @if (! empty($kpi['money']))<small>$</small>{{ number_format($kpi['value'], $kpi['label'] === 'Revenue' ? 0 : 2) }}@else{{ number_format($kpi['value']) }}@endif
                </div>
                <div class="kpi-delta">
                    @if ($kpi['delta'] === null)
                        <b class="up">New</b> no sales in the previous period
                    @elseif ($kpi['delta'] > 0)
                        <b class="up"><i class="fas fa-arrow-up"></i> {{ $kpi['delta'] }}%</b> vs previous {{ $days }} days
                    @elseif ($kpi['delta'] < 0)
                        <b class="down"><i class="fas fa-arrow-down"></i> {{ abs($kpi['delta']) }}%</b> vs previous {{ $days }} days
                    @else
                        <b>No change</b> vs previous {{ $days }} days
                    @endif
                </div>
            </div>
        @endforeach
    </section>

    <div class="row g-3">
        <div class="col-xl-8">
            <section class="panel">
                <div class="panel-head">
                    <h2>Sales by store <span class="small-muted">daily revenue, last {{ $days }} days</span></h2>
                </div>
                @if (count($chart['series']))
                    <div class="chart-wrap" id="salesChart" aria-label="Daily revenue by store"></div>
                    <div class="legend">
                        @foreach ($chart['series'] as $s)
                            <span class="store-chip" style="--c: {{ $s['color'] }}"><i></i>{{ $s['name'] }}</span>
                        @endforeach
                    </div>
                @else
                    <div class="empty">
                        <h3>No sales in this period</h3>
                        <p class="mb-0">Revenue will appear here as soon as orders come in.</p>
                    </div>
                @endif
            </section>
            <section class="panel panel-flush">
                <div class="panel-head">
                    <h2>Latest orders</h2>
                    <a href="{{ route('orders.index') }}" class="fw-semibold">View all</a>
                </div>
                <div class="tbl-wrap">
                    <table class="tbl">
                        <thead>
                            <tr>
                                <th>Order</th><th>Customer</th><th>Store</th><th>Status</th><th class="num">Total</th><th>Placed</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentOrders as $order)
                                <tr>
                                    <td><a class="row-link" href="{{ route('orders.show', $order) }}">#{{ $order->number }}</a></td>
                                    <td>{{ $order->customer_name }}</td>
                                    <td>@if ($order->store)<span class="store-chip" style="--c: {{ $order->store->color }}"><i></i>{{ $order->store->name }}</span>@else<span class="muted">-</span>@endif</td>
                                    <td><span class="status status-{{ $order->status }}">{{ ucfirst($order->status) }}</span></td>
                                    <td class="num">${{ number_format($order->total, 2) }}</td>
                                    <td class="muted" style="white-space:nowrap" title="{{ $order->created_at }}">{{ $order->created_at->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6"><div class="empty"><h3>No orders yet</h3><p class="mb-0">Orders placed in the storefront show up here.</p></div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <div class="col-xl-4">
            <section class="panel panel-flush">
                <div class="panel-head"><h2>Needs attention</h2></div>
                <ul class="attn">
                    @foreach ($attention as $a)
                        <li>
                            <span class="attn-ico {{ $a['tone'] }}"><i class="{{ $a['icon'] }}"></i></span>
                            <div>
                                <div class="fw-semibold">{{ $a['text'] }}</div>
                                <div class="small-muted">{{ $a['hint'] }}</div>
                            </div>
                            @if ($a['url'])
                                <a class="stretch" href="{{ $a['url'] }}">{{ $a['cta'] }}</a>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </section>
            <section class="panel panel-flush">
                <div class="panel-head"><h2>Top stores</h2><span class="small-muted">by revenue</span></div>
                <ul class="rank">
                    @forelse ($topStores as $row)
                        <li>
                            <a class="store-chip" style="--c: {{ $row->store->color }}" href="{{ route('stores.show', $row->store) }}"><i></i>{{ $row->store->name }}</a>
                            <span class="num fw-semibold">${{ number_format($row->revenue, 0) }}</span>
                            <div class="rank-bar" style="--c: {{ $row->store->color }}"><span style="width: {{ $row->share }}%"></span></div>
                        </li>
                    @empty
                        <li><span class="muted">No sales yet.</span></li>
                    @endforelse
                </ul>
            </section>

            <section class="panel panel-flush">
                <div class="panel-head"><h2>Best sellers</h2><span class="small-muted">last {{ $days }} days</span></div>
                <ul class="rank">
                    @forelse ($topProducts as $row)
                        <li>
                            <div>
                                <div class="fw-semibold">{{ $row->name }}</div>
                                <div class="small-muted">
                                    @if ($row->store)<span class="store-chip" style="--c: {{ $row->store->color }}"><i></i>{{ $row->store->name }}</span> · @endif{{ number_format($row->units) }} sold
                                </div>
                            </div>
                            <span class="num fw-semibold">${{ number_format($row->revenue, 0) }}</span>
                        </li>
                    @empty
                        <li><span class="muted">No sales yet.</span></li>
                    @endforelse
                </ul>
            </section>
        </div>
    </div>
@endsection

@push('script')
    <script>
        (function () {
            var host = document.getElementById('salesChart');
            if (!host) return;
            var data = @json($chart);
            var W = 760, H = 270, L = 46, R = 8, T = 10, B = 28;
            var n = data.days.length;
            var totals = data.days.map(function (_, i) {
                return data.series.reduce(function (s, x) { return s + x.values[i]; }, 0);
            });
            var max = Math.max.apply(null, totals) || 1;
            var step = Math.pow(10, Math.floor(Math.log10(max)));
            var top = Math.ceil(max / step) * step;
            if (top / step < 3) { step = step / 2; top = Math.ceil(max / step) * step; }
            var ticks = []; for (var v = 0; v <= top; v += step) ticks.push(v);
            var plotW = W - L - R, plotH = H - T - B, slot = plotW / n, bw = Math.max(4, slot * 0.66);
            var y = function (v) { return T + plotH - (v / top) * plotH; };
            var fmt = function (v) { return '$' + Math.round(v).toLocaleString('en-US'); };
            var NS = 'http://www.w3.org/2000/svg';
            var svg = '<svg viewBox="0 0 ' + W + ' ' + H + '" role="img"><g class="grid">';
            ticks.forEach(function (t) {
                svg += '<line x1="' + L + '" x2="' + (W - R) + '" y1="' + y(t) + '" y2="' + y(t) + '"/>' +
                    '<text x="' + (L - 8) + '" y="' + (y(t) + 4) + '" text-anchor="end">' + (t >= 1000 ? (t / 1000) + 'k' : t) + '</text>';
            });
            svg += '</g>';
            data.days.forEach(function (day, i) {
                var x = L + i * slot + (slot - bw) / 2, acc = 0;
                svg += '<g class="col" data-i="' + i + '"><rect x="' + (L + i * slot) + '" y="' + T + '" width="' + slot + '" height="' + plotH + '" fill="transparent"/>';
                data.series.forEach(function (s) {
                    var v = s.values[i]; if (!v) return;
                    var h = (v / top) * plotH, yy = y(acc + v);
                    svg += '<rect class="bar" x="' + x + '" y="' + yy + '" width="' + bw + '" height="' + Math.max(1, h - 1) + '" rx="1.5" fill="' + s.color + '"/>';
                    acc += v;
                });
                svg += '</g>';
                if (i % 5 === 0 || i === n - 1) {
                    var d = new Date(day + 'T00:00:00');
                    svg += '<text x="' + (L + i * slot + slot / 2) + '" y="' + (H - 8) + '" text-anchor="middle">' +
                        d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) + '</text>';
                }
            });
            svg += '</svg>';
            host.innerHTML = svg + '<div class="chart-tip" id="chartTip"></div>';

            var tip = document.getElementById('chartTip');
            host.querySelectorAll('.col').forEach(function (g) {
                g.addEventListener('mousemove', function (e) {
                    var i = +g.getAttribute('data-i');
                    var d = new Date(data.days[i] + 'T00:00:00');
                    var html = '<b>' + d.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' }) + '</b>';
                    data.series.forEach(function (s) {
                        if (s.values[i]) html += '<div><span style="color:' + s.color + '">&#9632;</span> ' + s.name + '<span>' + fmt(s.values[i]) + '</span></div>';
                    });
                    html += '<div style="border-top:1px solid #3a4b66;margin-top:4px;padding-top:4px"><b style="margin:0">Total</b><b style="margin:0">' + fmt(totals[i]) + '</b></div>';
                    tip.innerHTML = totals[i] ? html : '<b>' + d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) + '</b>No sales';
                    var r = host.getBoundingClientRect();
                    var left = e.clientX - r.left + 14;
                    if (left + 170 > r.width) left = e.clientX - r.left - 170;
                    tip.style.display = 'block'; tip.style.left = left + 'px'; tip.style.top = (e.clientY - r.top - 20) + 'px';
                });
                g.addEventListener('mouseleave', function () { tip.style.display = 'none'; });
            });
        })();
    </script>
@endpush
