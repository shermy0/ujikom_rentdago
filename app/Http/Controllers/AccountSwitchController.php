<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountSwitchController extends Controller
{
    /**
     * Switch account dari seller ke customer atau sebaliknya
     */
    public function switch(Request $request)
    {
        $user = Auth::user();

        // Hanya seller yang bisa switch
        if ($user->role !== 'seller') {
            return back()->with('error', 'Hanya seller yang bisa switch account.');
        }

        // Toggle mode: jika sedang seller mode, switch ke customer mode
        // Simpan di session untuk tracking mode saat ini
        $currentMode = session('account_mode', 'seller'); // Default seller

        if ($currentMode === 'seller') {
            // Switch ke customer mode
            session(['account_mode' => 'customer']);
            return redirect('/')->with('success', 'Berhasil switch ke mode Customer');
        } else {
            // Switch ke seller mode
            session(['account_mode' => 'seller']);
            return redirect()->route('seller.dashboard.index')->with('success', 'Berhasil switch ke mode Seller');
        }
    }

    /**
     * Get current account mode
     */
    public function currentMode()
    {
        $user = Auth::user();
        
        if ($user->role !== 'seller') {
            return 'customer'; // Customer tidak bisa switch
        }

        return session('account_mode', 'seller'); // Default seller untuk yang role seller
    }
}