<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '/vendor/autoload.php';
require_once 'core/init.php';

$app = new \Slim\App;

$container = $app->getContainer();

// Register Twig View helper and configure it
$container['view'] = function ($c) {
    //You can change this as you want
    $view = new \Slim\Views\Twig('templates', [
        'cache' => false //or specify a cache directory
    ]);

    // Instantiate and add Slim specific extension
    $view->addExtension(new Slim\Views\TwigExtension(
        $c['router'],
        $c['request']->getUri()
    ));

    return $view;
};

$app->get('/', function($req, $res, $args) {
	return $this->view->render($res, "main.twig");
});

$app->post('/', function($req, $res, $args) {
	$bind = $req->getParsedBody();
	if(Users::login($bind)){
		return $res->withHeader('Location', '/exhibit');
	}
});

$app->get('/', function($req, $res) {
	return $this->view->render($res, "exhibit.twig");
});

$app->run();