<label class="form-label" for="{{ $name }}">{{ $label }}</label>
<input id="{{ $name }}" type="{{ $type }}" name="{{ $name }}" class="form-control @error($name) is-invalid @enderror" value="{{ old($name, $p->{$name}) }}">
@error($name)<div class="invalid-feedback">{{ $message }}</div>@enderror
