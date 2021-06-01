<?php

declare(strict_types=1);
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

include_once '../models/Test.php';
//include_once '../models/User.php';
//include_once '../models/Answer.php';

return function(App $app) {
    
    //$container = $app->getContainer();

    $app->group('/user', function(RouteCollectorProxy $userRoute){
        /*In this group we define operations regarding a single instance of a user:
        register, login etc.*/
        $userRoute->post('/register', function($request, $response, $args){
            /*Insert a new entry in users table */
            $data = $request->getParsedBody();
            //Catch some errors
            if(!$data["name"] || !$data["password"]){
                $err = json_encode(array('ERROR' => 'name or password missing',
                                          'code' => '1'));
                $response->withStatus(400)->getBody()->write($err);
                return $response;
            }
            //Instanciate a user
            $user = new User($this->get('connection'));

            $user->setName($data["name"]);
            $user->setPassword($data["password"]);

            $code = $user->create();

            switch($code){
                case 0:
                    $ok = json_encode(array('message' => 'user created successfully!',
                    'code' => '0'));
                    $response->withStatus(200);
                    $response->getBody()->write($ok);
                    return $response;

                case 1: ///< mysql duplicate key
                    $err = json_encode(array('ERROR' => 'username taken!',
                    'code' => '2'));
                    $response->withStatus(400);
                    $response->getBody()->write($err);
                    return $response;
                
                case 2: ///< pdo error
                    $err = json_encode(array('ERROR' => 'failed at registering user!',
                    'code' => '3'));
                    $response->withStatus(503);
                    $response->getBody()->write($err);
                    return $response;

                case 3: ///< unknown error
                    $response->withStatus(500);
                    $response->getBody()->write("Unkonwn error occurred!");
                    return $response;
            }
        });

        $userRoute->post('/login', function($request, $response, $args){
            /*Read from DB the user, match the password */
            $data = $request->getParsedBody();
            //Catch some errors
            if(!$data["name"] || !$data["password"]){
                $err = json_encode(array('ERROR' => 'name or password missing',
                                          'code' => '1'));
                $response->withStatus(400)->getBody()->write($err);
                return $response;
            }

            $user = new User($this->get('connection'));
            $user->setName($data["name"]);
            //read from DB
            $code = $user->read_single_by_id_or_name(true,false,true,false); ///< 0, 1, 2
            $valid_password = $user->validatePassword($data["password"]);    ///< -1, 0, 1

            switch($code){
                case 0: ///< successfull reading from DB
                    if($valid_password == 0){
                        $ok = json_encode(array('message' => 'user login successfully!',
                        'code' => '0',
                        'id' => $user->getId()));
                        $response->withStatus(200);
                        $response->getBody()->write($ok);
                        return $response;
                    }
                    else {
                        $nok = json_encode(array('ERROR' => 'wrong credentials!',
                        'code' => '2'));
                        $response = $response->withStatus(400);
                        $response->getBody()->write($nok);
                        return $response;
                    }
                
                case 1: ///< pdo error
                    $err = json_encode(array('ERROR' => 'failed at login!',
                    'code' => '3'));
                    $response->withStatus(503);
                    $response->getBody()->write($err);
                    return $response;

                case 2: ///< unknown error
                    $response->withStatus(500);
                    $response->getBody()->write("Unkonwn error occurred!");
                    return $response;

            }
        });

        $userRoute->get('/getSolvedTests/{userId}', function($request, $response, $args){
            /*Read from DB the user test data */
            $userId = $args['userId'];

            //create the sql command
            $sql = "SELECT id AS id_answer, id_test, date FROM `answers` WHERE id_user = :id_user;";
            $stmt = $this->get('connection')->prepare($sql); ///<prepare statement
            //validate data
            //bind data
            $stmt->bindParam(':id_user', $userId);
            
            //execute
            try{
               if($stmt->execute()){ ///<execute statement
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $response->withStatus(200)->getBody()->write(json_encode($data));
                return $response;
                }
            }catch(PDOException $pdo_err){
                $err = json_encode(array('ERROR' => 'failed at reading user solved tests!',
                'code' => '1'));
                $response->withStatus(503)->getBody()->write($err);
                return $response;
            }
            $response->getBody()->withStatus(500)->write("Unkonwn error occurred!");
            return $response;
        });

        $userRoute->get('/getCreatedTests/{userId}', function($request, $response, $args){
            /*Read from DB the user test data */
            $userId = $args['userId'];

            //create the sql command
            $sql = "SELECT id AS id_test, created_date FROM `test` WHERE creator_id = :creator_id;";
            $stmt = $this->get('connection')->prepare($sql); ///<prepare statement
            //validate data
            //bind data
            $stmt->bindParam(':creator_id', $userId);
            
            //execute
            try{
               if($stmt->execute()){ ///<execute statement
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $response->withStatus(200)->getBody()->write(json_encode($data));
                return $response;
                }
            }catch(PDOException $pdo_err){
                $err = json_encode(array('ERROR' => 'failed at reading user solved tests!',
                'code' => '1'));
                $response->withStatus(503)->getBody()->write($err);
                return $response;
            }
            $response->getBody()->withStatus(500)->write("Unkonwn error occurred!");
            return $response;
        });
    });

};