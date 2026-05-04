<?php

use Slim\Factory\AppFactory;
use Dotenv\Dotenv;
use Illuminate\Database\Capsule\Manager as Capsule;
use App\Routes\UserRoutes;
use App\Routes\ResumeRoutes;

require __DIR__ . '/../vendor/autoload.php';

// Load Environment Variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Setup Database (Eloquent)
$capsule = new Capsule;
$capsule->addConnection([
    'driver'    => $_ENV['DB_CONNECTION'] ?? 'mysql',
    'host'      => $_ENV['DB_HOST'] ?? '127.0.0.1',
    'port'      => $_ENV['DB_PORT'] ?? '3306',
    'database'  => $_ENV['DB_DATABASE'] ?? 'resume_builder',
    'username'  => $_ENV['DB_USERNAME'] ?? 'root',
    'password'  => $_ENV['DB_PASSWORD'] ?? '',
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

// Create App
$app = AppFactory::create();

// Add Body Parsing Middleware
$app->addBodyParsingMiddleware();

// Add Routing Middleware
$app->addRoutingMiddleware();

// Add Error Middleware
$app->addErrorMiddleware(true, true, true);

// Register Routes
UserRoutes::register($app);
ResumeRoutes::register($app);

$app->run();
