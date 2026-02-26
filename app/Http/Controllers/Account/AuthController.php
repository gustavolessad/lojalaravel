<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\LoginRequest;
use App\Http\Requests\Account\RegisterRequest;
use App\Models\Customer;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('account.auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');

        if (! Auth::guard('customer')->attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'E-mail ou senha incorretos.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        // Funde o carrinho anônimo com o carrinho do cliente logado
        app(CartService::class)->mergeSessionIntoCustomer(Auth::guard('customer')->id());

        return redirect()->intended(route('account.dashboard'));
    }

    public function showRegister(): View
    {
        return view('account.auth.register');
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['status'] = 'active';

        $customer = Customer::create($data);

        Auth::guard('customer')->login($customer);

        $request->session()->regenerate();

        // Funde o carrinho anônimo com o carrinho do novo cliente
        app(CartService::class)->mergeSessionIntoCustomer($customer->id);

        return redirect()->route('account.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('customer')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('account.login');
    }
}
