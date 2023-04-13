<?php

namespace App\Project\Controllers;

use App\Core\Controller;
use App\Project\Services\AuthService;
	
class StaticPagesController extends Controller
{
    protected $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }
    
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