<x-front-layout title="Forgot password">

    <div class="account-login section">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 offset-lg-3 col-md-10 offset-md-1 col-12">
                    <form class="card login-form" action="{{ route('password.email') }}" method="post">
                        @csrf
                        <div class="card-body">
                            <div class="title">
                                <h3>Forgot your password?</h3>
                                <p>Enter your email address and we will send you a link to reset it.</p>
                            </div>

                            @if (session('status'))
                                <div class="alert alert-success">{{ session('status') }}</div>
                            @endif
                            @if ($errors->any())
                                <div class="alert alert-danger">{{ $errors->first() }}</div>
                            @endif

                            <div class="form-group input-group">
                                <label for="email">Email</label>
                                <input class="form-control" type="email" id="email" name="email"
                                    value="{{ old('email') }}" required autofocus>
                            </div>
                            <div class="button">
                                <button class="btn" type="submit">Email password reset link</button>
                            </div>
                            <p class="outer-link"><a href="{{ route('login') }}">Back to login</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</x-front-layout>
