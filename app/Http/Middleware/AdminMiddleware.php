<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 管理者権限(users.is_admin = 1)を持つユーザーしかアクセス不可にする
        $user = Auth::user();
        if ($user->is_admin === 1) {
            return $next($request);
        } else {
            abort(404);
        }
    }
}
