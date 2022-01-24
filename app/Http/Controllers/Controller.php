<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponser;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController {
    use ApiResponser;

    public function respondWithToken($token): \Illuminate\Http\JsonResponse {
        return response()->json([
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ], 200);
    }
}
