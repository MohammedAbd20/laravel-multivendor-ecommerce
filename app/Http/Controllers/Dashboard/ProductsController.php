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


class ProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $request = request();
        // leftJoin('products as parents','parents.id','=','products.parent_id')
        // ->select([
        //     'products.*',
        //     'parents.name as parent_name'
        // ])
        // ->
        $products = Product::with(['category','store','tags'])
        ->filter($request)->paginate();

        // dd($products);
        return view('dashboard.products.index',compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
        {
            $categoryParents = Category::all();
            $storeParents = Store::all();

            $product = new Product();
            return view('dashboard.products.create',compact('categoryParents','product','storeParents'));
        }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $request->validate([
            'name'=>'required'
        ]);

        $data = $request->except('image');
        if($request->hasFile('image')){
            $file = $request->file('image');
            $path = $file->store('uploads','public');
            // dd($path);
            $data['image']=$path;
        }

        $products = Product::create($data);

        return redirect()->route('products.index')->with('success','Product Created');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $product = Product::find($id);
        // dd($product);
        if($product == null){
            return redirect()->route("products.index")->with('info','The Product Not Found');
        }

        $parents = Product::where('id','<>',$id)->get();

        $categoryParents = Category::all();
        $storeParents = Store::all();
        return view('dashboard.products.edit',compact('product','parents','categoryParents','storeParents'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required'
        ]);

        $product = Product::findOrfail($id);
        $request->merge([
            'slug' => Str::slug($request->post('name'))
        ]);

        $data = $request->except(['image', 'tags']);

        if($request->hasFile('image')){
            $file = $request->file('image');
            $path = $file->store('uploads','public');
            // dd($path);
            $data['image']=$path;
        }

        $tags = explode(',',$request->post('tags'));
        $tag_ids = [];
        foreach ($tags as $t_name){
            $slug = Str::slug($t_name);
            $tag = Tag::where('slug',$slug)->first();
            if(!$tag){
                $tag = Tag::create([
                    'name' =>$t_name,
                    'slug' => $slug,
                ]);
            }
            // dd($tag);
            $tag_ids[] = $tag->id;
        }
        $product->tags()->sync($tag_ids);
        $product->update($data);

        return redirect()->route('products.index')->with('success','Product Updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::findOrfail($id);
        $product->delete();
        return redirect()->route('products.index')->with('danger','Product Deleted, now show in trash');
    }


    public function trash() {
        $request = request();
        $products = Product::onlyTrashed()->filter($request)->paginate(8);
        return view('dashboard.products.trash',compact('products'));
    }

    public function restore(Request $request,$id) {
        $product = Product::onlyTrashed()->findOrfail($id);
        $product->restore();
        return redirect()->route('products.trash')->with('success','Product Restore');
    }
    public function forceDelete($id) {
        $product = Product::onlyTrashed()->findOrfail($id);
        $product->forceDelete();
        if($product->image){
            Storage::disk('public')->delete($product->image);
        }
        return redirect()->route('products.trash')->with('success','Product Deleted Forever!');
    }

    public function destroyAll()
    {
        $products = Product::all();

        foreach ($products as $product) {
            $product->delete();
        }

        return redirect()->route('products.index')->with('danger', 'All Products Deleted, now show in trash');
    }
    public function forceDeleteAll()
    {
        // الحصول على جميع الفئات المحذوفة
        $products = Product::onlyTrashed()->paginate(8);

        foreach ($products as $product) {
            $product->forceDelete();
            if($product->image){
                Storage::disk('public')->delete($product->image);
            }
        }

        return redirect()->route('products.trash')->with('success', 'All Products Deleted Forever!');
    }

    public function restoreAll(Request $request)
    {
        // الحصول على جميع الفئات المحذوفة
        $products = Product::onlyTrashed()->paginate(8);

        foreach ($products as $product) {
            $product->restore();
        }

        return redirect()->route('products.trash')->with('success', 'All Products Restored Successfully!');
    }
}
