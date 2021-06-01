<?php

/* - Middleware initialization - 
This file returns a single function,
which attaches all middleware functions to the main $app object,
given as a parameter.
*/

declare(strict_types=1);
use Slim\App;

return function(App $app) {
    //addErrorMiddleware(displayErrorDetails, logError, logErrorDetails)
    $errorMiddleware = $app->addErrorMiddleware(true,true,false);
    //$errorMiddleware->setDefaultErrorHandler($errorHandler);
    // Parse json, form data and xml
    $app->addBodyParsingMiddleware();
};