<?php 

namespace App\Project\Services;

use App\Core\Database;
use App\Project\Services\RedisService;
use App\Project\Models\Product;

class ProductService
{
    protected static $link;

    public function __construct()
    {
	self::$link = Database::getInstance()->getPdo();
    }
    
    // Получение бестселлеров с учетом кэширования
    public function getBestsellers()
    {
        $productModel = new Product;
        $products = $productModel->findAll("ORDER BY sales DESC LIMIT 20");
        return (new RedisService)->caching("top_products:sales_desc:limit_20", $products, 3600 * 2);
    }

    // Получение новинок с учетом кэширования
    public function getNewProducts()
    {
        $productModel = new Product;
        $products = $productModel->findAll("WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) ORDER BY id DESC LIMIT 50");
        return (new RedisService)->caching("new_products:date_desc:limit_50", $products, 3600 * 2);
    }

    // Получение товара индивидуально с учетом кэширования
    public function getProduct($product_id)
    {
        $productModel = new Product;
        $product = $productModel->find($product_id);
        return (new RedisService)->caching("product:$product_id", $product, 3600 * 6);
    }

    // Получение похожих товаров
    public function getSimilarProducts($category_id, $subcategory_id)
    {
        $productModel = new Product;
        $products = $productModel->findAll("WHERE category_id = :category_id OR subcategory_id = :subcategory_id", [':category_id' => $category_id, ':subcategory_id' => $subcategory_id]);
        return (new RedisService)->caching("similar_products:$category_id|$subcategory_id", $products, 3600 * 2);
    }
    
    // Получение похожего товара с другими характеристиками (цвет, память и т. д.)
    public function modification($title)
    {
        $query = self::$link->prepare("SELECT products.id, products.color, products.memory FROM products WHERE products.title = :title");
        $query->execute([':title' => $title]);
        return $query->fetchAll();
    }

    // Получение характеристик товара с учетом кэширования
    public function getChar($product_id)
    {
        $query = self::$link->prepare("SELECT * FROM characteristics WHERE product_id = :product_id");
        $query->execute([':product_id' => $product_id]);
        $ch = $query->fetchAll();
        return (new RedisService)->caching("ch:$product_id", $ch, 3600 * 6);
    }

    // Получение дополнительных изображений товара
    public function images($product_id)
    {
        $query = self::$link->prepare("SELECT product_images.image FROM product_images WHERE product_id = :product_id");
        $query->execute([':product_id' => $product_id]);
        $images = $query->fetchAll();
        return (new RedisService)->caching("images:$product_id", $images, 3600 * 6);
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
}
