<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Http\Request;

class CustomersController extends Controller
{
    private const SORTS = ['name', 'orders_count', 'spent', 'created_at'];

    public function index(Request $request)
    {
        $sort = in_array($request->query('sort'), self::SORTS, true) ? $request->query('sort') : 'created_at';
        $dir = $request->query('dir') === 'asc' ? 'asc' : 'desc';

        $customers = User::query()
            ->when($request->query('q'), fn ($q, $v) => $q->where(fn ($q) => $q->where('name', 'LIKE', "%{$v}%")
                ->orWhere('email', 'LIKE', "%{$v}%")->orWhere('phone_number', 'LIKE', "%{$v}%")))
            ->withCount('orders')
            ->addSelect(['spent' => OrderItem::query()
                ->selectRaw('COALESCE(SUM(order_items.price * order_items.quantity), 0)')
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->whereColumn('orders.user_id', 'users.id')
                ->whereNotIn('orders.status', ['cancelled', 'refunded']),
                'last_order_at' => Order::query()->select('created_at')->whereColumn('orders.user_id', 'users.id')->latest()->limit(1),
            ])
            ->orderBy($sort, $dir)->orderByDesc('id')
            ->paginate(15)->withQueryString();

        return view('dashboard.customers.index', ['customers' => $customers, 'sort' => $sort, 'dir' => $dir]);
    }

    public function show(User $customer)
    {
        $orders = Order::withTotal()->with('store')->where('orders.user_id', $customer->id)->latest('orders.created_at')->get();
        $paid = $orders->reject(fn ($o) => in_array($o->status, ['cancelled', 'refunded']));

        return view('dashboard.customers.show', [
            'customer' => $customer->load('profile'),
            'orders' => $orders,
            'spent' => (float) $paid->sum('total'),
            'average' => $paid->count() ? (float) $paid->sum('total') / $paid->count() : 0,
        ]);
    }
}
