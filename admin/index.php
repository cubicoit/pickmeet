<?php

ini_set('display_errors', true);
error_reporting(-1);

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header('Access-Control-Allow-Origin: *');

/*
// Include the Composer autoloader
if(!defined('PROJECT_ROOT')){
	session_start();
	header('Access-Control-Allow-Origin: *');  
	include './vendor/autoload.php';
}
*/
//die(__FILE__);
require 'vendor/autoload.php';
require 'constants.php';

// Auth stuff
//require 'vendor/slim/slim/Slim/Middleware.php';
//require 'vendor/slim/extras/Slim/Extras/Middleware/HttpBasicAuth.php';

// New Slim app
$app = new \Slim\Slim();

// Add auth
//$app->add(new \HttpBasicAuth());
//use \Slim\Extras\Middleware\HttpBasicAuth;
//$app->add(new HttpBasicAuth('admin', 'password')); // basic http auth

//CORS compatibility
$app->options('/{routes:.+}', function ($request, $response, $args) {
	return $response;
});



// configurazione

$app->config('debug', true);
$mode = (__FILE__ == 'I:\Lavori\_easyphp\www\pickmeet\admin\index.php') ? "development" : "production";
$app->config('mode', $mode);
/*
$app->config('log.level', \Slim\Log::DEBUG);
$app->config('log.enabled', true);
$app->config('log.writer', new Slim\Extras\Log\DateTimeFileWriter(
	array(
		'path' => __DIR__ . '/logs',
		'name_format' => 'y-m-d'
	)
));*/

// Only invoked if mode is "production"
$app->configureMode('production', function () use ($app) {
	$app->config(array(
		'log.enable' => true,
		'debug' => true,
		'personaggio' => "sniper wolf producer",
		"username" => "mlpickme_admin",
		"db_password" => "H5Z6+I.l6H%U",
		"db_dbname" => "mlpickme_webserver",
		"db_host" => "localhost",
		"URL_API" => "pickmeet.net/admin/"
	));
});

// Only invoked if mode is "development"
$app->configureMode('development', function () use ($app) {
	$app->config(array(
		'log.enable' => false,
		'debug' => true,
		'personaggio' => "paperino developer",
		"username" => "root",
		"db_password" => "",
		"db_dbname" => "pickmeet",
		"db_host" => "localhost",
		"URL_API" => "http://127.0.0.1:81/pickmeet/admin/"
	));
});

//$app->config('templates.path', './templates');

//require_once dirname(__FILE__).'/main.php';

$app->get('/hello/:name', function ($name) {
	echo "Hello, $name";
});





include 'db.php';

// add new Route
$app->get("/", function ()  use ($app)  {
	//echo "<h1>Hello Slim World</h1>";
	echo $app->config("personaggio") . "<br>";



	//home

	if ( getUserID()) {
		echo "user loggato";
	} else{
		echo "user non loggato";
	}
});


$app->run();




//$log = $app->getLog();

//echo '<br/>' . __DIR__;
//echo '<br/>' . dirname(__FILE__);


//echo $app->config('debug');

//http://127.0.0.1:81/r&d/slim/index.php/hello/ciccio

