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
	return $this->view->render($res, "home.twig");
});

$app->post('/', function($req, $res, $args) {
	$bind = $req->getParsedBody();
	if(Users::login($bind)){
		if($_SESSION['user']['type'] == 'admin'){
			return $res->withHeader('Location', '/contributor');
		}else{
			return $res->withHeader('Location', '/exhibit');
		}
	}
});

$app->get('/contributor', function($req, $res) {
	return $this->view->render($res, "contributor.twig");
});

$app->get('/exhibit', function($req, $res) {
	return $this->view->render($res, "exhibit.twig");
});


$app->run();