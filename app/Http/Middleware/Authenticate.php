<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class Authenticate extends BaseMiddleware {

    use ApiResponser;

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     *
     */
    public function handle(Request $request, Closure $next): mixed {
        try {
            $this->authenticate($request);
        } catch (UnauthorizedHttpException $e) {
            return $this->error('Unauthorized', $e->getMessage(), 401);
        }
        return $next($request);
    }
}
