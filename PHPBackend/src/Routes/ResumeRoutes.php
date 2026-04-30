<?php

namespace App\Routes;

use Slim\App;
use App\Controllers\ResumeController;
use App\Middleware\AuthMiddleware;

class ResumeRoutes
{
    public static function register(App $app)
    {
        $app->group('/api/resume', function ($group) {
            $group->get('/', [ResumeController::class, 'start']);
            $group->post('/createResume', [ResumeController::class, 'createResume'])->add(new AuthMiddleware());
            $group->get('/getAllResume', [ResumeController::class, 'getAllResumes'])->add(new AuthMiddleware());
            $group->get('/getResume', [ResumeController::class, 'getResume'])->add(new AuthMiddleware());
            $group->put('/updateResume', [ResumeController::class, 'updateResume'])->add(new AuthMiddleware());
            $group->delete('/removeResume', [ResumeController::class, 'removeResume'])->add(new AuthMiddleware());
        });
    }
}
