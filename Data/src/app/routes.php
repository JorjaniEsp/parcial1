<?php
namespace App\controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

$app->group('/api', function(RouteCollectorProxy $api){
    $api->group('/libros', function(RouteCollectorProxy $endpoint){

        $endpoint->get('[/{id}]', Libro::class . ':read');
        $endpoint->put('/{id}', Libro::class . ':update');
        $endpoint->delete('/{id}', Libro::class . ':delete');
        $endpoint->post('', Libro::class . ':create');
    });
});