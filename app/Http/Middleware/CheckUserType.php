<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserType
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next,...$types): Response
    {
        $user = $request->user();

        if(!$user){
            return redirect()->route('login');
        }
        if(in_array($user->type, $types)){
            return redirect()->route('home')->with('danger','لا يمكنك الدخول على لوحة التحكم، لوحة التحكم خاصة بالمسؤول فقط');
        }
        // if($user->type == 'user'){
        //     abort(403);
        // }
        return $next($request);
    }
}
