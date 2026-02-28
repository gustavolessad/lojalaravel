<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $customer = Auth::guard('customer')->user();

        $recentOrders  = $customer->orders()->latest()->limit(3)->get();
        $totalOrders   = $customer->orders()->count();
        $addressCount  = $customer->addresses()->count();

        return view('account.dashboard', compact('customer', 'recentOrders', 'totalOrders', 'addressCount'));
    }
}
