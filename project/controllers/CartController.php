<?php

namespace App\Project\Controllers;

use App\Core\Controller;
use App\Project\Requests\Request;
use App\Project\Models\Product;
use App\Project\Models\Cart;
	
class CartController extends BaseController
{
    public function cart($params)
    {
        $auth = $this->authService->verifyAuth();
        
        $validator = [
            Request::validateCsrfToken()
        ];
        
        if (Request::validate($validator)) {
            if (!$auth) {
                $cart_qty = $this->cartService->modifyCartCookie($params['id']);

                // Отправка данных на клиент в формате JSON
                $data = ['csrf_token' => $this->generateCsrfToken(), 'countCart' => $cart_qty];
                return json_encode($data);
            }
            // Удаление или добавление товара из БД (корзинки) если клиент авторизован
            $price = $this->add('price');
            $this->cartService->modifyCart($auth['id'], $params['id'], $price);
            $cart_qty = $this->cartService->getCountCartProducts($auth['id']);

            // Отправка данных на клиент в формате JSON
            setcookie('cart_qty', $cart_qty, $cart_qty == 0 ? time() - 86400 * 30 : time() + 86400 * 30, '/');            
	        $data = ['csrf_token' => $this->generateCsrfToken(), 'countCart' => $cart_qty];
            return json_encode($data);
        }
    }

    public function showCart()
    {
        $auth = $this->authService->verifyAuth();

        // Вывод товаров в корзину из куки если пользователь не авторизован
        $products = '';
        if (isset($_COOKIE['cart'])) {
            $productModel = new Product();

            $cartCookie = unserialize($_COOKIE['cart']);
            $carts = implode(' OR id = ', $cartCookie);
            $products = $productModel->findAll("WHERE id = $carts");
        }

        // Вывод данные о товарах если авторизован
        $productsCart = $auth ? $this->cartService->joinProductCart($auth['id']) : null;

        return $this->render('products/cart.twig', [
            'products' => $auth ? $productsCart : $products,
            'csrf_token' => $this->generateCsrfToken(),
            'auth' => $auth,
            'cart_qty' => $_COOKIE['cart_qty'] ?? null
        ]);
    }

    public function changeAmountPrice()
    {
        $auth = $this->authService->verifyAuth();
        $cart = new Cart();

        // Запрос на обновление кол-во товара в корзинке
        if (isset($_POST['counter']) and $auth) {
            $cart_id = $this->add('cart_id');
            $cart->update($cart_id, 'id', [
                'count' => $this->add('counter'),
                'price' => $this->add('price') * $this->add('counter')
            ]); 

            // Отправка данных на клиент в формате JSON
            $data = ['csrf_token' => $this->generateCsrfToken()];
            return json_encode($data);
        }
    }

    public function deleteSelectedCart()
    {
        $auth = $this->authService->verifyAuth();

        $deleteCart = $_POST['delete_cart'] ?? false;

        // Удаление выбранных товаров из куки (корзинки) если клиент не авторизован
        if (!$auth) {
            $cartCookie = unserialize($_COOKIE['cart']);
            $cartCookie = array_diff($cartCookie, $deleteCart);
            $cart_qty = count($cartCookie);
            if (!empty($cartCookie)) {
                setcookie('cart', serialize($cartCookie), time() + 86400 * 30, "/");
                setcookie('cart_qty', $cart_qty, time() + 86400 * 30, '/');
                $data = ['csrf_token' => $this->generateCsrfToken(), 'countCart' => $cart_qty];
                return json_encode($data);            
            } 
            setcookie('cart', '', time() - 86400 * 30, "/");
            setcookie('cart_qty', '', time() - 86400 * 30, '/');
            $data = ['csrf_token' => $this->generateCsrfToken(), 'countCart' => ''];
            return json_encode($data);        
        }

        // Удаление выбранных товаров из БД (корзинки) если клиент авторизован
        if ($deleteCart) {
            $this->cartService->deleteSelected($deleteCart, $auth['id']);
            $cart_qty = $this->cartService->getCountCartProducts($auth['id']);
            setcookie('cart_qty', $cart_qty, $cart_qty == 0 ? time() - 86400 * 30 : time() + 86400 * 30, '/');

            $data = ['csrf_token' => $this->generateCsrfToken(), 'countCart' => $cart_qty];
            return json_encode($data);
        }    
    }
}
