<?php 

namespace App\Project\Models;
use App\Core\Model;

class Product extends Model
{
    protected $table = 'products';

    // Получение товара индивидуально с учетом кэширования
    public function getProduct($product_id)
    {
        $product = $this->find($product_id);
        return $this->caching("product:$product_id", $product, 3600 * 6);
    }
    
    // Получение похожего товара с другими характеристиками (цвет, память и т. д.)
    public function modification($title)
    {
        $query = self::$link->prepare("SELECT products.id, products.color, products.memory FROM products WHERE products.title = :title");
        $query->execute([':title' => $title]);
        return $query->fetchAll();
    }

    // Получение дополнительных изображений товара
    public function images($product_id)
    {
        $query = self::$link->prepare("SELECT product_images.image FROM product_images WHERE product_id = :product_id");
        $query->execute([':product_id' => $product_id]);
        $images = $query->fetchAll();
        return $this->caching("images:$product_id", $images, 3600 * 6);
    }

    // Показ товаров в избранное
    public function getFavoritesByUser($user)
    {
        return $this->findAll("WHERE id IN (SELECT product_id FROM favorites WHERE user_id = :user_id)", [':user_id' => $user]);
    }

    // Показ товаров в корзинке
    public function getCartByUser($user)
    {
        return $this->findAll("WHERE id IN (SELECT product_id FROM carts WHERE user_id = :user_id)", [':user_id' => $user]);
    }

    // Показ заказанных товаров
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
}