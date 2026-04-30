<?php

namespace App\Routes;

use Slim\App;
use App\Controllers\UserController;
use App\Middleware\AuthMiddleware;

class UserRoutes
{
    public static function register(App $app)
    {
        $app->group('/api/user', function ($group) {
            $group->get('/', [UserController::class, 'start'])->add(new AuthMiddleware());
            $group->post('/register', [UserController::class, 'register']);
            $group->post('/login', [UserController::class, 'login']);
            $group->get('/logout', [UserController::class, 'logout'])->add(new AuthMiddleware());
        });
    }
}
