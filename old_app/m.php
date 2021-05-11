<?php

declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

use Psr\Http\Message\ResponseInterface as ResponseInterface;// returned by class invoke

use Slim\Psr7\Response;// for the function type of middleware

require __DIR__ . "/../vendor/autoload.php";

class AfterMiddleware{
    public function __invoke(Request $request, RequestHandler $handler) : ResponseInterface
    {
        $response = $handler->handle($request);
        $response->getBody()->write("After");
        return $response;
    }


}

$beforeMiddleware = function (Request $request, RequestHandler $handler) {
    $response = $handler->handle($request);
    $existingContent = (string) $response->getBody();

    $response = new Response();
    $response->getBody()->write('BEFORE' . $existingContent);

    return $response;
};

/*With invoke:
    use App\Application\Middleware\AfterMiddleware; // if under App\...

    $middleware = new AfterMiddleware;
    $middleware($request, $handler);
    //OR
    $app->add(AfterMiddleware::class)
*/

/* with funnction:
$app->add($beforeMiddleware);*/

