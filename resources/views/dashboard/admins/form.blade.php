@extends('layouts.dashboard')
@php $editing = $admin->exists; $self = $editing && $admin->id === Auth::id(); @endphp
@section('title', $editing ? 'Edit ' . $admin->name : 'New admin')
@section('breadcrump')
    @parent
    <li><a href="{{ route('admins.index') }}">Admins</a></li>
    <li>{{ $editing ? 'Edit' : 'New' }}</li>
@endsection

@section('content')
    <form class="panel" style="max-width:860px" method="post" action="{{ $editing ? route('admins.update', $admin) : route('admins.store') }}">
        @csrf
        @if ($editing) @method('PUT') @endif
        <div class="panel-body">
            <div class="form-grid">
                @foreach ([['name', 'Full name', 'text', 6], ['email', 'Email', 'email', 6], ['username', 'Username', 'text', 6], ['phone_number', 'Phone number', 'text', 6]] as [$field, $label, $type, $span])
                    <div class="c-{{ $span }}">
                        <label class="form-label" for="{{ $field }}">{{ $label }}</label>
                        <input id="{{ $field }}" type="{{ $type }}" name="{{ $field }}" class="form-control @error($field) is-invalid @enderror" value="{{ old($field, $admin->{$field}) }}" required>
                        @error($field)<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                @endforeach
                <div class="c-6">
                    <label class="form-label" for="password">{{ $editing ? 'New password' : 'Password' }}</label>
                    <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password" @if(! $editing) required @endif>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="help">{{ $editing ? 'Leave empty to keep the current password. ' : '' }}At least 8 characters.</div>
                </div>
                <div class="c-6">
                    <label class="form-label" for="password_confirmation">Confirm password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
                </div>
                <div class="c-6">
                    <label class="form-label" for="status">Status</label>
                    <select id="status" name="status" class="form-select" @disabled($self)>
                        @foreach (['active', 'inactive'] as $s)<option value="{{ $s }}" @selected(old('status', $admin->status) === $s)>{{ ucfirst($s) }}</option>@endforeach
                    </select>
                    @if ($self)<input type="hidden" name="status" value="active"><div class="help">You cannot deactivate your own account.</div>
                    @else<div class="help">Inactive admins cannot sign in.</div>@endif
                </div>
                <div class="c-6">
                    <label class="form-label d-block">Role</label>
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" role="switch" id="super_admin" name="super_admin" value="1" @checked(old('super_admin', $admin->super_admin)) @disabled($self)>
                        <label class="form-check-label" for="super_admin">Super admin</label>
                    </div>
                    @if ($self)<input type="hidden" name="super_admin" value="1">@endif
                    <div class="help">Super admins can manage other admin accounts.</div>
                </div>
            </div>
        </div>
        <div class="form-actions">
            <button class="btn btn-primary" type="submit">{{ $editing ? 'Save changes' : 'Create admin' }}</button>
            <a class="btn btn-light" href="{{ route('admins.index') }}">Cancel</a>
        </div>
    </form>
@endsection
