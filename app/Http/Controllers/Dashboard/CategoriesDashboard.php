<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoriesDashboard extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $request = request();
        // leftJoin('categories as parents','parents.id','=','categories.parent_id')
        // ->select([
        //     'categories.*',
        //     'parents.name as parent_name'
        // ])
        $categories = Category::with(['products','parent','children'])
        ->withCount('products')
        ->filter($request)->paginate(1);
        return view('dashboard.categories.index',compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $parents = Category::all();
        $category = new Category();
        return view('dashboard.categories.create',compact('parents','category'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $request->validate(Category::rules());


        $request->merge([
            'slug' => Str::slug($request->post('name'))
        ]);
        $data = $request->except('image');
        if($request->hasFile('image')){
            $file = $request->file('image');
            $path = $file->store('uploads','public');
            // dd($path);
            $data['image']=$path;
        }

        $categories = Category::create($data);

        return redirect()->route('categories.index')->with('success','Category Created');
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
        $category = Category::find($id);
        // dd($category);
        if($category == null){
            return redirect()->route("categories.index")->with('info','The Category Not Found');
        }

        $parents = Category::where('id','<>',$id)->get();
        return view('dashboard.categories.edit',compact('category','parents'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate(Category::rules($id));

        $category = Category::findOrfail($id);
        $request->merge([
            'slug' => Str::slug($request->post('name'))
        ]);

        $data = $request->except('image');
        if($request->hasFile('image')){
            $file = $request->file('image');
            $path = $file->store('uploads','public');
            // dd($path);
            $data['image']=$path;
        }
        $category->update($data);

        return redirect()->route('categories.index')->with('success','Category Updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = Category::findOrfail($id);
        $category->delete();
        return redirect()->route('categories.index')->with('danger','Category Deleted, now show in trash');
    }


    public function trash() {
        $request = request();
        $categories = Category::onlyTrashed()->filter($request)->paginate(8);
        return view('dashboard.categories.trash',compact('categories'));
    }

    public function restore(Request $request,$id) {
        $category = Category::onlyTrashed()->findOrfail($id);
        $category->restore();
        return redirect()->route('categories.trash')->with('success','Category Restore');
    }
    public function forceDelete($id) {
        $category = Category::onlyTrashed()->findOrfail($id);
        $category->forceDelete();
        if($category->image){
            Storage::disk('public')->delete($category->image);
        }
        return redirect()->route('categories.trash')->with('success','Category Deleted Forever!');
    }

    public function destroyAll()
    {
        $categories = Category::all();

        foreach ($categories as $category) {
            $category->delete();
        }

        return redirect()->route('categories.index')->with('danger', 'All Categories Deleted, now show in trash');
    }
    public function forceDeleteAll()
    {
        // الحصول على جميع الفئات المحذوفة
        $categories = Category::onlyTrashed()->paginate(8);

        foreach ($categories as $category) {
            $category->forceDelete();
            if($category->image){
                Storage::disk('public')->delete($category->image);
            }
        }

        return redirect()->route('categories.trash')->with('success', 'All Categories Deleted Forever!');
    }

    public function restoreAll(Request $request)
    {
        // الحصول على جميع الفئات المحذوفة
        $categories = Category::onlyTrashed()->paginate(8);

        foreach ($categories as $category) {
            $category->restore();
        }

        return redirect()->route('categories.trash')->with('success', 'All Categories Restored Successfully!');
    }
}
