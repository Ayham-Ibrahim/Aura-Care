<?php

namespace App\Http\Middleware;

use App\Models\Center\Center;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCenterIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $data = $next($request);
        $center = Center::where('phone', $request->phone)->first();
        if ($center && !$center->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'حسابك غير مفعل. يرجى التواصل مع الدعم لتفعيل الحساب.',
            ], 403);
        }

        return $data;
    }
}
