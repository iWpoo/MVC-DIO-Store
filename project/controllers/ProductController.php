<?php

namespace App\Project\Controllers;

use App\Core\Controller;
use App\Project\Requests\Request;
use App\Project\Models\Favorite;
use App\Project\Models\Cart;
use App\Project\Services\AuthService;
use App\Project\Services\CartService;
use App\Project\Services\ProductService;
	
class ProductController extends Controller
{
    protected $authService;
    protected $cartService;
    protected $productService;

    public function __construct()
    {
        $this->authService = new AuthService();
        $this->cartService = new CartService();
        $this->productService = new ProductService();
    }

    public function showProducts()
    {
        $auth = $this->authService->verifyAuth();

        return $this->render('products/products.twig', [
            'bestsellers' => $this->productService->getBestsellers(),
            'new_products' => $this->productService->getNewProducts(),
            'auth' => $auth,
            'cart_qty' => $_COOKIE['cart_qty'] ?? null
        ]);
    }

    public function showProduct($params)
    {
        $auth = $this->authService->verifyAuth();
        $product = $this->productService->getProduct($params['id']);

        $favorite = new Favorite();
        $cart = new Cart();

        return $this->render('products/product.twig', [
            'product' => $product,
            'product_images' => $this->productService->images($params['id']),
            'similar_products' => $this->productService->getSimilarProducts($product['category_id'], $product['subcategory_id']),
            'favorite' => $auth ? $favorite->findSpecific("WHERE user_id = :user_id AND product_id = :product_id", [':user_id' => $auth['id'], ':product_id' => $params['id']]) : false,
            'cart' => $auth ? $cart->findSpecific("WHERE user_id = :user_id AND product_id = :product_id", [':user_id' => $auth['id'], ':product_id' => $params['id']]) : false,
            'cart_local' => $this->cartService->getCartByCookie($params['id']),
            'ch' => $this->productService->getChar($params['id']),
            'modification' => $this->productService->modification($product['title'] ?? null),
            'csrf_token' => $this->generateCsrfToken(),
            'auth' => $auth,
            'cart_qty' => $_COOKIE['cart_qty'] ?? null
        ]);
    }
}