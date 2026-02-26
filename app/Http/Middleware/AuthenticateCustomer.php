<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateCustomer
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::guard('customer')->check()) {
            return redirect()->route('account.login')
                ->with('error', 'Faça login para acessar sua conta.');
        }

        if (Auth::guard('customer')->user()->status !== 'active') {
            Auth::guard('customer')->logout();

            return redirect()->route('account.login')
                ->withErrors(['email' => 'Sua conta está inativa. Entre em contato com o suporte.']);
        }

        return $next($request);
    }
}
