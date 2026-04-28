<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        if ($request->user()->role->nama_role !== 'admin') {
            return response()->json([
                'message' => 'Akses ditolak. Admin only.'
            ], 403);
        }

        return $next($request);
    }
}