<?php

declare(strict_types=1);
use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use Psr\Http\Message\ServerRequestInterface as Req;
use Psr\Http\Message\ResponseInterface as Resp;

//include_once '../models/Test.php';
//include_once '../models/User.php';
include_once '../models/Answer.php';

return function(App $app) {
    
    //$container = $app->getContainer();

    $app->group('/answer', function(RouteCollectorProxy $answer){
        /*In this group we define operations regarding a single instance of a answer:
        create, read etc.*/
        $answer->post('/create', function($request, $response, $args){
            /*Insert a new entry in users table */
            $data = $request->getParsedBody();
            //Catch some errors
            if(!$data["answers"] || !$data["id_test"] || !$data["id_user"]){
                $err = json_encode(array('ERROR' => 'answers, id_test or id_user missing',
                                        'code' => '1'));
                $response->withStatus(400);
                $response->getBody()->write($err);
                return $response;
            }

            //Instanciate a new answer object
            $answer_ = new Answer($this->get('connection'));
            //set data
            $answer_->setId_user($data["id_user"]);
            $answer_->setId_test($data["id_test"]);
            $answer_->setAnswers($data["answers"]);
            //execute command
            $code = $answer_->create();
            switch($code){
                case 0:///< success
                    $ok = json_encode(array('message' => 'answer created successfully!',
                    'code' => '0'));
                    $response->withStatus(200);
                    $response->getBody()->write($ok);
                    return $response;
                case 1:///< pdo error
                    $err = json_encode(array('ERROR' => 'failed at creating answer!',
                    'code' => '2'));
                    $response->withStatus(503);
                    $response->getBody()->write($err);
                    return $response;

                case 2:///< unknown error
                    $response->withStatus(500);
                    $response->getBody()->write("Unkonwn error occurred!");
                    return $response;


            }
        });
    });

};