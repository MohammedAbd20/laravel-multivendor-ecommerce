<div class="panel-body">
    <div class="form-grid">
        <div class="c-6">
            <label class="form-label" for="name">Category name</label>
            <input id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $category->name) }}" required>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="c-6">
            <label class="form-label" for="parent_id">Parent category</label>
            <select id="parent_id" name="parent_id" class="form-select @error('parent_id') is-invalid @enderror">
                <option value="">None (top level)</option>
                @foreach ($parents as $parent)
                    <option value="{{ $parent->id }}" @selected(old('parent_id', $category->parent_id) == $parent->id)>{{ $parent->name }}</option>
                @endforeach
            </select>
            @error('parent_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="c-12">
            <label class="form-label" for="description">Description</label>
            <textarea id="description" name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $category->description) }}</textarea>
            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="c-6">
            <label class="form-label" for="image">Image</label>
            <input id="image" type="file" name="image" accept="image/*" class="form-control @error('image') is-invalid @enderror">
            <div class="help">JPG, PNG or WebP, up to 2 MB.</div>
            @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
            @if ($category->image)
                <img src="{{ \Illuminate\Support\Str::startsWith($category->image, ['http://', 'https://']) ? $category->image : asset('storage/' . $category->image) }}" alt="" height="90" class="mt-2" style="border-radius:8px;border:1px solid var(--line)">
            @endif
        </div>
        <div class="c-6">
            <label class="form-label" for="status">Status</label>
            <select id="status" name="status" class="form-select @error('status') is-invalid @enderror">
                @foreach (\App\Models\Category::STATUSES as $s)
                    <option value="{{ $s }}" @selected(old('status', $category->status) === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>
