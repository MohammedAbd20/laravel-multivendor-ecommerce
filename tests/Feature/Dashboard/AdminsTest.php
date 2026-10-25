<?php

require_once __DIR__ . '/helpers.php';

use App\Actions\Fortify\AuthenticateUser;
use App\Models\Admin;
use Illuminate\Http\Request;

test('only super admins can manage admin accounts', function () {
    signInAdmin(['super_admin' => false]);

    $this->get('/admin/admins')->assertForbidden();
    $this->post('/admin/admins', [])->assertForbidden();
});

test('a super admin can create an admin', function () {
    signInAdmin();

    $this->post('/admin/admins', [
        'name' => 'Lina', 'email' => 'lina@example.com', 'username' => 'lina', 'phone_number' => '0591111111',
        'status' => 'active', 'password' => 'a-long-password', 'password_confirmation' => 'a-long-password',
    ])->assertRedirect('/admin/admins');

    $lina = Admin::where('email', 'lina@example.com')->first();
    expect($lina)->not->toBeNull()->and($lina->super_admin)->toBeFalsy();
});

test('passwords must be confirmed and long enough', function () {
    signInAdmin();

    $this->post('/admin/admins', [
        'name' => 'Lina', 'email' => 'lina@example.com', 'username' => 'lina', 'phone_number' => '0591111111',
        'status' => 'active', 'password' => 'short', 'password_confirmation' => 'different',
    ])->assertSessionHasErrors('password');
});

test('you cannot delete yourself or the last super admin', function () {
    $me = signInAdmin();

    $this->delete("/admin/admins/{$me->id}")->assertSessionHas('danger');
    expect(Admin::find($me->id))->not->toBeNull();
});

test('you cannot lock yourself out by editing your own account', function () {
    $me = signInAdmin();

    $this->put("/admin/admins/{$me->id}", [
        'name' => $me->name, 'email' => $me->email, 'username' => $me->username, 'phone_number' => $me->phone_number,
        'status' => 'inactive',
    ])->assertRedirect();

    expect($me->fresh()->status)->toBe('active')->and((bool) $me->fresh()->super_admin)->toBeTrue();
});

test('inactive admins cannot sign in', function () {
    Admin::factory()->create(['email' => 'off@example.com', 'status' => 'inactive']);
    Admin::factory()->create(['email' => 'on@example.com', 'status' => 'active']);

    $login = fn ($email) => (new AuthenticateUser)->authenticate(
        Request::create('/admin/login', 'POST', [config('fortify.username') => $email, 'password' => 'password'])
    );

    expect($login('off@example.com'))->toBeFalse()
        ->and($login('on@example.com'))->toBeInstanceOf(Admin::class);
});
