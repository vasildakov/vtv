<?php

declare(strict_types=1);

use Mezzio\Application;
use Mezzio\MiddlewareFactory;
use Psr\Container\ContainerInterface;

return static function (Application $app, MiddlewareFactory $factory, ContainerInterface $container): void {
    $app->get('/', App\Handler\HomePageHandler::class, 'api.home');
    $app->get('/api/ping', App\Handler\PingHandler::class, 'api.ping');


    $app->get('/wage-agreement[/:id]', App\Handler\GetWageAgreement::class, 'api.get.wage.agreement');
    $app->put('/wage-agreement[/:id]', App\Handler\PutWageAgreement::class, 'api.put.wage.agreement');

};
