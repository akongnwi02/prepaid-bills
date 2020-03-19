<?php
/**
 * Created by PhpStorm.
 * User: devert
 * Date: 3/18/20
 * Time: 10:40 AM
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\UnauthorizedException;

class WhitelistMiddleware
{
    /**
     * @param Request $request
     * @param Closure $next
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next)
    {
        
        $whitelist = config('access.whitelist');
        
        $ipAddresses = explode(';', $whitelist);
        
        if (! in_array($request->ip(), $ipAddresses)) {
            
            Log::error('IP address is not whitelisted', ['ip address', $request->ip()]);
            
            if (config('access.partner_restriction')) {
                
                throw new UnauthorizedException('IP address not whitelisted', 401);
                
            }
            
        }
        
        return $next($request);
    }
}
