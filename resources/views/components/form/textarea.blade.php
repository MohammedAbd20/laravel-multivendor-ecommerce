@props([
    'label'=> false,'name','type'=>'text','value',
])
@if ($label)
    <label for="">{{ $label }}</label>
@endif
<textarea
    type="{{ $type }}"
    name="{{ $name }}"
    {{ $attributes->class([
        'form-control',
        'is-invalid' => $errors->has($name)
        ])
    }}
>{{old($name , $value) }}</textarea>
@error($name)
    <div class="text-danger">
        {{ $message }}
    </div>
@enderror
