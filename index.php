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
	if(isset($_SESSION['user']['type']) && !empty($_SESSION['user']['type'])){
		$this->flash->addMessage('error', 'You are already logged in!');
		switch($_SESSION['user']['type']){
			case 'contributor':
				return $res->withHeader('Location', '/exhibit');
				break;
			case 'admin':
				return $res->withHeader('Location', '/contributor');
				break;
			default:
				break;
		}
	}
	$messages = $this->flash->getMessages();
	return $this->view->render($res, "home.twig", ['messages' => $messages]);
})->setName("homePage");

$app->post('/', function($req, $res, $args) {
	$bind = $req->getParsedBody();
	if(Users::login($bind)){
		if($_SESSION['user']['type'] == 'admin'){
			return $res->withHeader('Location', '/contributor');
		}else{
			return $res->withHeader('Location', '/exhibit');
		}
	}else{
		$this->flash->addMessage('error', $_SESSION['error']);
		return $res->withHeader('Location', '/');
		unset($_SESSION['error']);
	}
});

$app->get('/exhibit', function($req, $res) {
	if(empty($_SESSION['user']) && !isset($_SESSION['user'])){
		$this->flash->addMessage('error', 'Error 403! Forbidden access!');
		return $res->withHeader('Location', '/');
	}else if($_SESSION['user']['type'] != 'contributor'){
		var_dump($_SESSION['user']);
		$this->flash->addMessage('error', 'Error 403! Forbidden access!');
		return $res->withHeader('Location', '/contributor');
	}else{
		$messages = $this->flash->getMessages();
		$data = Contributor::readExhibitList(Utilities::getKey());
		return $this->view->render($res, "exhibit.twig", ['exhibits' => $data, 'messages' => $messages]);
	}
})->setName("listExhibist");

$app->get('/exhibit/add', function($req, $res){
	if($_SESSION['user']['type'] != 'contributor'){
		var_dump($_SESSION['user']);
		$this->flash->addMessage('error', 'Error 403! Forbidden access!');
		return $res->withHeader('Location', '/contributor');
	}
	$messages = $this->flash->getMessages();
	return $this->view->render($res, "form/addexhibit.twig", ['messages' => $messages]);
})->setName("addExhibit");

$app->post('/exhibit/add', function($req, $res, $args) {
	$bind = $req->getParsedBody();
	if(Contributor::addExhibit($bind)){
		$this->flash->addMessage('success', 'Notice! You have sucessfully added a new exhibit!');
		return $res->withHeader('Location', '/exhibit');
	}else{
		$this->flash->addMessage('error', 'Error! An error has occured!');
		return $res->withHeader('Location', '/exhibit/add');
	}
});

$app->get('/exhibit/delete[/[{id}]]', function($req, $res, $args){
	if($_SESSION['user']['type'] != 'contributor'){
		var_dump($_SESSION['user']);
		$this->flash->addMessage('error', 'Error 403! Forbidden access!');
		return $res->withHeader('Location', '/contributor');
	}
	Contributor::deleteExhibit($args['id']);
	$this->flash->addMessage('success', 'Notice! You have sucessfully deleted exhibit #'.$args['id'].'');
	return $res->withHeader('Location', '/exhibit');
})->setName("deleteExhibit");

$app->get('/exhibit/update[/[{id}]]', function($req, $res, $args){
	if($_SESSION['user']['type'] != 'contributor'){
		var_dump($_SESSION['user']);
		$this->flash->addMessage('error', 'Error 403! Forbidden access!');
		return $res->withHeader('Location', '/contributor');
	}
	$messages = $this->flash->getMessages();
	$data = Contributor::readExhibit($args['id']);
	return $this->view->render($res, "form/updateexhibit.twig", ['messages' => $messages, 'data' => $data]);
})->setName("updateExhibit");

$app->post('/exhibit/update[/[{id}]]', function($req, $res, $args) {
	$bind = $req->getParsedBody();
	if(Contributor::updateExhibit($bind)){
		$this->flash->addMessage('success', "Notice! You have sucessfully updated exhibit #" . $bind[':id']);
		return $res->withHeader('Location', '/exhibit');
	}else{
		$this->flash->addMessage('error', 'Error! File extensions not accepted');
		return $res->withHeader('Location', '/exhibit/update/'.$bind[':id']);
	}
});

$app->get('/contributor', function($req, $res) {
	if(empty($_SESSION['user']) && !isset($_SESSION['user'])){
		$this->flash->addMessage('error', 'Error 403! Forbidden access!');
		return $res->withHeader('Location', '/');
	}else if($_SESSION['user']['type'] != 'admin'){
		var_dump($_SESSION['user']);
		$this->flash->addMessage('error', 'Error 403! Forbidden access!');
		return $res->withHeader('Location', '/exhibit');
	}else{
		$messages = $this->flash->getMessages();
		$data = Admin::readContributorList();
		echo $_SESSION['user']['type'];
		return $this->view->render($res, "contributor.twig", ['contributors' => $data, 'messages' => $messages, 'session' => $_SESSION]);
	}
})->setName("listContributor");

$app->get('/contributor/add', function($req, $res){
	if($_SESSION['user']['type'] != 'admin'){
		var_dump($_SESSION['user']);
		$this->flash->addMessage('error', 'Error 403! Forbidden access!');
		return $res->withHeader('Location', '/exhibit');
	}
	$messages = $this->flash->getMessages();
	return $this->view->render($res, "form/addcontributor.twig", ['messages' => $messages]);
})->setName("addContributor");

$app->post('/contributor/add', function($req, $res, $args) {
	$bind = $req->getParsedBody();
	if(Admin::addUser($bind)){
		$this->flash->addMessage('success', 'Notice! You have sucessfully added a new contributor!');
		return $res->withHeader('Location', '/contributor');
	}else{
		$this->flash->addMessage('error', 'Error! An error has occured!');
		var_dump(Admin::addUser($bind));
	}
});

$app->get('/contributor/deactivate[/[{id}]]', function($req, $res, $args){
	if($_SESSION['user']['type'] != 'admin'){
		var_dump($_SESSION['user']);
		$this->flash->addMessage('error', 'Error 403! Forbidden access!');
		return $res->withHeader('Location', '/exhibit');
	}
	Admin::deactivateContributor($args['id']);
	$this->flash->addMessage('success', 'Notice! You have sucessfully deactivated contributor #'.$args['id'].'');
	return $res->withHeader('Location', '/contributor');
})->setName("deactivate");

$app->get('/contributor/activate[/[{id}]]', function($req, $res, $args){
	if($_SESSION['user']['type'] != 'admin'){
		var_dump($_SESSION['user']);
		$this->flash->addMessage('error', 'Error 403! Forbidden access!');
		return $res->withHeader('Location', '/exhibit');
	}
	Admin::activateContributor($args['id']);
	$this->flash->addMessage('success', 'Notice! You have sucessfully activated contributor #'.$args['id'].'');
	return $res->withHeader('Location', '/contributor');
})->setName("activate");

$app->get('/logout', function($req, $res){
	if(!isset($_SESSION['user']['id']) || empty($_SESSION['user']['id'])){
		$this->flash->addMessage('error', "Error! You're already logged out!");
		return $res->withHeader('Location', '/');
	}
	Users::logout();
	$this->flash->addMessage('success', 'You have sucessfully logout');
	return $res->withHeader('Location', "/");
})->setName("logoutUser");
		
$app->run();