


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
    {{-- <label for="">Category Name</label>
    <input class="form-control @error('name') is-invalid @enderror" value='{{ old('name', $category->name) }}'>
    @error('name')
        <div class="text-danger">
            {{ $message }}
        </div>
    @enderror --}}
    <x-form.input type="text" name="name" :value="$category->name" label="Category Name" />
</div>
<div class="form-group mt-3">
    <label for="">Category Parent</label>
    <select name="parent_id" class="form-control form-select">
        <option value="">Primary Category</option>
        @foreach ($parents as $parent)
            <option value="{{ $parent->id }}" @selected($category->parent_id == $parent->id)>{{old('name', $parent->name )}}</option>
        @endforeach
    </select>
    @error('parent_id')
        <div class="text-danger">
            {{ $message }}
        </div>
    @enderror
</div>
<div class="form-groub mt-3">
    <x-form.textarea type="text" name="description" label="Description" rows="4" value="{{ $category->description }}" />
</div>
<div class="form-groub mt-3">
    {{-- <input type="file" name="image" class="form-control" accept="image/*" > --}}
    <x-form.input type="file" name="image" accept="image/*" label="Image"/>
    <img src="{{ asset('storage/'.$category->image) }}" alt="" height="200">
</div>
<div class="form-groub mt-3 ">
    <label for="">Status</label>
    <div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="status" value="active" @checked(old('status',$category->status == 'active'))>
            <label class="form-check-label">
                active
            </label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="status" value="archived" @checked(old('status',$category->status == 'archived'))>
            <label class="form-check-label">
                archived
            </label>
        </div>
    </div>
    {{-- <x-form.radio name="status" :checked="$category->status" :options="['active' => 'Active','archived' => "Archived"]"/> --}}
    @error('status')
        <div class="text-danger">
            {{ $message }}
        </div>
    @enderror
</div>
<div class="form-groub mt-3">
    <button type="submit" class="btn btn-primary">{{ $button ?? "Save" }}</button>
</div>
