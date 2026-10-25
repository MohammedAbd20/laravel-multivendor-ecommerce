<x-front-layout title="Verify email">

    <div class="account-login section">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 offset-lg-3 col-md-10 offset-md-1 col-12">
                    <div class="card login-form">
                        <div class="card-body">
                            <div class="title">
                                <h3>Verify your email</h3>
                                <p>Thanks for signing up! Please click the link we emailed you to verify your address.
                                    If you did not receive it, we can send another.</p>
                            </div>

                            @if (session('status') == 'verification-link-sent')
                                <div class="alert alert-success">A new verification link has been sent to your email address.</div>
                            @endif

                            <form action="{{ route('verification.send') }}" method="post">
                                @csrf
                                <div class="button">
                                    <button class="btn" type="submit">Resend verification email</button>
                                </div>
                            </form>
                            <form action="{{ route('logout') }}" method="post" class="mt-3">
                                @csrf
                                <button class="btn btn-link p-0" type="submit">Log out</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-front-layout>
