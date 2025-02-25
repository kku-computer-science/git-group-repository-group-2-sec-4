<?php

namespace App\Http\Middleware;

use Closure;
use App\Helpers\LogHelper;
use Illuminate\Http\Request;

class ErrorLogMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($response->status() >= 400) {
            LogHelper::logError($response->status(), "เกิดข้อผิดพลาดที่ " . $request->fullUrl());
        }

        return $response;
    }
}
