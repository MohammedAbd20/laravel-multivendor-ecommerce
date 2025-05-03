<?php

namespace App\Http\Controllers\Front\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TwoFAController extends Controller
{
    public function index() {
        return view('front.auth.2fa');
    }
}
