<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
/**
 * @OA\OpenApi(
 *     @OA\Info(
 *         version="1.0.0",
 *         title="SportReserve",
 *         @OA\License(name="MIT"),
 *         @OA\Contact(
 *             email="derex@outlook.com.br"
 *         )
 *     ),
 *     @OA\Components(
 *         @OA\SecurityScheme(
 *         securityScheme="bearerAuth",
 *         type="http",
 *             scheme="Bearer",
 *         ),
 *         @OA\Attachable
 *     )
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
