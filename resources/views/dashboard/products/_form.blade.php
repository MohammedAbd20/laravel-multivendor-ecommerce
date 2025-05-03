


@if ($errors->any())
<div class="alert alert-danger">
    <ul>
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif


<div class="form-groub mt-3">
    {{-- <label for="">Product Name</label>
    <input class="form-control @error('name') is-invalid @enderror" value='{{ old('name', $product->name) }}'>
    @error('name')
        <div class="text-danger">
            {{ $message }}
        </div>
    @enderror --}}
    <x-form.input type="text" name="name" :value="$product->name" label="Product Name" />
</div>
<div class="form-group mt-3">
    <label for="category_id">Category Parent</label>
    <select name="category_id" class="form-control form-select" id="category_id">
        <option value="">Primary Category</option>
        @foreach ($categoryParents as $categoryParent)
            <option value="{{ $categoryParent->id}}" @selected($product->category_id == $categoryParent->id)>{{old('name', $categoryParent->name )}}</option>
        @endforeach
    </select>

    @error('category_id')
        <div class="text-danger">
            {{ $message }}
        </div>
    @enderror
</div>
<div class="form-group mt-3">
    <label for="store_id">Store Parent</label>
    <select name="store_id" class="form-control form-select" id="store_id   ">
        <option value="">Primary Product</option>
        @foreach ($storeParents as $storeParent)
            <option value="{{ $storeParent->id }}" @selected($product->store_id == $storeParent->id)>{{old('name', $storeParent->name )}}</option>
        @endforeach
    </select>

    @error('store_id')
        <div class="text-danger">
            {{ $message }}
        </div>
    @enderror
</div>
<div class="form-groub mt-3">
    <x-form.textarea type="text" name="description" label="Description" rows="4" value="{{ $product->description }}" />
</div>
<div class="form-groub mt-3">
    {{-- <input type="file" name="image" class="form-control" accept="image/*" > --}}
    <x-form.input type="file" name="image" accept="image/*" label="Image"/>
    <img src="{{ asset('storage/'.$category->image) }}" alt="" height="200">
</div>
<div class="form-groub mt-3">
    <x-form.input type="number" name="price" :value="$product->price" label="Product Price" />
</div>
<div class="form-groub mt-3">
    <x-form.input type="number" name="compare_price" :value="$product->compare_price" label="Compare Price" />
</div>
<div class="form-groub mt-3">
    <x-form.input type="text" name="tags" label="Tag" :value="implode(',',$product->tags->pluck('name')->toArray())"/>
</div>
<div class="form-groub mt-3 ">
    <label for="">Status</label>
    <div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="status" value="active" @checked(old('status',$product->status == 'active'))>
            <label class="form-check-label">
                active
            </label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="status" value="draft" @checked(old('status',$product->status == 'draft'))>
            <label class="form-check-label">
                draft
            </label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="status" value="archived" @checked(old('status',$product->status == 'archived'))>
            <label class="form-check-label">
                archived
            </label>
        </div>
    </div>
    {{-- <x-form.radio name="status" :checked="$product->status" :options="['active' => 'Active','archived' => "Archived"]"/> --}}
    @error('status')
        <div class="text-danger">
            {{ $message }}
        </div>
    @enderror
</div>
<div class="form-groub mt-3">
    <button type="submit" class="btn btn-primary">{{ $button ?? "Save" }}</button>
</div>
