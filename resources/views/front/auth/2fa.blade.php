<x-front-layout title="2FA">


    <!-- Start Account Login Area -->
    <div class="account-login section">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 offset-lg-3 col-md-10 offset-md-1 col-12">
                    <form class="card login-form" action="{{ route('two-factor.enable') }}" method="post">
                        @csrf
                        <div class="card-body">
                            <div class="title">
                                <h3>2 FA</h3>
                                <p>can enable / disable</p>
                            </div>

                            @if (session('status') == 'two-factor-authentication-enabled')
                                <div class="mb-4 font-medium text-sm">
                                    Please finish configuring two factor authentication below.
                                </div>
                            @endif
                            <div class="button">
                                @if (!Auth::user()->two_factor_secret)
                                    <button class="btn" type="submit">Enable</button>
                                @else
                                <div class="p-3">
                                    {!! Auth::user()->twoFactorQrCodeSvg() !!}
                                </div>
                                <h3>Recovery Code</h3>
                                <ul>
                                    @foreach (Auth::user()->recoveryCodes() as $code)
                                        <li>{{ $code }}</li>
                                    @endforeach
                                </ul>

                                    @method('delete')
                                    <button class="btn btn-danger my-3" type="submit">disable</button>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- End Account Login Area -->m

</x-front-layout>
