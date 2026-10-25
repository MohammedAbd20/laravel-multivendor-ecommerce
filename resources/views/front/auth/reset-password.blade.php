<x-front-layout title="Reset password">

    <div class="account-login section">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 offset-lg-3 col-md-10 offset-md-1 col-12">
                    <form class="card login-form" action="{{ route('password.update') }}" method="post">
                        @csrf
                        <input type="hidden" name="token" value="{{ $request->route('token') }}">
                        <div class="card-body">
                            <div class="title">
                                <h3>Reset password</h3>
                                <p>Choose a new password for your account.</p>
                            </div>

                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="form-group input-group">
                                <label for="email">Email</label>
                                <input class="form-control" type="email" id="email" name="email"
                                    value="{{ old('email', $request->email) }}" required autofocus>
                            </div>
                            <div class="form-group input-group">
                                <label for="password">New password</label>
                                <input class="form-control" type="password" id="password" name="password" required
                                    autocomplete="new-password">
                            </div>
                            <div class="form-group input-group">
                                <label for="password_confirmation">Confirm password</label>
                                <input class="form-control" type="password" id="password_confirmation"
                                    name="password_confirmation" required autocomplete="new-password">
                            </div>
                            <div class="button">
                                <button class="btn" type="submit">Reset password</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</x-front-layout>
