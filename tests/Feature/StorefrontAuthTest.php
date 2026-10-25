<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('the account pages render', function (string $path) {
    $this->get($path)->assertOk();
})->with(['/login', '/register', '/forgot-password', '/reset-password/some-token?email=lina@example.com']);

test('a visitor can register and is signed in', function () {
    $this->post('/register', [
        'name' => 'Lina Said', 'email' => 'lina@example.com',
        'password' => 'Str0ng-pass-word', 'password_confirmation' => 'Str0ng-pass-word',
    ])->assertRedirect();

    $user = User::where('email', 'lina@example.com')->first();
    expect($user)->not->toBeNull()->and(Hash::check('Str0ng-pass-word', $user->password))->toBeTrue();
    $this->assertAuthenticatedAs($user);
});

test('registration validates the form', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $this->post('/register', ['name' => '', 'email' => 'taken@example.com', 'password' => 'x', 'password_confirmation' => 'y'])
        ->assertSessionHasErrors(['name', 'email', 'password']);
});

test('a customer can log in, a wrong password is refused', function () {
    $user = User::factory()->create(['password' => 'correct-horse']);

    $this->post('/login', ['email' => $user->email, 'password' => 'wrong'])->assertSessionHasErrors();
    $this->assertGuest();

    $this->post('/login', ['email' => $user->email, 'password' => 'correct-horse'])->assertRedirect();
    $this->assertAuthenticatedAs($user);
});

test('a password reset link can be requested', function () {
    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email])->assertSessionHas('status');
});
