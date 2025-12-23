<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Support\Facades\Auth;

class LoginResponse implements LoginResponseContract
{
    /**
     * Handle the response after a user logins.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toResponse($request)
    {
        if (Auth::check() && ! Auth::user()->hasVerifiedEmail() && Auth::user()->authority === 'general') {
            return redirect('/email/verify');
        }

        if (Auth::check() && Auth::user()->hasVerifiedEmail() && Auth::user()->authority === 'general') {
            return redirect('attendance');
        }
    }
}