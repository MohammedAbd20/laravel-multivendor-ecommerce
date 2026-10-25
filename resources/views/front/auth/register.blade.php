<x-front-layout title="Register">

    <!-- Start Account Register Area -->
    <div class="account-login section">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 offset-lg-3 col-md-10 offset-md-1 col-12">
                    <form class="card login-form" action="{{ route('register') }}" method="post">
                        @csrf
                        <div class="card-body">
                            <div class="title">
                                <h3>Register Now</h3>
                                <p>Create an account to shop and track your orders.</p>
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
                                <label for="reg-name">Name</label>
                                <input class="form-control" type="text" id="reg-name" name="name"
                                    value="{{ old('name') }}" required autofocus autocomplete="name">
                            </div>
                            <div class="form-group input-group">
                                <label for="reg-email">Email</label>
                                <input class="form-control" type="email" id="reg-email" name="email"
                                    value="{{ old('email') }}" required autocomplete="username">
                            </div>
                            <div class="form-group input-group">
                                <label for="reg-pass">Password</label>
                                <input class="form-control" type="password" id="reg-pass" name="password" required
                                    autocomplete="new-password">
                            </div>
                            <div class="form-group input-group">
                                <label for="reg-pass-confirm">Confirm password</label>
                                <input class="form-control" type="password" id="reg-pass-confirm"
                                    name="password_confirmation" required autocomplete="new-password">
                            </div>
                            <div class="button">
                                <button class="btn" type="submit">Register</button>
                            </div>
                            <p class="outer-link">Already have an account? <a href="{{ route('login') }}">Login here</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- End Account Register Area -->

</x-front-layout>
