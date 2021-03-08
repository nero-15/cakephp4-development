<?php

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

$routes->setRouteClass(DashedRoute::class);

$routes->scope('/', function (RouteBuilder $builder) {
	$builder->connect('/', ['controller' => 'Pages', 'action' => 'display', 'home']);
	$builder->connect('/pages/*', 'Pages::display');

	$builder->scope('/articles', function (RouteBuilder $builder) {
		$builder->connect('/tagged/*', ['controller' => 'Articles', 'action' => 'tags']);
	});

	$builder->fallbacks();
});
