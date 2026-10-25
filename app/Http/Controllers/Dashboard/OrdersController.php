<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrdersController extends Controller
{
    private const SORTS = ['created_at', 'number', 'total', 'status'];

    public function index(Request $request)
    {
        $filters = $request->only(['q', 'status', 'payment_status', 'store_id', 'from', 'to']);

        $query = Order::withTotal()->with(['store', 'user', 'billingAddresses'])->filter($filters);

        $sort = in_array($request->query('sort'), self::SORTS, true) ? $request->query('sort') : 'created_at';
        $dir = $request->query('dir') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sort === 'total' ? 'total' : "orders.{$sort}", $dir)->orderByDesc('orders.id');

        $orders = $query->paginate(15)->withQueryString();

        // Tab counts respect every filter except the status itself.
        $counts = Order::filter(collect($filters)->except('status')->all())
            ->select('orders.status', DB::raw('COUNT(*) as n'))->groupBy('orders.status')->pluck('n', 'status');

        return view('dashboard.orders.index', [
            'orders' => $orders,
            'counts' => $counts,
            'stores' => Store::orderBy('name')->get(),
            'sort' => $sort,
            'dir' => $dir,
        ]);
    }

    public function show(Order $order)
    {
        $order->load(['store', 'user', 'items.products', 'billingAddresses', 'shippingAddresses']);
        $order->total = $order->items->sum(fn ($i) => $i->price * $i->quantity);

        return view('dashboard.orders.show', [
            'order' => $order,
            'customerOrders' => $order->user_id ? Order::where('user_id', $order->user_id)->count() : 0,
        ]);
    }

    public function update(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(Order::STATUSES)],
            'payment_status' => ['required', Rule::in(Order::PAYMENT_STATUSES)],
        ]);

        $this->apply($order, $data['status'], $data['payment_status']);

        return back()->with('success', "Order #{$order->number} updated.");
    }

    public function bulk(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:orders,id'],
            'status' => ['required', Rule::in(Order::STATUSES)],
        ]);

        $orders = Order::whereIn('id', $data['ids'])->get();
        foreach ($orders as $order) {
            $this->apply($order, $data['status'], $order->payment_status);
        }

        return back()->with('success', $orders->count() . ' ' . str('order')->plural($orders->count()) . " marked as {$data['status']}.");
    }

    public function export(Request $request): StreamedResponse
    {
        $orders = Order::withTotal()->with(['store', 'user', 'billingAddresses'])
            ->filter($request->only(['q', 'status', 'payment_status', 'store_id', 'from', 'to']))
            ->orderByDesc('orders.created_at')->get();

        return response()->streamDownload(function () use ($orders) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Order', 'Placed', 'Store', 'Customer', 'Email', 'Status', 'Payment status', 'Payment method', 'Total']);
            foreach ($orders as $o) {
                $bill = $o->billingAddresses;
                fputcsv($out, [
                    $o->number, $o->created_at->toDateTimeString(), $o->store?->name,
                    $o->user->name !== 'Guest Customer' ? $o->user->name : trim(($bill->first_name ?? '') . ' ' . ($bill->last_name ?? '')),
                    $o->user->email ?? ($bill->email ?? ''), $o->status, $o->payment_status, $o->payment_method,
                    number_format((float) $o->total, 2, '.', ''),
                ]);
            }
            fclose($out);
        }, 'orders-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv']);
    }

    /**
     * Change an order's status. Cancelling or refunding returns the items to stock;
     * re-opening such an order takes them out again.
     */
    private function apply(Order $order, string $status, string $paymentStatus): void
    {
        $closed = ['cancelled', 'refunded'];
        $wasClosed = in_array($order->status, $closed, true);
        $nowClosed = in_array($status, $closed, true);

        DB::transaction(function () use ($order, $status, $paymentStatus, $wasClosed, $nowClosed) {
            if ($wasClosed !== $nowClosed) {
                foreach ($order->items as $item) {
                    $product = Product::withoutGlobalScopes()->find($item->product_id);
                    if (! $product) {
                        continue;
                    }
                    $nowClosed
                        ? $product->increment('quantity', $item->quantity)
                        : $product->update(['quantity' => max(0, $product->quantity - $item->quantity)]);
                }
            }
            $order->update(['status' => $status, 'payment_status' => $paymentStatus]);
        });
    }
}
