<?php

declare(strict_types=1);
use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use Psr\Http\Message\ServerRequestInterface as Req;
use Psr\Http\Message\ResponseInterface as Resp;

return function(App $app) {

    $app->get("/{name}", function(Req $request, Resp $response, array $args){
        $name = $args['name'];
        $response->getBody()->write("<h2>Hello $name</h2>");
       return $response;
    });

    /*Views routes (twig templates) 
    will have twig middleware attached*/
    $container = $app->getContainer();

    $app->group('/user', function(RouteCollectorProxy $user){
        /*In this group we define operations regarding a single instance of a user:
        register, login etc.*/
        $user->post('/register', function($request, $response, $args){
            /*Insert a new entry in users table */
            $data = $request->getParsedBody();
            //Catch some errors
            if(!$data["name"] || !$data["password"]){
                $err = json_encode(array('ERROR' => 'name or password missing',
                                          'code' => '1'));
                $response->withStatus(400)->getBody()->write($err);
                return $response;
            }
            //create the sql command
            $sql = "INSERT INTO `user`(name, password) VALUES (:name, :password)";
            $stmt = $this->get('connection')->prepare($sql); ///<prepare statement

            //validate data
            $data["password"]=password_hash($data['password'], PASSWORD_BCRYPT);///< password must be crypted

            //bind data
            $stmt->bindParam(':name', $data["name"]);
            $stmt->bindParam(':password', $data["password"]);

            try{
                if($stmt->execute()){///<execute statement
                    $ok = json_encode(array('message' => 'user created successfully!',
                    'code' => '0'));
                    $response->withStatus(200)->getBody()->write($ok);
                    return $response;
                } 

            }catch(PDOException $pdo_err){
                if($stmt->errorInfo()[1] == '1062'){ // duplicate key
                    $err = json_encode(array('ERROR' => 'username taken!',
                    'code' => '2'));
                    $response->withStatus(400)->getBody()->write($err);
                    return $response;
                } else {
                    $err = json_encode(array('ERROR' => 'failed at registering user!',
                    'code' => '3'));
                    $response->withStatus(503)->getBody()->write($err);
                    return $response;
                }

            }
           // $html = var_export($data, true);
            $response->getBody()->withStatus(500)->write("Unkonwn error occurred!");
            return $response;

        });

        $user->post('/login', function($request, $response, $args){
            /*Read from DB the user, match the password */
            $data = $request->getParsedBody();
            //Catch some errors
            if(!$data["name"] || !$data["password"]){
                $err = json_encode(array('ERROR' => 'name or password missing',
                                          'code' => '1'));
                $response->withStatus(400)->getBody()->write($err);
                return $response;
            }

            //create the sql command
            $sql = "SELECT password, id FROM `user` WHERE name = :name LIMIT 0, 1";
            $stmt = $this->get('connection')->prepare($sql); ///<prepare statement
            //validate data
            //bind data
            $stmt->bindParam(':name', $data["name"]);
            
            //execute
            try{
               if($stmt->execute()){ ///<execute statement
                $x = $stmt->fetch(PDO::FETCH_ASSOC);
                $pass=$x["password"];
                $id=$x["id"];
                if($pass==null){goto x;}

                if(password_verify($data["password"],$pass)){///< password matched to hash
                    $ok = json_encode(array('message' => 'user login successfully!',
                    'code' => '0',
                    'id' => $id));
                    $response->withStatus(200)->getBody()->write($ok);
                    return $response;
                } else { ///< password not matched to hash
                    x:
                    $nok = json_encode(array('ERROR' => 'wrong credentials!',
                    'code' => '2'));
                    $response = $response->withStatus(400);
                    $response->getBody()->write($nok);
                    return $response;
                }
            }
            }catch(PDOException $pdo_err){
                $err = json_encode(array('ERROR' => 'failed at login!',
                'code' => '3'));
                $response->withStatus(503)->getBody()->write($err);
                return $response;
            }

            $response->getBody()->withStatus(500)->write("Unkonwn error occurred!");
            return $response;
        });

        $user->get('/getSolvedTests/{userId}', function($request, $response, $args){
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

        $user->get('/getCreatedTests/{userId}', function($request, $response, $args){
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
                $response->withStatus(400)->getBody()->write($err);
                return $response;
            }

            //create the sql command
            $sql = "INSERT INTO `test`(creator_id, created_date, payload) VALUES (:creator_id, CURRENT_DATE, :payload);";
            $stmt = $this->get('connection')->prepare($sql); ///<prepare statement

            //validate data
            //bind data
            $stmt->bindParam(':creator_id', $data["creator_id"]);
            $stmt->bindParam(':payload', json_encode($data["payload"]));

            try{
                if($stmt->execute()){///<execute statement
                    $ok = json_encode(array('message' => 'test created successfully!',
                    'code' => '0'));
                    $response->withStatus(200)->getBody()->write($ok);
                    return $response;
                } 

            }catch(PDOException $pdo_err){
                $err = json_encode(array('ERROR' => 'failed at creating test!',
                'code' => '3'));
                $response->withStatus(503)->getBody()->write($err);
                return $response;
            }
            // $html = var_export($data, true);
            $response->getBody()->withStatus(500)->write("Unkonwn error occurred!");
            return $response;

        });

        $test->get('/getTestToSolve/{testId}', function($request, $response, $args){
            /*Read from DB the user test data */
            $testId = $args['testId'];

            //create the sql command
            $sql = "SELECT id AS id_test, payload FROM `test` WHERE id = :test_id LIMIT 0, 1;";
            $stmt = $this->get('connection')->prepare($sql); ///<prepare statement
            //validate data
            //bind data
            $stmt->bindParam(':test_id', $testId);
            //execute
            try{
               if($stmt->execute()){ ///<execute statement
                    $data = $stmt->fetch(PDO::FETCH_ASSOC);
                    //Clean data
                    $data["payload"] = str_replace(",\"corect\":true", "", $data["payload"]);///< anti-theft measure
                    $data["code"]='0';///< add success code
                    $response->withStatus(200)->getBody()->write(json_encode($data));
                    return $response;
                }
            }catch(PDOException $pdo_err){
                $err = json_encode(array('ERROR' => 'failed at reading test data!',
                'code' => '1'));
                $response->withStatus(503)->getBody()->write($err);
                return $response;
            }
            $response->getBody()->withStatus(500)->write("Unkonwn error occurred!");
            return $response;
        });

        /*
        $test->post('/test/login', function($request, $response, $args){


        });
        */
    });

    $app->group('/answer', function(RouteCollectorProxy $answer){
        /*In this group we define operations regarding a single instance of a test:
        create, read etc.*/
        $answer->post('/create', function($request, $response, $args){
            /*Insert a new entry in users table */
            $data = $request->getParsedBody();
            //Catch some errors
            if(!$data["answers"] || !$data["id_test"] || !$data["id_user"]){
                $err = json_encode(array('ERROR' => 'answers, id_test or id_user missing',
                                        'code' => '1'));
                $response->withStatus(400)->getBody()->write($err);
                return $response;
            }

            //create the sql command
            $sql = "INSERT INTO `answers`(id_user, id_test, date, answers) VALUES (:id_user, :id_test, CURRENT_DATE, :answers);";
            $stmt = $this->get('connection')->prepare($sql); ///<prepare statement

            //validate data
            //bind data
            $stmt->bindParam(':id_user', $data["id_user"]);
            $stmt->bindParam(':id_test', $data["id_test"]);
            $stmt->bindParam(':answers', json_encode($data["answers"]));

            try{
                if($stmt->execute()){///<execute statement
                    $ok = json_encode(array('message' => 'answer created successfully!',
                    'code' => '0'));
                    $response->withStatus(200)->getBody()->write($ok);
                    return $response;
                } 

            }catch(PDOException $pdo_err){
                $err = json_encode(array('ERROR' => 'failed at creating answer!',
                'code' => '2'));
                $response->withStatus(503)->getBody()->write($err);
                return $response;
            }
            // $html = var_export($data, true);
            $response->getBody()->withStatus(500)->write("Unkonwn error occurred!");
            return $response;

        });

    });

    $app->group('/views', function(RouteCollectorProxy $view){
        $view->get('/', function ($request, $response, $args){
             //$view = "index_view.twig";
            // $name = $args['name'];

            //$this references the container. Get the view = Twig obj
            return $this->get('view')
            ->render($response, 'index_view.twig', compact('name'));
        });

        $view->get('/users', function ($request, $response, $args){
            try{
                $sql = "SELECT id, name FROM user;";
                $stmt = $this->get('connection')->prepare($sql); ///<prepare statement
                $stmt->execute(); ///<execute statement
                $users = $stmt->fetchAll();

                return $this->get('view')
                ->render($response, 'users_view.twig', ['users'=>$users]);

            }catch(Exception $e) {
                $response->withStatus(500)->getBody()->write("<h2>500 SERVER ERROR</h2>");
                return $response;
            }
       });

       $view->get('/user/{id}', function ($request, $response, $args){
        $id = $args['id'];
        //validate data TODO
        $created=[];
        $solved=[];

        //Get created tests
        try{
            $sql = "SELECT id, created_date FROM `test` WHERE creator_id = ?;";
            
            $stmt = $this->get('connection')->prepare($sql); ///<prepare statement
            $stmt->bindParam(1, $id); ///<bind creator id
            $stmt->execute(); ///<execute statement
            $created = $stmt->fetchAll();
        }catch(Exception $e) {
            $response->withStatus(500)->getBody()->write("<h2>500 SERVER ERROR</h2>");
            return $response;
        }

        //Get solved tests
        try{
            $sql = "SELECT id_user, id_test, date FROM `answers` WHERE id_user = ?;";
            
            $stmt = $this->get('connection')->prepare($sql); ///<prepare statement
            $stmt->bindParam(1, $id); ///<bind creator id
            $stmt->execute(); ///<execute statement
            $solved = $stmt->fetchAll();
        }catch(Exception $e) {
            $response->withStatus(500)->getBody()->write("<h2>500 SERVER ERROR</h2>");
            return $response;
        }

        //Get user name
        try{
            $sql = "SELECT name FROM `user` WHERE id = ?;";
            
            $stmt = $this->get('connection')->prepare($sql); ///<prepare statement
            $stmt->bindParam(1, $id); ///<bind creator id
            $stmt->execute(); ///<execute statement
            $name = $stmt->fetch();
        }catch(Exception $e) {
            $response->withStatus(500)->getBody()->write("<h2>500 SERVER ERROR</h2>");
            return $response;
        }

        return $this->get('view')
        ->render($response, 'user_info.twig', ['created'=>$created,
                                                'solved'=>$solved,
                                                'name' => $name['name']]);
   });

    })->add($container->get('viewMiddleware'));

};