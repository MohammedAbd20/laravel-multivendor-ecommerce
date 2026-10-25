@php
    $err = fn ($f) => $errors->has($f) ? ' is-invalid' : '';
    $fieldError = fn ($f) => $errors->has($f) ? '<div class="invalid-feedback d-block">' . e($errors->first($f)) . '</div>' : '';
    $tagValue = old('tags', $product->exists ? $product->tags->pluck('name')->implode(', ') : '');
@endphp

<div class="row g-3">
    <div class="col-xl-8">
        <section class="panel">
            <div class="panel-head"><h2>Details</h2></div>
            <div class="panel-body">
                <div class="form-grid">
                    <div class="c-12">
                        <label class="form-label" for="name">Product name</label>
                        <input id="name" name="name" class="form-control{{ $err('name') }}" value="{{ old('name', $product->name) }}" required>
                        {!! $fieldError('name') !!}
                    </div>
                    <div class="c-12">
                        <label class="form-label" for="description">Description</label>
                        <textarea id="description" name="description" rows="5" class="form-control{{ $err('description') }}">{{ old('description', $product->description) }}</textarea>
                        {!! $fieldError('description') !!}
                    </div>
                    <div class="c-12">
                        <label class="form-label" for="tags">Tags</label>
                        <input id="tags" name="tags" list="tagList" class="form-control{{ $err('tags') }}" value="{{ $tagValue }}" placeholder="New, Gift idea, Bestseller">
                        <datalist id="tagList">@foreach ($knownTags as $t)<option value="{{ $t }}">@endforeach</datalist>
                        <div class="help">Separate tags with commas. New tags are created automatically.</div>
                        {!! $fieldError('tags') !!}
                    </div>
                </div>
            </div>
        </section>

        <section class="panel">
            <div class="panel-head"><h2>Pricing and stock</h2></div>
            <div class="panel-body">
                <div class="form-grid">
                    <div class="c-4">
                        <label class="form-label" for="price">Price ($)</label>
                        <input id="price" type="number" step="0.01" min="0" name="price" class="form-control{{ $err('price') }}" value="{{ old('price', $product->price) }}" required>
                        {!! $fieldError('price') !!}
                    </div>
                    <div class="c-4">
                        <label class="form-label" for="compare_price">Compare-at price ($)</label>
                        <input id="compare_price" type="number" step="0.01" min="0" name="compare_price" class="form-control{{ $err('compare_price') }}" value="{{ old('compare_price', $product->compare_price) }}">
                        <div class="help">Shown struck through. Must be higher than the price.</div>
                        {!! $fieldError('compare_price') !!}
                    </div>
                    <div class="c-4">
                        <label class="form-label" for="quantity">In stock</label>
                        <input id="quantity" type="number" min="0" step="1" name="quantity" class="form-control{{ $err('quantity') }}" value="{{ old('quantity', $product->quantity ?? 0) }}" required>
                        <div class="help">Units available to sell.</div>
                        {!! $fieldError('quantity') !!}
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="col-xl-4">
        <section class="panel">
            <div class="panel-head"><h2>Organisation</h2></div>
            <div class="panel-body">
                <div class="mb-3">
                    <label class="form-label" for="status">Status</label>
                    <select id="status" name="status" class="form-select{{ $err('status') }}">
                        @foreach (\App\Models\Product::STATUSES as $s)
                            <option value="{{ $s }}" @selected(old('status', $product->status) === $s)>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                    <div class="help">Only active products are visible in the storefront.</div>
                    {!! $fieldError('status') !!}
                </div>
                <div class="mb-3">
                    <label class="form-label" for="store_id">Store</label>
                    <select id="store_id" name="store_id" class="form-select{{ $err('store_id') }}" required>
                        <option value="">Choose a store</option>
                        @foreach ($stores as $s)
                            <option value="{{ $s->id }}" @selected(old('store_id', $product->store_id) == $s->id)>{{ $s->name }}</option>
                        @endforeach
                    </select>
                    {!! $fieldError('store_id') !!}
                </div>
                <div class="mb-3">
                    <label class="form-label" for="category_id">Category</label>
                    <select id="category_id" name="category_id" class="form-select{{ $err('category_id') }}">
                        <option value="">No category</option>
                        @foreach ($categories as $c)
                            <option value="{{ $c->id }}" @selected(old('category_id', $product->category_id) == $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                    {!! $fieldError('category_id') !!}
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="featured" name="featured" value="1" @checked(old('featured', $product->featured))>
                    <label class="form-check-label" for="featured">Featured product</label>
                </div>
            </div>
        </section>

        <section class="panel">
            <div class="panel-head"><h2>Image</h2></div>
            <div class="panel-body">
                <img id="imagePreview" src="{{ $product->image_url }}" alt="" class="w-100 mb-3" style="border-radius:8px;border:1px solid var(--line);aspect-ratio:1/1;object-fit:cover;background:var(--canvas)">
                <input id="image" type="file" name="image" accept="image/*" class="form-control{{ $err('image') }}" aria-label="Product image">
                <div class="help">JPG, PNG or WebP, up to 2 MB.</div>
                {!! $fieldError('image') !!}
                @if ($product->image)
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" id="remove_image" name="remove_image" value="1">
                        <label class="form-check-label" for="remove_image">Remove the current image</label>
                    </div>
                @endif
            </div>
        </section>
    </div>
</div>

<div class="d-flex gap-2 mb-4">
    <button type="submit" class="btn btn-primary">{{ $product->exists ? 'Save changes' : 'Create product' }}</button>
    <a class="btn btn-light" href="{{ route('products.index') }}">Cancel</a>
</div>

@push('script')
    <script>
        document.getElementById('image')?.addEventListener('change', function () {
            var f = this.files && this.files[0];
            if (f) document.getElementById('imagePreview').src = URL.createObjectURL(f);
        });
    </script>
@endpush
