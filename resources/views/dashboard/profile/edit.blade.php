@extends('layouts.dashboard')
@section('title', 'Your profile')
@section('breadcrump')
    @parent
    <li>Profile</li>
@endsection
@section('subtitle')
    Signed in as {{ $user->email }}.
@endsection

@section('content')
    @php
        $p = $user->profile;
        $field = fn ($name, $label, $type = 'text') => view()->make('dashboard.profile._field', ['name' => $name, 'label' => $label, 'type' => $type, 'p' => $p])->render();
    @endphp
    <form class="panel" style="max-width:860px" action="{{ route('dashboard.profile.update') }}" method="post">
        @csrf @method('PATCH')
        <div class="panel-body">
            <div class="form-grid">
                <div class="c-6">{!! $field('first_name', 'First name') !!}</div>
                <div class="c-6">{!! $field('last_name', 'Last name') !!}</div>
                <div class="c-6">{!! $field('birthday', 'Birthday', 'date') !!}</div>
                <div class="c-6">
                    <label class="form-label d-block">Gender</label>
                    <div class="d-flex gap-4 mt-2">
                        @foreach (['male' => 'Male', 'female' => 'Female'] as $value => $label)
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="gender" id="gender-{{ $value }}" value="{{ $value }}" @checked(old('gender', $p->gender) === $value)>
                                <label class="form-check-label" for="gender-{{ $value }}">{{ $label }}</label>
                            </div>
                        @endforeach
                    </div>
                    @error('gender')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="c-12">{!! $field('street_address', 'Street address') !!}</div>
                <div class="c-4">{!! $field('city', 'City') !!}</div>
                <div class="c-4">{!! $field('state', 'State') !!}</div>
                <div class="c-4">{!! $field('postal_code', 'Postal code') !!}</div>
                <div class="c-6">
                    <label class="form-label" for="country">Country</label>
                    <select name="country" id="country" class="form-select @error('country') is-invalid @enderror">
                        <option value="">Choose a country</option>
                        @foreach ($countries as $code => $name)
                            <option value="{{ $code }}" @selected(old('country', $p->country) == $code)>{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('country')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="c-6">
                    <label class="form-label" for="locale">Language</label>
                    <select name="locale" id="locale" class="form-select">
                        <option value="">Choose a language</option>
                        @foreach ($locales as $code => $name)
                            <option value="{{ $code }}" @selected(old('locale', $p->locale) == $code)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="form-actions"><button type="submit" class="btn btn-primary">Save profile</button></div>
    </form>
@endsection
