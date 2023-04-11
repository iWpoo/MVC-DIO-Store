<?php

use App\Core\Route;
	
$routes = [
    // Auth
	new Route('/register', 'user', 'register'),
	new Route('/register/verification', 'user', 'registerVerification'),
	new Route('/login', 'user', 'login'),

	// Logout
	new Route('/logout', 'user', 'logout'),
	
	// Forgot Password
	new Route('/password/reset', 'passwordReset', 'sendResetToken'),
	new Route('/change/password/reset/:token', 'passwordReset', 'changePass'),

	// Profile
	new Route('/profile', 'profile', 'profile'),
	new Route('/edit/profile', 'profile', 'editProfile'),
	new Route('/change/password', 'profile', 'changePassword'),
	new Route('/delete/profile', 'profile', 'deleteProfile'),
	new Route('/profile/favorites', 'favorite', 'favorites'),
	new Route('/profile/active/orders', 'profile', 'activeOrders'),
	new Route('/profile/history/orders', 'profile', 'historyOrders'),

	// Products
	new Route('/', 'product', 'showProducts'),
	new Route('/product/:id', 'product', 'showProduct'),

	// Favorites
	new Route('/product/favorite/:id', 'favorite', 'favorite'),

	// Carts
	new Route('/product/cart/:id', 'cart', 'cart'),
	new Route('/cart', 'cart', 'showCart'),
	new Route('/cart/delete-selected', 'cart', 'deleteSelectedCart'),
	new Route('/cart/change/amount/price', 'cart', 'changeAmountPrice'),

	// Categories
	new Route('/categories', 'staticPages', 'categories'),
	new Route('/mobile/categories', 'staticPages', 'phoneCategories'),
	new Route('/search/category/:category', 'search', 'searchByCategory'),
	new Route('/search/subcategory/:subcategory', 'search', 'searchBySubcategory'),

	// Search
	new Route('/search/:q', 'search', 'search'),

	// Order
	new Route('/order', 'order', 'order'),
	new Route('/order/make', 'order', 'makeOrder'),
	new Route('/order/:id', 'order', 'buy'),
	new Route('/order/make/:id', 'order', 'makeOrderOne'),
	new Route('/order/complete/:id', 'order', 'completeOrder'),

	// Static pages
	new Route('/about-us', 'staticPages', 'aboutUs'),
	new Route('/contacts', 'staticPages', 'contacts')
];