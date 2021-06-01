<?php

declare(strict_types=1);
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

//include_once '../models/Test.php';
//include_once '../models/User.php';
//include_once '../models/Answer.php';

return function(App $app) {
    
    /*Views routes (twig templates) 
    will have twig middleware attached*/
    $container = $app->getContainer();

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