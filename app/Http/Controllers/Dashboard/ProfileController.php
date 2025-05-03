<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Languages;

class ProfileController extends Controller
{
    public function edit(){
        $user = Auth::guard('admin')->user();
        // dd(Languages::getNames());
        return view('dashboard.profile.edit',[
            'user' => $user,
            'countries'=> Countries::getNames(),
            'locales'=> Languages::getNames()
        ]);
    }

    public function update(Request $request){
        // dd($request->all());
        $request->validate([
            'first_name'=>['required','string','max:255'],
            'last_name'=>['required','string','max:255'],
            'birthday'=>['nullable','date','before:today'],
            'gender'=>['in:male,female'],
            'country'=>['required','string','size:2'],
        ]);

        $user = Auth::guard('admin')->user();
        // dd($user->profile);
        $user->profile->fill($request->all())->save();
        return redirect()->route('dashboard.profile.edit')->with('success','Profile Update');
    }
}
