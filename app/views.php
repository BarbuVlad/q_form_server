<?php

/* - Twig templates -
This file attaches a Twig middleware to the 
main $app object
*/

declare(strict_types=1);

use Slim\App;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Twig\Loader\FilesystemLoader;

return function (App $app) {
    $container = $app->getContainer();

    $container->set('view', function() use ($container) {

        $settings = $container->get('settings')['views'];
        $loader = new FilesystemLoader($settings['path']);

        //return a Twig object with the views settings from the app settings
        return new Twig($loader, $settings['settings']);
    });

    //set a new key for the $app copntainer; value of key is TwigMiddleware obj
    $container->set('viewMiddleware', function() use ($app, $container){
        return new TwigMiddleware($container->get('view'), 
            $app->getRouteCollector()->getRouteParser());

    });


};
