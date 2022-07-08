<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class BaseMiddleware extends \PHPOpenSourceSaver\JWTAuth\Http\Middleware\BaseMiddleware {

    public function checkForRight(Request $request, $service, $right = null): void {
        $this->authenticate($request);

        if(!isset($this->auth->getClaim('acc')[$service])) {
            throw new UnauthorizedHttpException('jwt-auth', 'User doesn\'t have the right to access this service');
        }

        if($right && !in_array($right, $this->auth->getClaim('acc')[$service], true)) {
            throw new UnauthorizedHttpException('jwt-auth', 'User doesn\'t have the specified right.');
        }
    }
}
