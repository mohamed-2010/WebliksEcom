<?php

namespace App\Http\Middleware;

use App\Enums\ResponseCodeEnum;
use App\Traits\ApiResponseTrait;
use Closure;
use Illuminate\Http\Request;


class AccessApiMiddleware
{
    use ApiResponseTrait;

    public function handle(Request $request, Closure $next)
    {
        // \Log::alert($request->headers->all());
        if ($request->header('authorization') !== config('setting.api_key')) {
            return $this->errorResponse('Wrong api key', ResponseCodeEnum::UNAUTHORIZED->value);
        }
        return $next($request);
    }
}
