<?php

declare(strict_types=1);

use DI\Container;

return function (Container $container){
    $container->set('settings', function() {
        return [
            "name" => "q_form server",

            //set settings for twig templates
            "views" => [
                'path' => __DIR__ . '/../src/Views',
                'settings' => ['cache' => false],
            ],
            /*database settings, mysql and sqlite as alternative (local VM hosted)*/
            'connection' => [
                "host" => "localhost",
                "dbname" => "q_form_db",
                "dbuser" => "pcuser",
                "dbpass" => "102938"
            ],

            'connection_sqlite' => [
                //"host" => "localhost",
                "dbname" => "/home/pcuser/q_form_db.db",
            ]

        ];
    });

};
