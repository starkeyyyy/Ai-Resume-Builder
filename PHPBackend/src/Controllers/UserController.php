<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\User;
use App\Utils\ApiResponse;
use App\Utils\ApiError;
use Firebase\JWT\JWT;

class UserController
{
    public function start(Request $request, Response $response)
    {
        $user = $request->getAttribute('user');
        if ($user) {
            $apiResponse = new ApiResponse(200, $user, "User Found");
            $response->getBody()->write(json_encode($apiResponse->toArray()));
            return $response->withHeader('Content-Type', 'application/json');
        } else {
            $apiError = new ApiError(404, "User Not Found");
            $response->getBody()->write(json_encode($apiError->toArray()));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }
    }

    public function register(Request $request, Response $response)
    {
        $data = $request->getParsedBody();
        $fullName = $data['fullName'] ?? null;
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        if (!$fullName || !$email || !$password) {
            $apiError = new ApiError(400, "Please provide all required fields: fullName, email, and password.");
            $response->getBody()->write(json_encode($apiError->toArray()));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            $existingUser = User::where('email', $email)->first();
            if ($existingUser) {
                $apiError = new ApiError(409, "User already registered.");
                $response->getBody()->write(json_encode($apiError->toArray()));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(409);
            }

            $user = User::create([
                'fullName' => $fullName,
                'email' => $email,
                'password' => $password
            ]);

            $apiResponse = new ApiResponse(201, [
                'user' => [
                    'id' => $user->id,
                    'fullName' => $user->fullName,
                    'email' => $user->email,
                ]
            ], "User successfully registered.");
            $response->getBody()->write(json_encode($apiResponse->toArray()));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
        } catch (\Exception $e) {
            $apiError = new ApiError(500, "Internal Server Error.", [], $e->getMessage());
            $response->getBody()->write(json_encode($apiError->toArray()));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    public function login(Request $request, Response $response)
    {
        $data = $request->getParsedBody();
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        if (!$email || !$password) {
            $apiError = new ApiError(400, "Please provide all required fields: email and password.");
            $response->getBody()->write(json_encode($apiError->toArray()));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            $user = User::where('email', $email)->first();
            if (!$user || !$user->comparePassword($password)) {
                $apiError = new ApiError(406, "Invalid credentials.");
                $response->getBody()->write(json_encode($apiError->toArray()));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(406);
            }

            $payload = [
                'id' => $user->id,
                'iat' => time(),
                'exp' => time() + (int)$_ENV['JWT_SECRET_EXPIRES_IN']
            ];

            $jwtToken = JWT::encode($payload, $_ENV['JWT_SECRET_KEY'], 'HS256');

            $apiResponse = new ApiResponse(200, [
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'fullName' => $user->fullName,
                ]
            ], "User logged in successfully.");

            $response->getBody()->write(json_encode($apiResponse->toArray()));
            
            // Set cookie (manually or via a library)
            $cookie = "token=" . $jwtToken . "; HttpOnly; Max-Age=" . $_ENV['JWT_SECRET_EXPIRES_IN'] . "; Path=/; SameSite=Lax";
            
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withHeader('Set-Cookie', $cookie)
                ->withStatus(200);

        } catch (\Exception $e) {
            $apiError = new ApiError(500, "Internal Server Error", [], $e->getMessage());
            $response->getBody()->write(json_encode($apiError->toArray()));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    public function logout(Request $request, Response $response)
    {
        $apiResponse = new ApiResponse(200, null, "User logged out successfully.");
        $response->getBody()->write(json_encode($apiResponse->toArray()));
        
        $cookie = "token=; HttpOnly; Max-Age=0; Path=/; SameSite=Lax";
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Set-Cookie', $cookie)
            ->withStatus(200);
    }
}
