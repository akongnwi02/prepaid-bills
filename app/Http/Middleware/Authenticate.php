<?php

namespace App\Http\Middleware;

use App\Exceptions\UnAuthorizationException;
use Closure;

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     * @throws UnAuthorizationException
     */
    public function handle($request, Closure $next)
    {
        if ($request->header('x-api-key') != config('app.api_key')) {
            throw new UnAuthorizationException('Unauthorized', 401);
        }
        return $next($request);
    }
}
