<?php

namespace App\Core;

require_once __DIR__ . '/vendor/autoload.php';

session_start();

error_reporting(E_ALL);
ini_set('display_errors', 'on');

require_once $_SERVER['DOCUMENT_ROOT'] . '/database/connection.php';
	
$routes = require $_SERVER['DOCUMENT_ROOT'] . '/project/routes/routes.php';

$track = ( new Router ) -> getTrack($routes, $_SERVER['REQUEST_URI']);
echo $page  = ( new Dispatcher ) -> getPage($track);