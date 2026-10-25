<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoriesDashboard extends Controller
{
    private const SORTS = ['name', 'products_count', 'created_at'];

    public function index(Request $request)
    {
        $sort = in_array($request->query('sort'), self::SORTS, true) ? $request->query('sort') : 'name';
        $dir = $request->query('dir') === 'desc' ? 'desc' : 'asc';

        $categories = Category::with('parent')
            ->withCount(['products' => fn ($q) => $q->withoutGlobalScopes(), 'children'])
            ->filter($request->only(['q', 'status']))
            ->orderBy($sort, $dir)->orderBy('id')
            ->paginate(15)->withQueryString();

        return view('dashboard.categories.index', [
            'categories' => $categories,
            'sort' => $sort,
            'dir' => $dir,
            'trashed' => Category::onlyTrashed()->count(),
        ]);
    }

    public function create()
    {
        return view('dashboard.categories.create', [
            'category' => new Category(['status' => 'active']),
            'parents' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(Category::rules());
        $category = Category::create($this->payload($request, $data));

        return redirect()->route('categories.index')->with('success', "Category \"{$category->name}\" created.");
    }

    public function show(string $id)
    {
        return redirect()->route('categories.edit', $id);
    }

    public function edit(string $id)
    {
        $category = Category::find($id);
        if ($category === null) {
            return redirect()->route('categories.index')->with('info', 'That category no longer exists.');
        }

        return view('dashboard.categories.edit', [
            'category' => $category,
            'parents' => Category::where('id', '<>', $id)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $category = Category::findOrFail($id);
        $data = $request->validate(Category::rules($category->id));
        $category->update($this->payload($request, $data, $category));

        return redirect()->route('categories.index')->with('success', "Category \"{$category->name}\" updated.");
    }

    public function destroy(string $id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return redirect()->route('categories.index')->with('success', "\"{$category->name}\" moved to the trash.");
    }

    public function trash(Request $request)
    {
        $categories = Category::onlyTrashed()->filter($request->only(['q']))->latest('deleted_at')->paginate(15)->withQueryString();

        return view('dashboard.categories.trash', ['categories' => $categories]);
    }

    public function restore(Request $request, $id)
    {
        $category = Category::onlyTrashed()->findOrFail($id);
        $category->restore();

        return redirect()->route('categories.trash')->with('success', "\"{$category->name}\" restored.");
    }

    public function forceDelete($id)
    {
        $category = Category::onlyTrashed()->findOrFail($id);
        $this->deleteImage($category->image);
        $category->forceDelete();

        return redirect()->route('categories.trash')->with('success', "\"{$category->name}\" deleted permanently.");
    }

    public function restoreAll()
    {
        $n = 0;
        Category::onlyTrashed()->chunkById(100, function ($items) use (&$n) {
            foreach ($items as $item) {
                $item->restore();
                $n++;
            }
        });

        return redirect()->route('categories.trash')->with('success', "{$n} " . str('category')->plural($n) . ' restored.');
    }

    public function forceDeleteAll()
    {
        $n = 0;
        Category::onlyTrashed()->chunkById(100, function ($items) use (&$n) {
            foreach ($items as $item) {
                $this->deleteImage($item->image);
                $item->forceDelete();
                $n++;
            }
        });

        return redirect()->route('categories.trash')->with('success', "Trash emptied ({$n} " . str('category')->plural($n) . ' deleted permanently).');
    }

    private function payload(Request $request, array $data, ?Category $category = null): array
    {
        $payload = collect($data)->except('image')->all();
        $payload['parent_id'] = $payload['parent_id'] ?? null;
        if (! $category || $category->name !== $data['name']) {
            $payload['slug'] = Category::uniqueSlug($data['name'], $category?->id);
        }

        if ($request->hasFile('image')) {
            $payload['image'] = $request->file('image')->store('uploads', 'public');
            if ($category) {
                $this->deleteImage($category->image);
            }
        }

        return $payload;
    }

    private function deleteImage(?string $path): void
    {
        if ($path && ! Str::startsWith($path, ['http://', 'https://'])) {
            Storage::disk('public')->delete($path);
        }
    }
}
