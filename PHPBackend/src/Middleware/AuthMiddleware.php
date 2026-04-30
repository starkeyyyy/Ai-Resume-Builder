<?php

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\User;
use App\Utils\ApiError;
use Slim\Psr7\Response as SlimResponse;

class AuthMiddleware
{
    public function __invoke(Request $request, Handler $handler): Response
    {
        $cookies = $request->getCookieParams();
        $token = $cookies['token'] ?? null;

        if (!$token) {
            $response = new SlimResponse();
            $apiError = new ApiError(401, "Unauthorized: No token provided");
            $response->getBody()->write(json_encode($apiError->toArray()));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        try {
            $decoded = JWT::decode($token, new Key($_ENV['JWT_SECRET_KEY'], 'HS256'));
            $user = User::find($decoded->id);

            if (!$user) {
                $response = new SlimResponse();
                $apiError = new ApiError(401, "Unauthorized: User not found");
                $response->getBody()->write(json_encode($apiError->toArray()));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
            }

            $request = $request->withAttribute('user', $user);
            return $handler->handle($request);
        } catch (\Exception $e) {
            $response = new SlimResponse();
            $apiError = new ApiError(401, "Unauthorized: Invalid token", [], $e->getMessage());
            $response->getBody()->write(json_encode($apiError->toArray()));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }
    }
}
