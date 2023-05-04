<?php

namespace App\Project\Controllers;

use App\Core\Controller;
	
class StaticPagesController extends BaseController
{
    public function aboutUs()
    {
        return $this->render('about-us.twig', ['auth' => $this->authService->verifyAuth(), 'cart_qty' => $_COOKIE['cart_qty'] ?? null]);
    }

    public function contacts()
    {
        return $this->render('contacts.twig', ['auth' => $this->authService->verifyAuth(), 'cart_qty' => $_COOKIE['cart_qty'] ?? null]);
    }

    public function categories()
    {
        return $this->render('products/categories.twig', ['auth' => $this->authService->verifyAuth(), 'cart_qty' => $_COOKIE['cart_qty'] ?? null]);
    }

    public function phoneCategories()
    {
        return $this->render('products/categories_phone.twig', ['auth' => $this->authService->verifyAuth(), 'cart_qty' => $_COOKIE['cart_qty'] ?? null]);
    }
}