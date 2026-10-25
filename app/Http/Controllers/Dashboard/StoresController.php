<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoresController extends Controller
{
    public function index(Request $request)
    {
        $stores = Store::query()
            ->filter($request->only(['name', 'status']))
            ->withCount(['products' => fn ($q) => $q->withoutGlobalScopes(), 'orders'])
            ->addSelect(['revenue' => OrderItem::query()
                ->selectRaw('COALESCE(SUM(order_items.price * order_items.quantity), 0)')
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->whereColumn('orders.store_id', 'stores.id')
                ->whereNotIn('orders.status', ['cancelled', 'refunded']),
            ])
            ->orderBy('name')
            ->paginate(12)->withQueryString();

        return view('dashboard.stores.index', ['stores' => $stores]);
    }

    public function create()
    {
        return view('dashboard.stores.create', ['store' => new Store(['status' => 'active'])]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(Store::rules());
        $data = $this->prepare($request, $data);

        $store = Store::create($data);

        return redirect()->route('stores.show', $store)->with('success', "Store \"{$store->name}\" created.");
    }

    public function show(Store $store)
    {
        $revenueQuery = fn () => OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.store_id', $store->id)
            ->whereNotIn('orders.status', ['cancelled', 'refunded']);

        $revenue = (float) $revenueQuery()->sum(\DB::raw('order_items.price * order_items.quantity'));
        $orderCount = Order::where('store_id', $store->id)->whereNotIn('status', ['cancelled', 'refunded'])->count();
        $products = Product::withoutGlobalScopes()->where('store_id', $store->id);

        return view('dashboard.stores.show', [
            'store' => $store,
            'revenue' => $revenue,
            'orderCount' => $orderCount,
            'aov' => $orderCount ? $revenue / $orderCount : 0,
            'productCount' => (clone $products)->count(),
            'lowStock' => (clone $products)->where('status', 'active')->where('quantity', '<=', 5)->orderBy('quantity')->limit(6)->get(),
            'recentOrders' => Order::withTotal()->with('user')->where('orders.store_id', $store->id)->latest('orders.created_at')->limit(6)->get(),
        ]);
    }

    public function edit(Store $store)
    {
        return view('dashboard.stores.edit', ['store' => $store]);
    }

    public function update(Request $request, Store $store)
    {
        $data = $request->validate(Store::rules($store->id));
        $data = $this->prepare($request, $data, $store);

        $store->update($data);

        return redirect()->route('stores.show', $store)->with('success', 'Store updated.');
    }

    public function status(Request $request, Store $store)
    {
        $data = $request->validate(['status' => ['required', Rule::in(Store::STATUSES)]]);
        $store->update($data);

        return back()->with('success', "\"{$store->name}\" is now {$data['status']}.");
    }

    public function destroy(Store $store)
    {
        if ($store->orders()->exists()) {
            return back()->with('danger', 'This store has orders, so it cannot be deleted. Archive it instead.');
        }

        foreach (['logo_image', 'cover_image'] as $field) {
            $this->deleteFile($store->{$field});
        }
        $name = $store->name;
        $store->delete();

        return redirect()->route('stores.index')->with('success', "Store \"{$name}\" deleted.");
    }

    private function prepare(Request $request, array $data, ?Store $store = null): array
    {
        $data['slug'] = Str::slug($data['slug'] ?: $data['name']);
        if (! $store || $store->slug !== $data['slug']) {
            $base = $data['slug'];
            $i = 2;
            while (Store::where('slug', $data['slug'])->when($store, fn ($q) => $q->where('id', '!=', $store->id))->exists()) {
                $data['slug'] = "{$base}-{$i}";
                $i++;
            }
        }

        foreach (['logo_image', 'cover_image'] as $field) {
            unset($data[$field]);
            if ($request->hasFile($field)) {
                $data[$field] = $request->file($field)->store('stores', 'public');
                if ($store) {
                    $this->deleteFile($store->{$field});
                }
            }
        }

        return $data;
    }

    private function deleteFile(?string $path): void
    {
        if ($path && ! Str::startsWith($path, ['http://', 'https://'])) {
            Storage::disk('public')->delete($path);
        }
    }
}
