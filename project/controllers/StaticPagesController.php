<?php

namespace App\Project\Controllers;
use App\Core\Controller;
use App\Project\Models\User;
	
class StaticPagesController extends Controller
{
    public function aboutUs()
    {
        return $this->render('about-us.twig', ['auth' => (new User)->verifyAuth(), 'cart_qty' => $_COOKIE['cart_qty'] ?? null]);
    }

    public function contacts()
    {
        return $this->render('contacts.twig', ['auth' => (new User)->verifyAuth(), 'cart_qty' => $_COOKIE['cart_qty'] ?? null]);
    }

    public function categories()
    {
        return $this->render('products/categories.twig', ['auth' => (new User)->verifyAuth(), 'cart_qty' => $_COOKIE['cart_qty'] ?? null]);
    }

    public function phoneCategories()
    {
        return $this->render('products/categories_phone.twig', ['auth' => (new User)->verifyAuth(), 'cart_qty' => $_COOKIE['cart_qty'] ?? null]);
    }
}