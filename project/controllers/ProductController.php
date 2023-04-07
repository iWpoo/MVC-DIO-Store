<?php

namespace App\Project\Controllers;
use App\Core\Controller;
use App\Project\Requests\Request;
use App\Project\Models\User;
use App\Project\Models\Product;
use App\Project\Models\Favorite;
use App\Project\Models\Cart;
use App\Project\Models\Characteristic;
use App\Project\Models\Order;
	
class ProductController extends Controller
{
    public function showProducts()
    {
        $user = new User();
        $auth = $user->verifyAuth();

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
        $user = new User();
        $auth = $user->verifyAuth();

        $productModel = new Product();
        $product = $productModel->getProduct($params['id']);

        $favorite = new Favorite();
        $cart = new Cart();
        $ch = new Characteristic();

        return $this->render('products/product.twig', [
            'product' => $product,
            'product_images' => $productModel->images($params['id']),
            'similar_products' => $productModel->getSimilarProducts($product['category_id'], $product['subcategory_id']),
            'favorite' => $auth ? $favorite->findSpecific("WHERE user_id = :user_id AND product_id = :product_id", [':user_id' => $auth['id'], ':product_id' => $params['id']]) : false,
            'cart' => $auth ? $cart->findSpecific("WHERE user_id = :user_id AND product_id = :product_id", [':user_id' => $auth['id'], ':product_id' => $params['id']]) : false,
            'cart_local' => $cart->getCartByCookie($params['id']),
            'ch' => $ch->getChar($params['id']),
            'modification' => $productModel->modification($product['title'] ?? null),
            'csrf_token' => $this->generateCsrfToken(),
            'auth' => $auth,
            'cart_qty' => $_COOKIE['cart_qty'] ?? null
        ]);
    }

    public function favorite($params)
    {
        $user = new User();
        $auth = $user->verifyAuth();
        $favorite = new Favorite();
        
        $validator = [
            Request::validateCsrfToken()
        ];
        
        if (Request::validate($validator)) {
            // Удаление или добавление товара в избранное
            $favorite->modifyFavorite($auth['id'], $params['id']);
            return json_encode(['csrf_token' => $this->generateCsrfToken()]);
        }
    }
}