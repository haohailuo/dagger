<?php

class User
{
	function __construct()
	{
		echo 'this is user __construct<br>';
	}

	public function index()
	{
		echo 'this is user/index page<br>';
	}

	public function profile($name)
	{
		echo 'this is user/profile page<br>';
		echo "username:\t".$name.'<br>';
	}

	public function update()
	{
		echo 'this is user/update page<br>';
	}
}

require __DIR__ .'/../Router.php';

use Dagger\Router;

$router = new Router();

$router->get('/', function(){
	echo 'This is home page<br>';
});
$router->get('/user/', 'User@index');
$router->post('/user/', 'User@update');
$router->get('/user/(:all)', 'User@profile');

$router->dispatch();