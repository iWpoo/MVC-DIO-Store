<?php

namespace App\Project\Controllers;
use App\Core\Controller;
use App\Project\Requests\Request;
use App\Project\Models\User;
use App\Project\Models\Order;
use App\Project\Models\Product;
use App\Project\Models\Cart;
	
class OrderController extends Controller
{
    public function order()
    {
        $cart = new Cart();
        $userModel = new User();
        $auth = $userModel->verifyAuth();

        if (!$auth) {
            $this->redirect('/login');
        }

        // Вывод данных о пользователя
        $user = $userModel->find($auth['id']);

        return $this->render('products/order.twig', [
            'user' => $user,
            'products' => $cart->joinProductCart($auth['id']),
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }

    public function makeOrder()
    {
        $userModel = new User();
        $auth = $userModel->verifyAuth();

        $order = new Order();
        $cart = new Cart();

        $product_ids = $_POST['product_ids'];
        $productsCart = $cart->findAll("WHERE user_id = ".$auth['id']." AND product_id = " . implode(' OR product_id = ', $product_ids));       
        
        $orderNumber = mt_rand(1000, 9999) . mt_rand(10000, 99999);
        $payment_methods = [1 => 'Через курьера', 2 => 'Самовызов', 3 => 'Переводы', 4 => 'Криптовалютой'];
        $payment_method = $payment_methods[$this->add('payment_method')] ?? null;
        $prices = array_column($productsCart, 'price');
        $total_price = array_sum($prices);

        if ($payment_method === null) {
            return 'Выберите способ оплаты.';
        }

        $user = $userModel->find($auth['id']);
        $validator = [
            Request::validateRequired($user['address'], 'address_required'),
            Request::validateRequired($user['phone'], 'phone_required'),
            Request::validateRequired($payment_method, 'payment_required'),
            Request::validateCsrfToken()
        ];

        if (Request::validate($validator)) {
            $order->create([
                'user_id' => $auth['id'],
                'order_num' => $orderNumber,
                'payment_method' => $payment_method,
                'total_price' => $total_price,
                'address' => $user['address'],
                'created_at' => date('d M Y H:i:s')
            ]);
    
            $order_id = Order::getLink()->lastInsertId();
    
            foreach ($productsCart as $product) {
                $order->setOrdersProducts([
                    ':order_id' => $order_id,
                    ':product_id' => $product['product_id'],
                    ':quantity' => $product['count'],
                    ':price' => $product['price']
                ]);
                $cart->delete($product['id']);
            }

            return 'Заказ успешно оформлен!';
        }

        return "Заполните адрес доставки и контактную информацию.";
    }

    public function buy($params)
    {
        $productModel = new Product();
        $userModel = new User();
        $auth = $userModel->verifyAuth();

        if (!$auth) {
            $this->redirect('/login');
        }

        // Вывод данных о пользователя
        $user = $userModel->find($auth['id']);
        $product = $productModel->find($params['id']);

        return $this->render('products/orderOne.twig', [
            'user' => $user,
            'product' => $product,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }

    public function makeOrderOne($params)
    {
        $userModel = new User();
        $auth = $userModel->verifyAuth();

        $productModel = new Product();
        $product = $productModel->find($params['id']);
        
        $order = new Order();

        $orderNumber = mt_rand(1000, 9999) . mt_rand(10000, 99999);
        $payment_methods = [1 => 'Через курьера', 2 => 'Самовызов', 3 => 'Переводы', 4 => 'Криптовалютой'];
        $payment_method = $payment_methods[$this->add('payment_method')] ?? null;

        if ($payment_method === null) {
            return 'Выберите способ оплаты.';
        }

        $user = $userModel->find($auth['id']);
        $validator = [
            Request::validateRequired($user['address'], 'address_required'),
            Request::validateRequired($user['phone'], 'phone_required'),
            Request::validateRequired($payment_method, 'payment_required'),
            Request::validateCsrfToken()
        ];

        if (Request::validate($validator)) {
            $order->create([
                'user_id' => $auth['id'],
                'order_num' => $orderNumber,
                'payment_method' => $payment_method,
                'total_price' => $product['price'],
                'address' => $user['address'],
                'created_at' => date('d M Y H:i:s')
            ]);
    
            $order_id = Order::getLink()->lastInsertId();
            $order->setOrdersProducts([
                ':order_id' => $order_id,
                ':product_id' => $params['id'],
                ':quantity' => 1,
                ':price' => $product['price']
            ]);

            return 'Заказ успешно оформлен!';
        }

        return "Заполните адрес доставки и контактную информацию.";
    }
}