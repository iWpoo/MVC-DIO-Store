<?php

use App\Core\Route;
	
$routes = [
    // Auth
	new Route('/register', 'user', 'register'),
	new Route('/register/verification', 'user', 'registerVerification'),
	new Route('/login', 'user', 'login'),

	// Logout
	new Route('/logout', 'user', 'logout'),
	new Route('/', 'user', 'index'),
	
	// Forgot Password
	new Route('/password/reset', 'passwordReset', 'sendResetToken'),
	new Route('/change/password/reset/:token', 'passwordReset', 'changePass'),

	// Profile
	new Route('/profile', 'profile', 'profile'),
	new Route('/edit/profile', 'profile', 'editProfile'),
	new Route('/change/password', 'profile', 'changePassword'),
	new Route('/delete/profile', 'profile', 'deleteProfile'),
	new Route('/profile/favorites', 'profile', 'favorites'),
	new Route('/profile/active/orders', 'profile', 'activeOrders'),

	// Products
	new Route('/product/:id', 'product', 'showProduct'),

	// Favorites
	new Route('/product/favorite/:id', 'product', 'favorite'),

	// Carts
	new Route('/product/cart/:id', 'cart', 'cart'),
	new Route('/cart', 'cart', 'showCart'),
	new Route('/cart/delete-selected', 'cart', 'deleteSelectedCart'),

	// Order
	new Route('/order', 'order', 'order'),
	new Route('/order/make', 'order', 'makeOrder'),
	new Route('/order/:id', 'order', 'buy'),
	new Route('/order/make/:id', 'order', 'makeOrderOne'),
];