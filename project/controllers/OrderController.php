<?php

namespace App\Project\Controllers;
use App\Core\Controller;
use App\Project\Requests\Request;
use App\Project\Models\Order;
use App\Project\Models\Product;
use App\Project\Models\Cart;
use App\Project\Services\AuthService;
	
class OrderController extends Controller
{
    protected $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function order()
    {
        $order = new Order();
        $auth = $this->authService->verifyAuth();

        if (!$auth) {
            $this->redirect('/login');
        }

        return $this->render('products/order.twig', [
            'products' => $order->joinProductCart($auth['id']),
            'csrf_token' => $this->generateCsrfToken(),
            'auth' => $auth,
            'cart_qty' => $_COOKIE['cart_qty'] ?? null
        ]);
    }

    public function makeOrder()
    {
        $auth = $this->authService->verifyAuth();

        if (!$auth) {
            $this->redirect('/login');
        }

        $order = new Order();
        $db = Order::getLink();
        $cart = new Cart();
       
        // Если id заказанных товаров будет не определено
        if (!isset($_POST['product_ids']) or !is_array($_POST['product_ids'])) {
            return 'Выберите товары для покупки.';
        }

        // Вывод товаров из корзинки пользователя
        $product_ids = $_POST['product_ids'] ?? null;
        $productsCart = $cart->findAll("WHERE user_id = ".$auth['id']." AND product_id = " . implode(' OR product_id = ', $product_ids));

        $orderNumber = mt_rand(1000, 9999) . mt_rand(10000, 99999);
        $payment_methods = [1 => 'Через курьера', 2 => 'Самовызов', 3 => 'Переводы', 4 => 'Криптовалютой'];
        $payment_method = $payment_methods[$this->add('payment_method')] ?? null;
        $prices = array_column($productsCart, 'price');
        $total_price = array_sum($prices);

        // Если способ оплаты был не выбран
        if ($payment_method === null) {
            return 'Выберите способ оплаты.';
        }

        // Валидация данных для заказа
        $validator = [
            Request::validateRequired($auth['address'], 'address_required'),
            Request::validateRequired($auth['phone'], 'phone_required'),
            Request::validateRequired($payment_method, 'payment_required'),
            Request::validateCsrfToken()
        ];

        if (Request::validate($validator)) {
            // Начало транзакции
            $db->beginTransaction();
            try {
                // Оформление заказа
                $order->create([
                    'user_id' => $auth['id'],
                    'order_num' => $orderNumber,
                    'payment_method' => $payment_method,
                    'total_price' => $total_price,
                    'address' => $auth['address'],
                    'status' => 'Новый',
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
                    setcookie('cart_qty', '', time() - 86400 * 30, '/');
                }
                $db->commit();

                return 'Заказ успешно оформлен!';
            } catch(PDOException $e) {
                $db->rollBack();
                return 'Возникли ошибки при оформлении заказа.';
            }
        }

        return "Заполните адрес доставки и контактную информацию.";
    }

    public function buy($params)
    {
        $auth = $this->authService->verifyAuth();

        if (!$auth) {
            $this->redirect('/login');
        }

        return $this->render('products/orderOne.twig', [
            'product' => (new Product)->getProduct($params['id']),
            'csrf_token' => $this->generateCsrfToken(),
            'auth' => $auth,
            'cart_qty' => $_COOKIE['cart_qty'] ?? null
        ]);
    }

    public function makeOrderOne($params)
    {
        $auth = $this->authService->verifyAuth();

        if (!$auth) {
            $this->redirect('/login');
        }

        $productModel = new Product();
        
        $order = new Order();
        $db = Order::getLink();

        $orderNumber = mt_rand(1000, 9999) . mt_rand(10000, 99999);
        $payment_methods = [1 => 'Через курьера', 2 => 'Самовызов', 3 => 'Переводы', 4 => 'Криптовалютой'];
        $payment_method = $payment_methods[$_POST['payment_method'] ?? null] ?? null;

        if ($payment_method === null) {
            return 'Выберите способ оплаты.';
        }

        $validator = [
            Request::validateRequired($auth['address'], 'address_required'),
            Request::validateRequired($auth['phone'], 'phone_required'),
            Request::validateRequired($payment_method, 'payment_required'),
            Request::validateCsrfToken()
        ];

        if (Request::validate($validator)) {
            $db->beginTransaction();
            try {
                $product = $productModel->getProduct($params['id']);
                $order->create([
                    'user_id' => $auth['id'],
                    'order_num' => $orderNumber,
                    'payment_method' => $payment_method,
                    'total_price' => $product['price'],
                    'address' => $auth['address'],
                    'status' => 'Новый',
                    'created_at' => date('d M Y H:i:s')
                ]);
    
                $order_id = $db->lastInsertId();
                $order->setOrdersProducts([
                    ':order_id' => $order_id,
                    ':product_id' => $params['id'],
                    ':quantity' => 1,
                    ':price' => $product['price']
                ]);
                setcookie('cart_qty', '', time() - 86400 * 30, '/');
                $db->commit();

                return 'Заказ успешно оформлен!';
            } catch(PDOException $e) {
                $db->rollBack();
                return 'Возникли ошибки при оформлении заказа.';
            }
        }

        return "Заполните адрес доставки и контактную информацию.";
    }

    public function completeOrder($params)
    {
        $order = new Order();
        $db = Order::getLink();

        $validator = [
            Request::validateCsrfToken()
        ];

        // Начало транзакции
        $db->beginTransaction();

        // Запрос на отмену заказа и перемещение заказа в историю покупок
        if (isset($_POST['cancel_order']) and Request::validate($validator)) {
            try {
                $order->moveToOrderHistory($params['id'], 'Отменен');
                $lastInsertOrderId = $db->lastInsertId();        ;
                $order->moveToOrdersProductsHistory($params['id'], $lastInsertOrderId, 'canceled');
                $order->delete($params['id']);
                $db->commit();

                return $this->generateCsrfToken();
            } catch (PDOException $e) {
                $db->rollBack();
            }
        }

        // Запрос на подтверждение заказа и перемещение заказа в историю покупок
        if (isset($_POST['confirm_order']) and Request::validate($validator)) {
            try {
                $order->moveToOrderHistory($params['id'], 'Доставлен');
                $lastInsertOrderId = $db->lastInsertId();        ;
                $order->moveToOrdersProductsHistory($params['id'], $lastInsertOrderId, 'delivered');
                $order->delete($params['id']);
                $db->commit();

                return $this->generateCsrfToken();
            } catch (PDOException $e) {
                $db->rollBack();
            }
        }

        return false;
    }
}