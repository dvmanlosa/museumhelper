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

$container['flash'] = function () {
    return new \Slim\Flash\Messages();
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

$app->get('/exhibit', function($req, $res) {
	$data = Contributor::readExhibitList(Utilities::getKey());
	return $this->view->render($res, "exhibit.twig", ['exhibits' => $data]);
})->setName("listExhibist");

$app->get('/exhibit/add', function($req, $res){
	return $this->view->render($res, "form/addexhibit.twig");
})->setName("addExhibit");

$app->post('/exhibit/add', function($req, $res, $args) {
	$bind = $req->getParsedBody();
	if(Contributor::addExhibit($bind)){
		return $res->withHeader('Location', '/exhibit');
	}else{
		
	}
});

$app->get('/contributor', function($req, $res) {
	$messages = $this->flash->getMessages();
	$data = Admin::readContributorList();
	return $this->view->render($res, "contributor.twig", ['contributors' => $data, 'messages' => $messages]);
})->setName("listContributor");

$app->get('/contributor/add', function($req, $res){
	return $this->view->render($res, "form/addcontributor.twig");
})->setName("addContributor");

$app->get('/contributor/deactivate[/[{id}]]', function($req, $res, $args){
	Admin::deactivateContributor($args['id']);
	$this->flash->addMessage('action', 'You have sucessfully deactivated contributor #'.$args['id'].'');
	return $res->withHeader('Location', '/contributor');
})->setName("deactivate");

$app->get('/contributor/activate[/[{id}]]', function($req, $res, $args){
	Admin::activateContributor($args['id']);
	$this->flash->addMessage('action', 'You have sucessfully activated contributor #'.$args['id'].'');
	return $res->withHeader('Location', '/contributor');
})->setName("activate");

$app->get('/logout', function($req, $res){
	Users::logout();
	$this->flash->addMessage('action', 'You have sucessfully logout');
	return $res->withHeader('Location', '/');
})->setName("logoutUser");

$app->run();