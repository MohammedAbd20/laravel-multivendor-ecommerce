<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductsController extends Controller
{
    private const SORTS = ['name', 'price', 'quantity', 'created_at'];
    private const FILTERS = ['q', 'store_id', 'category_id', 'status', 'stock', 'tag_id'];

    public function index(Request $request)
    {
        $filters = $request->only(self::FILTERS);
        $sort = in_array($request->query('sort'), self::SORTS, true) ? $request->query('sort') : 'created_at';
        $dir = $request->query('dir') === 'asc' ? 'asc' : 'desc';

        $products = Product::with(['category', 'store', 'tags'])
            ->filter($filters)
            ->orderBy("products.{$sort}", $dir)->orderByDesc('products.id')
            ->paginate(15)->withQueryString();

        // Status tabs: counts respect every filter except status.
        $counts = Product::filter(collect($filters)->except('status')->all())
            ->selectRaw('products.status, COUNT(*) as n')->groupBy('products.status')->pluck('n', 'status');

        return view('dashboard.products.index', [
            'products' => $products,
            'counts' => $counts,
            'stores' => Store::orderBy('name')->get(),
            'categories' => Category::orderBy('name')->get(),
            'tags' => Tag::orderBy('name')->get(),
            'sort' => $sort,
            'dir' => $dir,
            'trashed' => Product::onlyTrashed()->count(),
        ]);
    }

    public function create(Request $request)
    {
        $product = new Product([
            'status' => 'active', 'quantity' => 0, 'store_id' => $request->query('store_id'),
        ]);

        return view('dashboard.products.create', $this->formData($product));
    }

    public function store(Request $request)
    {
        $data = $request->validate(Product::rules());
        $product = Product::create($this->payload($request, $data));
        $this->syncTags($product, $data['tags'] ?? '');

        return redirect()->route('products.index')->with('success', "Product \"{$product->name}\" created.");
    }

    public function edit(string $id)
    {
        $product = Product::with('tags')->find($id);
        if ($product === null) {
            return redirect()->route('products.index')->with('info', 'That product no longer exists.');
        }

        return view('dashboard.products.edit', $this->formData($product));
    }

    public function update(Request $request, string $id)
    {
        $product = Product::findOrFail($id);
        $data = $request->validate(Product::rules($product->id));

        $payload = $this->payload($request, $data, $product);
        $product->update($payload);
        $this->syncTags($product, $data['tags'] ?? '');

        return redirect()->route('products.index')->with('success', "Product \"{$product->name}\" updated.");
    }

    public function show(string $id)
    {
        return redirect()->route('products.edit', $id);
    }

    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return redirect()->route('products.index')->with('success', "\"{$product->name}\" moved to the trash.");
    }

    /** Apply one action to the selected products. */
    public function bulk(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
            'action' => ['required', Rule::in(['active', 'draft', 'archived', 'delete'])],
        ]);

        $query = Product::whereIn('id', $data['ids']);
        $n = (clone $query)->count();

        if ($data['action'] === 'delete') {
            $query->get()->each->delete();
            $msg = "{$n} " . str('product')->plural($n) . ' moved to the trash.';
        } else {
            $query->update(['status' => $data['action']]);
            $msg = "{$n} " . str('product')->plural($n) . " set to {$data['action']}.";
        }

        return back()->with('success', $msg);
    }

    public function trash(Request $request)
    {
        $products = Product::onlyTrashed()->with(['category', 'store'])
            ->filter($request->only(['q', 'store_id']))
            ->latest('deleted_at')->paginate(15)->withQueryString();

        return view('dashboard.products.trash', ['products' => $products, 'stores' => Store::orderBy('name')->get()]);
    }

    public function restore(Request $request, $id)
    {
        $product = Product::onlyTrashed()->findOrFail($id);
        $product->restore();

        return redirect()->route('products.trash')->with('success', "\"{$product->name}\" restored.");
    }

    public function forceDelete($id)
    {
        $product = Product::onlyTrashed()->findOrFail($id);
        $this->deleteImage($product->image);
        $product->forceDelete();

        return redirect()->route('products.trash')->with('success', "\"{$product->name}\" deleted permanently.");
    }

    public function restoreAll()
    {
        $n = 0;
        Product::onlyTrashed()->chunkById(100, function ($products) use (&$n) {
            foreach ($products as $product) {
                $product->restore();
                $n++;
            }
        });

        return redirect()->route('products.trash')->with('success', "{$n} " . str('product')->plural($n) . ' restored.');
    }

    public function forceDeleteAll()
    {
        $n = 0;
        Product::onlyTrashed()->chunkById(100, function ($products) use (&$n) {
            foreach ($products as $product) {
                $this->deleteImage($product->image);
                $product->forceDelete();
                $n++;
            }
        });

        return redirect()->route('products.trash')->with('success', "Trash emptied ({$n} " . str('product')->plural($n) . ' deleted permanently).');
    }

    private function formData(Product $product): array
    {
        return [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(),
            'stores' => Store::orderBy('name')->get(),
            'knownTags' => Tag::orderBy('name')->pluck('name'),
        ];
    }

    private function payload(Request $request, array $data, ?Product $product = null): array
    {
        $payload = collect($data)->except(['image', 'tags'])->all();
        $payload['featured'] = $request->boolean('featured');
        $payload['category_id'] = $payload['category_id'] ?? null;

        if ($request->hasFile('image')) {
            $payload['image'] = $request->file('image')->store('uploads', 'public');
            if ($product) {
                $this->deleteImage($product->image);
            }
        } elseif ($product && $request->boolean('remove_image')) {
            $this->deleteImage($product->image);
            $payload['image'] = null;
        }

        return $payload;
    }

    private function syncTags(Product $product, string $csv): void
    {
        $ids = collect(explode(',', $csv))
            ->map(fn ($name) => trim($name))
            ->filter()
            ->unique(fn ($name) => Str::slug($name))
            ->map(fn ($name) => Tag::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name])->id)
            ->all();

        $product->tags()->sync($ids);
    }

    private function deleteImage(?string $path): void
    {
        if ($path && ! Str::startsWith($path, ['http://', 'https://'])) {
            Storage::disk('public')->delete($path);
        }
    }
}
