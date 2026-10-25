<div class="panel-body">
    <div class="form-grid">
        <div class="c-6">
            <label class="form-label" for="name">Store name</label>
            <input id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $store->name) }}" required>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="c-6">
            <label class="form-label" for="slug">URL name</label>
            <input id="slug" name="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $store->slug) }}" placeholder="created from the name if left empty">
            @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="c-12">
            <label class="form-label" for="description">Description</label>
            <textarea id="description" name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $store->description) }}</textarea>
            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="c-4">
            <label class="form-label" for="status">Status</label>
            <select id="status" name="status" class="form-select">
                @foreach (\App\Models\Store::STATUSES as $s)
                    <option value="{{ $s }}" @selected(old('status', $store->status) === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            <div class="help">Only active stores appear on the storefront.</div>
        </div>
        <div class="c-4">
            <label class="form-label" for="logo_image">Logo</label>
            <input id="logo_image" type="file" name="logo_image" accept="image/*" class="form-control @error('logo_image') is-invalid @enderror">
            @error('logo_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <div class="help">Square image, up to 2 MB.</div>
        </div>
        <div class="c-4">
            <label class="form-label" for="cover_image">Cover</label>
            <input id="cover_image" type="file" name="cover_image" accept="image/*" class="form-control @error('cover_image') is-invalid @enderror">
            @error('cover_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <div class="help">Wide image, up to 4 MB.</div>
        </div>
    </div>
</div>
