<x-front-layout title="2fa challenge">


    <!-- Start Account Login Area -->
    <div class="account-login section">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 offset-lg-3 col-md-10 offset-md-1 col-12">

                    <form class="card login-form" action="{{ route('two-factor.login') }}" method="post">
                        @csrf
                            
                        <div class="card-body">
                            <div class="title">
                                <h3>2fa challenge</h3>
                                <p>must enter 2fa code</p>
                            </div>

                            <div class="alt-option">
                                <span>Or</span>
                            </div>
                            <div class="form-group input-group">
                                <label for="reg-fn">2FA Code</label>
                                <input class="form-control" type="text" name="code" id="reg-pass" >
                            </div>
                            <div class="form-group input-group">
                                <label for="reg-fn">recovery Code</label>
                                <input class="form-control" type="text" name="recovery_code" id="reg-pass" >
                            </div>
                            <div class="button">
                                <button class="btn" type="submit">Submit</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- End Account Login Area -->

</x-front-layout>
