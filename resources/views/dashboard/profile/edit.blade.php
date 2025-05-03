@extends('layouts.dashboard')
@section('title','Edit Profile')
@section('breadcrump')
    @parent
    <li class="breadcrumb-item active">Edit Profile</li>
@endsection

@section('content')
@if($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<x-alert type="success" />
    <form action="{{ route('dashboard.profile.edit') }}" method="post" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <div class="form-row mt-2">
            <div class="col-md-6">
                <x-form.input name="first_name" label="First Name" :value="$user->profile->first_name ?? '' "/>
            </div>
            <div class="col-md-6">
                <x-form.input name="last_name" label="Last Name" :value="$user->profile->last_name ?? '' "/>
            </div>
        </div>
        <div class="form-row mt-2">
            <div class="col-md-6">
                <x-form.input type="date" name="birthday" label="Birthday" :value="$user->profile->birthday ?? '' "/>
            </div>
            <div class="col-md-6">
                <x-form.radio name="gender" label="Gengder" :options="['male'=>'Male','female'=>'Female']" :checked="$user->profile->gender ?? '' "/>
            </div>
        </div>
        <div class="form-row mt-2">
            <div class="col-md-4">
                <x-form.input name="street_address" label="Street Adress" :value="$user->profile->street_address ?? '' "/>
            </div>
            <div class="col-md-4">
                <x-form.input name="city" label="City" :value="$user->profile->city ?? '' "/>
            </div>
            <div class="col-md-4">
                <x-form.input name="state" label="State" :value="$user->profile->state ?? '' "/>
            </div>
        </div>
        <div class="form-row mt-2">
            <div class="col-md-4">
                <x-form.input name="postal_code" label="Postal Code" :value="$user->profile->postal_code ?? '' "/>
            </div>
            <div class="col-md-4">
                <label for="">Country</label>
                <select name="country" class="form-control form-select" id="country">
                    <option value="">Primary Country</option>
                    @foreach ($countries as $countryCode => $countryName)
                        <option value="{{ $countryCode ?? '' }}" {{ old('country', $user->profile->country ?? '') == $countryCode ? 'selected' : '' }}>
                            {{ $countryName }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label for="">Locale</label>
                <select name="locale" class="form-control form-select" id="locale">
                    <option value="">Primary Locale</option>
                    @foreach ($locales as $localeCode => $localeName)
                        <option value="{{ $localeCode ?? '' }}" {{ old('locale', $user->profile->locale ?? '') == $localeCode ? 'selected' : '' }}>
                            {{ $localeName ?? '' }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <button type="submit" class="btn btn-outline-primary mt-4">Edit</button>
    </form>

    {{-- <a href="{{ route('front.profile.edit') }}" class="btn btn-outline-primary my-2">Email Pass && User</a> --}}

@endsection
