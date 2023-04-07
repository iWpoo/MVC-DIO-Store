<?php

namespace App\Project\Controllers;
use App\Core\Controller;
use App\Project\Requests\Request;
use App\Project\Models\User;
use App\Project\Models\Product;
use App\Project\Models\Cart;
use App\Project\Models\Order;
	
class CartController extends Controller
{
    public function cart($params)
    {
        $user = new User();
        $auth = $user->verifyAuth();
        $cart = new Cart();
        $product = new Product();
        
        $validator = [
            Request::validateCsrfToken()
        ];
        
        if (Request::validate($validator)) {
            // Удаление или добавление товара из куки (корзинки) если клиент не авторизован
            if (!$auth) {
                $cart_qty = $cart->modifyCartCookie($params['id']);
                $data = ['csrf_token' => $this->generateCsrfToken(), 'countCart' => $cart_qty];
                return json_encode($data);
            }

            // Удаление или добавление товара из БД (корзинки) если клиент авторизован
            $price = $product->getProduct($params['id']);
            $cart->modifyCart($auth['id'], $params['id'], $price['price']);
            
            $cart_qty = $cart->getCountCartProducts($auth['id']);
            setcookie('cart_qty', $cart_qty, $cart_qty == 0 ? time() - 86400 * 30 : time() + 86400 * 30, '/');
        
            // Отправка данных на клиент в формате JSON
            $data = ['csrf_token' => $this->generateCsrfToken(), 'countCart' => $cart_qty];
            return json_encode($data);
        }
    }

    public function showCart()
    {
        $user = new User();
        $auth = $user->verifyAuth();

        $cart = new Cart();
        $productModel = new Product();

        // Вывод товаров в корзину из куки
        $products = '';
        if (isset($_COOKIE['cart'])) {
            $cartCookie = unserialize($_COOKIE['cart']);
            $carts = implode(' OR id = ', $cartCookie);
            $products = $productModel->findAll("WHERE id = $carts");
        }

        // Вывод данные о товарах
        $productsCart = $auth ? $cart->joinProductCart($auth['id']) : null;

        return $this->render('products/cart.twig', [
            'products' => $auth ? $productsCart : $products,
            'csrf_token' => $this->generateCsrfToken(),
            'auth' => $auth,
            'cart_qty' => $_COOKIE['cart_qty'] ?? null
        ]);
    }

    public function changeAmountPrice()
    {
        $user = new User();
        $auth = $user->verifyAuth();
        $cart = new Cart();

        // Запрос на обновление кол-во товара в корзинке
        if (isset($_POST['counter']) and $auth) {
            $idCart = $cart->findSpecific("WHERE user_id = :user_id AND product_id = :product_id", [':user_id' => $auth['id'], ':product_id' => $_POST['product_id']]);
            $cart->update($idCart['id'], 'id', [
                'count' => $_POST['counter'],
                'price' => $_POST['price'] * $_POST['counter']
            ]); 

            // Отправка данных на клиент в формате JSON
            $data = ['csrf_token' => $this->generateCsrfToken()];
            return json_encode($data);
        }
    }

    public function deleteSelectedCart()
    {
        $user = new User();
        $auth = $user->verifyAuth();

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
        $cart = new Cart();
        if ($deleteCart) {
            $cart->deleteSelected($deleteCart, $auth['id']);
            $cart_qty = $cart->getCountCartProducts($auth['id']);
            setcookie('cart_qty', $cart_qty, $cart_qty == 0 ? time() - 86400 * 30 : time() + 86400 * 30, '/');

            $data = ['csrf_token' => $this->generateCsrfToken(), 'countCart' => $cart_qty];
            return json_encode($data);
        }    
    }
}