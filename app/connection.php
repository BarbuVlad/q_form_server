<?php
/* - Database connection -
This file attaches a database conn middleware to the 
main $app object, by setting into it's container a
PDO connection
*/

declare(strict_types=1);

use DI\Container;

return function (Container $container) {
    $container->set('connection', function() use ($container) {
        $connection = $container->get('settings')['connection'];

        $host = $connection['host'];
        $dbname = $connection['dbname'];
        $dbuser = $connection['dbuser'];
        $dbpass = $connection['dbpass'];

        try {
            $connection = new PDO ('mysql:host=' . $host . ';dbname=' . $dbname, $dbuser, $dbpass);
            $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
          } catch (PDOException $e) { echo 'Error at connection. ' . $e->getMessage();}

          return $connection;
    });


};