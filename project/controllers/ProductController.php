<?php

namespace App\Project\Controllers;

use App\Core\Controller;
use App\Project\Requests\Request;
use App\Project\Models\Product;
use App\Project\Models\Cart;
use App\Project\Models\Favorite;
use App\Project\Services\AuthService;
	
class ProductController extends Controller
{
    protected $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function showProducts()
    {
        $auth = $this->authService->verifyAuth();
        $products = new Product();

        return $this->render('products/products.twig', [
            'bestsellers' => $products->getBestsellers(),
            'new_products' => $products->getNewProducts(),
            'auth' => $auth,
            'cart_qty' => $_COOKIE['cart_qty'] ?? null
        ]);
    }

    public function showProduct($params)
    {
        $auth = $this->authService->verifyAuth();

        $productModel = new Product();
        $product = $productModel->getProduct($params['id']);

        $favorite = new Favorite;
        $cart = new Cart();

        return $this->render('products/product.twig', [
            'product' => $product,
            'product_images' => $productModel->images($params['id']),
            'similar_products' => $productModel->getSimilarProducts($product['category_id'], $product['subcategory_id']),
            'favorite' => $auth ? $favorite->findSpecific("WHERE user_id = :user_id AND product_id = :product_id", [':user_id' => $auth['id'], ':product_id' => $params['id']]) : false,
            'cart' => $auth ? $cart->findSpecific("WHERE user_id = :user_id AND product_id = :product_id", [':user_id' => $auth['id'], ':product_id' => $params['id']]) : false,
            'cart_local' => $cart->getCartByCookie($params['id']),
            'ch' => $productModel->getChar($params['id']),
            'modification' => $productModel->modification($product['title'] ?? null),
            'csrf_token' => $this->generateCsrfToken(),
            'auth' => $auth,
            'cart_qty' => $_COOKIE['cart_qty'] ?? null
        ]);
    }
}