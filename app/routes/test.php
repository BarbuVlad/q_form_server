<?php

declare(strict_types=1);
use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use Psr\Http\Message\ServerRequestInterface as Req;
use Psr\Http\Message\ResponseInterface as Resp;

include_once '../models/Test.php';
include_once '../models/User.php';
include_once '../models/Answer.php';

return function(App $app) {
    //$container = $app->getContainer();
    $app->group('/test', function(RouteCollectorProxy $test){
        /*In this group we define operations regarding a single instance of a test:
        create, read etc.*/
        $test->post('/create', function($request, $response, $args){
            /*Insert a new entry in users table */
            $data = $request->getParsedBody();
            //Catch some errors
            if(!$data["payload"] || !$data["creator_id"]){
                $err = json_encode(array('ERROR' => 'payload or id missing',
                                        'code' => '1'));
                $response->withStatus(400);
                $response->getBody()->write($err);
                return $response;
            }
            //instanciate an test object
            $test_ = new Test($this->get('connection'));

            $test_->setCreator_id($data["creator_id"]);
            $test_->setPayload($data["payload"]);

            $code = $test_->create();

            switch($code){
                case 0:///< success
                    $ok = json_encode(array('message' => 'test created successfully!',
                    'code' => '0'));
                    $response->withStatus(200);
                    $response->getBody()->write($ok);
                    return $response;

                case 1: ///< pdo error
                    $err = json_encode(array('ERROR' => 'failed at creating test!',
                    'code' => '2'));
                    $response->withStatus(503);
                    $response->getBody()->write($err);
                    return $response;

                case 2: ///< unkonwn error
                    $response->withStatus(500);
                    $response->getBody()->write("Unkonwn error occurred!");
                    return $response;
            }
        });

        $test->get('/getTestToSolve/{testId}', function($request, $response, $args){
        /*Read from DB the the payload of test */
        $testId = $args['testId'];
        // Instantiate a table object
        $test_ = new Test($this->get('connection'));

        $test_->setId($args['testId']);
        $code = $test_->read_single_by_id(false,false,false,true);///< read only payload

        switch($code){
            case 0: ///< success
                $data["payload"] = $test_->getPayload() ? $test_->getPayload() : "";
                $data["payload"] = str_replace(",\"corect\":true", "", $data["payload"]);///< anti-theft measure
                $data["code"] = $code;
                $response->withStatus(200);
                $response->getBody()->write(json_encode($data));
                return $response;

            case 1: ///< fail with PDO err
                $err = json_encode(array('ERROR' => 'failed at reading test data!',
                'code' => '1'));
                $response->withStatus(503);
                $response->getBody()->write($err);
                return $response;

            case 2: ///< fail unknown
                $response->withStatus(500);
                $response->getBody()->write("Unkonwn error occurred!");
                return $response;
        }
    });

    $test->get('/getTestAnswers/{testId}', function($request, $response, $args){
        /*Read from DB the all answers to this test*/
        //$testId = $args['testId'];
        // Instantiate a table object
        $answer_ = new Answer($this->get('connection'));

        $answer_->setId_test($args['testId']);
        $data = $answer_->read_all_by_test_user_id(true, true, true, true, true, true,false,true);///<

        if (gettype($data) == "integer"){///< error
            switch($data){
                case 1: ///< pdo error
                    $err = json_encode(array('ERROR' => 'failed at reading test data!',
                    'code' => '1'));
                    $response->withStatus(503);
                    $response->getBody()->write($err);
                    return $response;
                case 2: ///< unknown error
                    $response->withStatus(500);
                    $response->getBody()->write("Unkonwn error occurred!");
                    return $response;
            }    
        }

        //Success:
        $response->withStatus(200);
        $response->getBody()->write(json_encode($data));
        return $response;
    });

        /*
        $test->post('/test/login', function($request, $response, $args){


        });
        */
    });

};