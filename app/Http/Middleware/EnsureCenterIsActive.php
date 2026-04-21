<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureCenterIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $data = $next($request);
        $center = Auth::guard('center')->user();
        if ($center && !$center->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'حسابك غير مفعل. يرجى التواصل مع الدعم لتفعيل الحساب.',
            ], 403);
        }

        return $data;
    }
}
