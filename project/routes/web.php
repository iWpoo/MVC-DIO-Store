<?php

use App\Core\Route;
	
$routes = [
    // Auth
	new Route('/register', 'user', 'register'),
	new Route('/login', 'user', 'login'),
	new Route('/', 'user', 'index'),
	
	// Password Reset
	new Route('/password/reset', 'passwordReset', 'sendResetCode'),
	new Route('/change/password/reset/:token', 'passwordReset', 'changePass'),
];