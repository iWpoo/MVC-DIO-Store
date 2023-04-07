<?php 

namespace App\Project\Models;
use App\Core\Model;

class Product extends Model
{
    protected $table = 'products';

    // Получение бестселлеров с учетом кэширования
    public function getBestsellers()
    {
        $products = $this->findAll("ORDER BY sales DESC LIMIT 20");
        return $this->caching("top_products:sales_desc:limit_20", $products, 3600 * 2);
    }

    // Получение новинок с учетом кэширования
    public function getNewProducts()
    {
        $products = $this->findAll("WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) ORDER BY id DESC LIMIT 50");
        return $this->caching("new_products:date_desc:limit_50", $products, 3600 * 2);
    }

    // Получение товара индивидуально с учетом кэширования
    public function getProduct($product_id)
    {
        $product = $this->find($product_id);
        return $this->caching("product:$product_id", $product, 3600 * 6);
    }

    // Получение похожих товаров
    public function getSimilarProducts($category_id, $subcategory_id)
    {
        $products = $this->findAll("WHERE category_id = :category_id OR subcategory_id = :subcategory_id", [':category_id' => $category_id, ':subcategory_id' => $subcategory_id]);
        return $this->caching("similar_products:$category_id|$subcategory_id", $products, 3600 * 2);
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

    // Поиск товаров
    public function search($search, $offset, $limit, $sort_type = 'popularity', $priceFrom = false, $priceTo = false)
    {
        if (empty($search)) {
            return false;
        }
    
        $sch = explode(' ', $search);
        $count = count($sch);
        $array = array();
        
        $sql = "SELECT * FROM products WHERE ";

        foreach ($sch as $key) {
            $sql .= "(title LIKE :param" . $count . " OR (color IS NOT NULL AND color LIKE :param" . $count . ") OR (memory IS NOT NULL AND memory LIKE :param" . $count . ")) OR ";
            $params[":param" . $count] = '%' . $key . '%';
            $count--;
        }
        
        $sql = rtrim($sql, " OR ");
    
        if (!empty($priceFrom)) {
            $sql .= " AND price >= $priceFrom ";
        }
    
        if (!empty($priceTo)) {
            $sql .= " AND price <= $priceTo ";
        }
    
        $dataSort = [
            'popularity' => 'sales DESC',
            'new' => 'id DESC',
            'price_inc' => 'price ASC',
            'price_dec' => 'price DESC'
        ];
    
        if (array_key_exists($sort_type, $dataSort)) {
            $sql .= " ORDER BY " . $dataSort[$sort_type];
        } else {
            $sql .= " ORDER BY sales DESC";
        }
    
        $sql .= " LIMIT $offset, $limit";
    
        $query = self::$link->prepare($sql);
        $query->execute($params);
    
        return $query->fetchAll();
    }

    // Поиск категории
    public function searchByCategory($id, $offset, $limit, $sort_type = 'popularity', $priceFrom = false, $priceTo = false)
    {
        $sql = "SELECT id, title, image, price FROM products WHERE category_id = ?";
        $params = [$id];

        if (!empty($priceFrom)) {
            $sql .= " AND price >= ?";
            $params[] = $priceFrom;
        }

        if (!empty($priceTo)) {
            $sql .= " AND price <= ?";
            $params[] = $priceTo;
        }

        $dataSort = [
            'popularity' => 'sales DESC',
            'new' => 'id DESC',
            'price_inc' => 'price ASC',
            'price_dec' => 'price DESC'
        ];

        $sort = $dataSort[$sort_type] ?? $dataSort['popularity'];
        $sql .= " ORDER BY $sort LIMIT $offset, $limit";

        $query = self::$link->prepare($sql);
        $query->execute($params);
        return $query->fetchAll();
    }

    // Поиск подкатегории
    public function searchBySubcategory($id, $offset, $limit, $sort_type = 'popularity', $priceFrom = false, $priceTo = false)
    {
        $sql = "SELECT id, title, image, price FROM products WHERE subcategory_id = ?";
        $params = [$id];

        if (!empty($priceFrom)) {
            $sql .= " AND price >= ?";
            $params[] = $priceFrom;
        }

        if (!empty($priceTo)) {
            $sql .= " AND price <= ?";
            $params[] = $priceTo;
        }

        $dataSort = [
            'popularity' => 'sales DESC',
            'new' => 'id DESC',
            'price_inc' => 'price ASC',
            'price_dec' => 'price DESC'
        ];

        $sort = $dataSort[$sort_type] ?? $dataSort['popularity'];
        $sql .= " ORDER BY $sort LIMIT $offset, $limit";

        $query = self::$link->prepare($sql);
        $query->execute($params);
        return $query->fetchAll();
    }

    // Получить общее количество записей в products
    public function countRow()
    {
        $query = self::$link->prepare("SELECT COUNT(*) FROM products");
        $query->execute();
        return $query->fetchColumn();
    }

    // Обрезать часть url строки
    public function cutUrlString($string)
    {
        if (isset($_SERVER['QUERY_STRING'])) {
            $query = $_SERVER['QUERY_STRING'];
            if (strpos($query, $string) !== false) {
              $query = preg_replace("/$string.*/", '', $query);
              $url = strtok($_SERVER['REQUEST_URI'], '?') . '?' . $query;
              return $url;
            }
            return '?' . $_SERVER['QUERY_STRING'];
        }  
    }
}