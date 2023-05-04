<?php 

namespace App\Project\Services;

use App\Core\Database;
use App\Project\Models\Order;

class OrderService
{
    protected static $link;

    public function __construct()
    {
       self::$link = Database::getInstance()->getPdo();
    }
    
    // Объединение данных Product and Cart
    public function joinProductCart($user_id)
    {
        $query = self::$link->prepare("SELECT products.*, carts.id as cart_id, carts.count FROM products INNER JOIN carts ON products.id = carts.product_id WHERE carts.user_id = :user_id");
        $query->execute([':user_id' => $user_id]);
        return $query->fetchAll();
    }
    
    // Создание записей в таблице orders_products
    public function setOrdersProducts(array $data)
    {
        $query = self::$link->prepare("INSERT INTO orders_products (order_id, product_id, quantity, price) VALUES (:order_id, :product_id, :quantity, :price)");
        $query->execute($data);
    }

    // Получение информации о активных заказах конкретного пользователя
    public function getOrdersInfo($user_id)
    {
        $query = self::$link->prepare("SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC", [':user_id' => $user_id]);
        $query->execute([':user_id' => $user_id]);
        return $query->fetchAll();
    }

    // Получение информации об историю покупок конкретного пользователя
    public function getHistoryOrdersInfo($user_id)
    {
        $query = self::$link->prepare("SELECT * FROM order_history WHERE user_id = :user_id ORDER BY created_at DESC", [':user_id' => $user_id]);
        $query->execute([':user_id' => $user_id]);
        return $query->fetchAll();
    }

    // Показ активных заказов
    public function getOrdersByUser($user_id)
    {
        $query = self::$link->prepare("SELECT 
            products.title, 
            products.image, 
            orders_products.order_id,
            orders_products.product_id,
            orders_products.quantity, 
            orders_products.price 
        FROM 
            products 
        INNER JOIN orders_products ON orders_products.product_id = products.id 
        INNER JOIN orders ON orders.id = orders_products.order_id 
        WHERE 
            orders.user_id = :user_id
        ");
        $query->execute([':user_id' => $user_id]);
        return $query->fetchAll();
    }

    // Показ историю покупок
    public function getHistoryOrdersByUser($user_id)
    {
        $query = self::$link->prepare("SELECT 
            products.title, 
            products.image, 
            order_history_products.order_id,
            order_history_products.product_id,
            order_history_products.quantity, 
            order_history_products.price 
        FROM 
            products 
        INNER JOIN order_history_products ON order_history_products.product_id = products.id 
        INNER JOIN order_history ON order_history.id = order_history_products.order_id 
        WHERE 
            order_history.user_id = :user_id
        ");
        $query->execute([':user_id' => $user_id]);
        return $query->fetchAll();
    }

    // Перемещение заказа из таблицы orders в order_history
    public function moveToOrderHistory($id, $status)
    {
        $order_info = (new Order)->find($id);
        $query = self::$link->prepare("INSERT INTO order_history (order_num, user_id, payment_method, total_price, address, status, created_at) VALUES (:order_num, :user_id, :payment_method, :total_price, :address, :status, :created_at)");
        $query->execute([
            ':order_num' => $order_info['order_num'],
            ':user_id' => $order_info['user_id'],
            ':payment_method' => $order_info['payment_method'],
            ':total_price' => $order_info['total_price'],
            ':address' => $order_info['address'],
            ':status' => $status,
            ':created_at' => $order_info['created_at']
        ]);
    }

    // Перемещение данных о заказанных товаров из таблицы orders_products в order_history_products
    public function moveToOrdersProductsHistory($order_id, $order_historyId, $status)
    {
        $query_orders_products = self::$link->prepare("SELECT * FROM orders_products WHERE order_id = :order_id");
        $query_orders_products->execute([':order_id' => $order_id]);
        $orders_products = $query_orders_products->fetchAll();

        $query = self::$link->prepare("INSERT INTO order_history_products (order_id, product_id, quantity, price) VALUES (:order_id, :product_id, :quantity, :price)");

        foreach ($orders_products as $product) {
            $query->execute([
                ':order_id' => $order_historyId,
                ':product_id' => $product['product_id'],
                ':quantity' => $product['quantity'],
                ':price' => $product['price']
            ]);

            // Инкрементируем число продаж товара
            if ($status == 'delivered') {
                $product_update = self::$link->prepare("UPDATE products SET sales = sales + 1 WHERE id = :id");
                $product_update->execute([':id' => $product['product_id']]);   
            }
        }        
    }
}
