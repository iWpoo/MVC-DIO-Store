<?php 

namespace App\Project\Models;
use App\Core\Model;

class Order extends Model
{
    protected $table = 'orders';
    
    public static function getLink() {
        return static::$link;
    }
    
    // Создание записей в таблице orders_products
    public function setOrdersProducts(array $data)
    {
        $query = self::$link->prepare("INSERT INTO orders_products (order_id, product_id, quantity, price) VALUES (:order_id, :product_id, :quantity, :price)");
        $query->execute($data);
    }

    // Получение информации о заказах конкретного пользователя
    public function getOrdersInfo($user_id)
    {
        $query = self::$link->prepare("SELECT * FROM orders WHERE user_id = :user_id", [':user_id' => $user_id]);
        $query->execute([':user_id' => $user_id]);
        return $query->fetchAll();
    }
}