<?php 

namespace App\Project\Models;
use App\Core\Model;

class Cart extends Model
{
    protected $table = 'carts';

    // Добавление или удаление товара из корзинки
    public function modifyCart($user_id, $product_id, $price)
    {
        $cart = $this->findSpecific("WHERE user_id = :user_id AND product_id = :product_id", [':user_id' => $user_id, ':product_id' => $product_id]);
        if (!$cart) {
            $this->create([
                'user_id' => $user_id,
                'product_id' => $product_id,
                'count' => 1,
                'price' => $price,
                'expire' => date('Y-m-d H:i:s', strtotime('+1 month'))
            ]);
        } else {
            $this->delete($cart['id'], 'id');
        }
    }

    // Удаление выбранных записей
    public function deleteSelected(array $data)
    {
        $ids = implode(',', $data);
        $query = self::$link->prepare("DELETE FROM carts WHERE product_id IN (".str_repeat('?,', count($data) - 1)."?)");
        $query->execute($data);
    }

    public function getCartByCookie($id)
    {
        if (!isset($_COOKIE['cart'])) {
            return false;
        }

        $cart = unserialize($_COOKIE['cart']);
        if (in_array($id, $cart)) {
            return true;
        }
        return false;
    }

    public function modifyCartCookie($id)
    {   
        if (!isset($_COOKIE['cart'])) {
            $cart = [$id];
            setcookie('cart', serialize($cart), time() + 86400 * 30, '/');
            header('Location: /product/'.$id);
        }

        if (!$this->getCartByCookie($id)) {
            $cart = unserialize($_COOKIE['cart']);
            array_push($cart, $id);
            setcookie('cart', serialize($cart), time() + 86400 * 30, '/');
        } else {
            $cart = unserialize($_COOKIE['cart']);
            $key = array_search($id, $cart);
            unset($cart[$key]);
            setcookie('cart', serialize($cart), time() + 86400 * 30, '/');
        }
    }

    // Объединение данных Product and Cart
    public function joinProductCart($user_id)
    {
        $query = self::$link->prepare("SELECT products.*, carts.count FROM products INNER JOIN carts ON products.id = carts.product_id WHERE carts.user_id = :user_id");
        $query->execute([':user_id' => $user_id]);
        return $query->fetchAll();
    }
}