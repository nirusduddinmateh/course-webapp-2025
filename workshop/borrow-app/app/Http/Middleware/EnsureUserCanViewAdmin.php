<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserCanViewAdmin
{
    public function handle(Request $request, Closure $next)
    {
        // ถ้ายังไม่ล็อกอิน ให้ไปหน้า login ของ Filament
        if (! auth()->check()) {
            return redirect()->route('filament.admin.auth.login'); // ชื่อ route ของ panel "admin"
        }

        if (!auth()->check() || !auth()->user()->can('view admin')) {
            abort(403, 'คุณไม่มีสิทธิ์เข้าหน้านี้');
        }
        return $next($request);
    }
}

