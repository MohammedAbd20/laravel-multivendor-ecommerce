<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    private const DAYS = 30;
    private const LOW_STOCK = 5;

    public function index()
    {
        $end = Carbon::now()->endOfDay();
        $start = Carbon::now()->subDays(self::DAYS - 1)->startOfDay();
        $prevEnd = $start->copy()->subSecond();
        $prevStart = $start->copy()->subDays(self::DAYS);

        $current = $this->periodStats($start, $end);
        $previous = $this->periodStats($prevStart, $prevEnd);

        $kpis = [
            'revenue' => ['label' => 'Revenue', 'value' => $current['revenue'], 'delta' => $this->delta($current['revenue'], $previous['revenue']), 'money' => true],
            'orders' => ['label' => 'Orders', 'value' => $current['orders'], 'delta' => $this->delta($current['orders'], $previous['orders'])],
            'aov' => ['label' => 'Average order', 'value' => $current['aov'], 'delta' => $this->delta($current['aov'], $previous['aov']), 'money' => true],
            'customers' => ['label' => 'New customers', 'value' => $current['customers'], 'delta' => $this->delta($current['customers'], $previous['customers'])],
        ];

        return view('dashboard.index', [
            'days' => self::DAYS,
            'kpis' => $kpis,
            'chart' => $this->salesChart($start),
            'attention' => $this->attention(),
            'recentOrders' => Order::withTotal()->with(['store', 'user', 'billingAddresses'])->latest('orders.created_at')->limit(8)->get(),
            'topStores' => $this->topStores($start, $end),
            'topProducts' => $this->topProducts($start, $end),
            'totals' => [
                'stores' => Store::count(),
                'products' => Product::withoutGlobalScopes()->count(),
                'customers' => User::count(),
            ],
        ]);
    }

    /** Revenue ignores cancelled and refunded orders. */
    private function periodStats(Carbon $from, Carbon $to): array
    {
        $row = DB::table('orders')
            ->join('order_items', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$from, $to])
            ->whereNotIn('orders.status', ['cancelled', 'refunded'])
            ->selectRaw('COALESCE(SUM(order_items.price * order_items.quantity), 0) as revenue, COUNT(DISTINCT orders.id) as orders')
            ->first();

        $orders = (int) $row->orders;
        $revenue = (float) $row->revenue;

        return [
            'revenue' => $revenue,
            'orders' => $orders,
            'aov' => $orders ? $revenue / $orders : 0,
            'customers' => User::whereBetween('created_at', [$from, $to])->count(),
        ];
    }

    private function delta(float $now, float $before): ?float
    {
        if ($before <= 0) {
            return $now > 0 ? null : 0.0;
        }

        return round((($now - $before) / $before) * 100, 1);
    }

    private function salesChart(Carbon $start): array
    {
        $rows = DB::table('orders')
            ->join('order_items', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.created_at', '>=', $start)
            ->whereNotIn('orders.status', ['cancelled', 'refunded'])
            ->groupBy(DB::raw('DATE(orders.created_at)'), 'orders.store_id')
            ->selectRaw('DATE(orders.created_at) as day, orders.store_id, SUM(order_items.price * order_items.quantity) as revenue')
            ->get();

        $days = collect(range(0, self::DAYS - 1))->map(fn ($i) => $start->copy()->addDays($i)->toDateString());
        $stores = Store::orderBy('id')->get();

        $series = $stores->map(function ($store) use ($rows, $days) {
            return [
                'id' => $store->id,
                'name' => $store->name,
                'color' => $store->color,
                'values' => $days->map(fn ($d) => round((float) $rows->where('store_id', $store->id)->where('day', $d)->sum('revenue'), 2))->all(),
            ];
        })->filter(fn ($s) => array_sum($s['values']) > 0)->values();

        return ['days' => $days->all(), 'series' => $series->all()];
    }

    private function attention(): array
    {
        $items = [];

        $pending = Order::where('status', 'pending')->count();
        if ($pending) {
            $oldest = Order::where('status', 'pending')->min('created_at');
            $items[] = ['tone' => 'warn', 'icon' => 'fas fa-hourglass-half',
                'text' => "{$pending} " . str('order')->plural($pending) . ' waiting to be accepted',
                'hint' => 'Oldest: ' . Carbon::parse($oldest)->diffForHumans(),
                'url' => route('orders.index', ['status' => 'pending']), 'cta' => 'Review'];
        }

        $late = Order::where('status', 'processing')->where('created_at', '<', now()->subDays(3))->count();
        if ($late) {
            $items[] = ['tone' => 'danger', 'icon' => 'fas fa-exclamation-triangle',
                'text' => "{$late} " . str('order')->plural($late) . ' processing for more than 3 days',
                'hint' => 'May need a follow-up with the store',
                'url' => route('orders.index', ['status' => 'processing']), 'cta' => 'Open'];
        }

        $products = Product::withoutGlobalScopes()->where('status', 'active');
        $out = (clone $products)->where('quantity', '<=', 0)->count();
        $low = (clone $products)->where('quantity', '>', 0)->where('quantity', '<=', self::LOW_STOCK)->count();
        if ($out) {
            $items[] = ['tone' => 'danger', 'icon' => 'fas fa-box-open',
                'text' => "{$out} active " . str('product')->plural($out) . ' out of stock',
                'hint' => 'Customers can see them but cannot buy',
                'url' => route('products.index', ['stock' => 'out']), 'cta' => 'Restock'];
        }
        if ($low) {
            $items[] = ['tone' => 'warn', 'icon' => 'fas fa-battery-quarter',
                'text' => "{$low} " . str('product')->plural($low) . ' with ' . self::LOW_STOCK . ' or fewer left',
                'hint' => 'Low stock',
                'url' => route('products.index', ['stock' => 'low']), 'cta' => 'View'];
        }

        $inactive = Store::where('status', '!=', 'active')->count();
        if ($inactive) {
            $items[] = ['tone' => 'info', 'icon' => 'fas fa-store-slash',
                'text' => "{$inactive} " . str('store')->plural($inactive) . ' not active',
                'hint' => 'Their products are hidden from the storefront',
                'url' => route('stores.index', ['status' => 'inactive']), 'cta' => 'Manage'];
        }

        if (! $items) {
            $items[] = ['tone' => 'ok', 'icon' => 'fas fa-check', 'text' => 'Nothing needs your attention', 'hint' => 'Orders, stock and stores look healthy', 'url' => null, 'cta' => null];
        }

        return $items;
    }

    private function topStores(Carbon $from, Carbon $to)
    {
        $rows = DB::table('orders')
            ->join('order_items', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$from, $to])
            ->whereNotIn('orders.status', ['cancelled', 'refunded'])
            ->groupBy('orders.store_id')
            ->selectRaw('orders.store_id, SUM(order_items.price * order_items.quantity) as revenue, COUNT(DISTINCT orders.id) as orders')
            ->orderByDesc('revenue')->limit(5)->get();

        $stores = Store::whereIn('id', $rows->pluck('store_id'))->get()->keyBy('id');
        $max = max(1, (float) $rows->max('revenue'));

        return $rows->filter(fn ($r) => $stores->has($r->store_id))->map(fn ($r) => (object) [
            'store' => $stores[$r->store_id],
            'revenue' => (float) $r->revenue,
            'orders' => (int) $r->orders,
            'share' => round(((float) $r->revenue / $max) * 100),
        ])->values();
    }

    private function topProducts(Carbon $from, Carbon $to)
    {
        return DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->leftJoin('products', 'products.id', '=', 'order_items.product_id')
            ->whereBetween('orders.created_at', [$from, $to])
            ->whereNotIn('orders.status', ['cancelled', 'refunded'])
            ->groupBy('order_items.product_id', 'order_items.product_name', 'orders.store_id')
            ->selectRaw('order_items.product_id, order_items.product_name as name, orders.store_id, SUM(order_items.quantity) as units, SUM(order_items.price * order_items.quantity) as revenue')
            ->orderByDesc('revenue')->limit(5)->get()
            ->each(fn ($r) => $r->store = Store::find($r->store_id));
    }
}
