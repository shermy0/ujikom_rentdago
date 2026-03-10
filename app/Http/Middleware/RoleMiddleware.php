<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login');
        }

        $user = Auth::user();

        // 2. Seller punya mode (seller / customer)
        if ($user->role === 'seller') {
            $currentMode = session('account_mode', 'seller');

            if (in_array('seller', $roles) && $currentMode !== 'seller') {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Silakan switch ke mode Seller.'], 403);
                }
                return redirect()->back()
                    ->with('error', 'Silakan switch ke mode Seller.');
            }

            if (in_array('customer', $roles) && $currentMode !== 'customer') {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Silakan switch ke mode Customer.'], 403);
                }
                return redirect()->back()
                    ->with('error', 'Silakan switch ke mode Customer.');
            }

            return $next($request);
        }

        // 3. Role lain (admin, customer, dll)
        if (!in_array($user->role, $roles)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
