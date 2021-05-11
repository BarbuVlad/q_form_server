<?php
declare(strict_types=1);

//Use objects
use Slim\Factory\AppFactory;
use Psr\Http\Message\ServerRequestInterface as Req;
use Psr\Http\Message\ResponseInterface as Resp;

use DI\Container; ///<dependency injection container

// autoload packages of composer
require __DIR__ . '/../vendor/autoload.php';



//set the container for class AppFactory 
$container = new Container();
$settings = require __DIR__ . '/../app/settings.php'; 
$settings($container); ///<initialize container with settings
AppFactory::setContainer($container);

//initialize container with database connection
$connection = require __DIR__ . '/../app/connection.php';
$connection($container);

$app = AppFactory::create();

/*Middleware initialization */
$middleware = require __DIR__ . '/../app/middleware.php';
$middleware($app);

//Set views into app (twig middleware)
$views = require __DIR__ . '/../app/views.php';
$views($app);


/*Routes definition */
$routes = require __DIR__ . '/../app/routes.php';
$routes($app);

$app->get("/", function(Req $request, Resp $response, array $args){
    $name = $args['name'];
    $response->getBody()->write("<h2>Server is running</h2>");
   return $response;
});


//Run app
$app->run();