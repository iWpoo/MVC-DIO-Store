<?php 

namespace App\Project\Models;
use App\Core\Model;

class User extends Model
{
    protected $table = 'users';

    public static function getLink() {
        return static::$link;
    }

    public function auth(array $user)
    {
        // Генерируем токен сессии
        $token = bin2hex(random_bytes(42));
        $key = '5fQ-1VloNNPMsvo5HK_ylkX^%9%5+=B8';
        $iv = 'iI^WL%GPVow6Ae3t';
        $encrypted_token = openssl_encrypt($token, 'AES-128-CBC', $key, 0, $iv);

        // Сохраняем токен в Redis
        $id = serialize($user);
        $this->redis()->setex($encrypted_token, 86400 * 30, $id);

        // Сохраняем токен в cookie на стороне клиента
        setcookie('session_token', $token, time() + 86400 * 30, '/', 'localhost', false, true);
        
        // Перенести данные корзинки из куки в БД после авторизации
        $this->addCart($user['id']);
    }

    public function verifyAuth()
    {
        // Проверка на отсутствие токена
        if (!isset($_COOKIE['session_token'])) {
            return false;
        }

        // Шифруем токен и сравниваем на существование данного токена в Redis
        $token = $_COOKIE['session_token'];
        $key = '5fQ-1VloNNPMsvo5HK_ylkX^%9%5+=B8';
        $iv = 'iI^WL%GPVow6Ae3t';
        $encrypted_token = openssl_encrypt($token, 'AES-128-CBC', $key, 0, $iv);
        $user = $this->redis()->get($encrypted_token);

        // Если токен есть, то возвращаем массив с данными авторизованного пользователя
        if ($user) {
            $ttl = $this->redis()->ttl($encrypted_token);
            /* Если пользователь кинет запрос на оставшееся неделе 
            до окончания срока действия токена, то токен обновляется */
            if ($ttl < 86400 * 7) {
                $this->redis()->del($encrypted_token);
                $this->auth($user);
            }
            return unserialize($user);
        }
        return false;
    }
  
    
    // Добавление товаров в корзинку, из куки в БД
    private function addCart($user_id)
    {
        $products_id = isset($_COOKIE['cart']) ? unserialize($_COOKIE['cart']) : false;
        if ($products_id !== false) {
            $arr = [];
            foreach ($products_id as $number) {
                $cart = self::$link->prepare("SELECT * FROM carts WHERE user_id = :user_id AND product_id = :product_id");
                $cart->execute([':user_id' => $user_id, ':product_id' => $number]);
                $cartGet = $cart->fetchAll();

                if ($cartGet) {
                    unset($number);
                } else {
                    $innerArr = [
                        'user_id' => $user_id,
                        'product_id' => $number,
                        'expire' => date('Y-m-d H:i:s', strtotime('+1 month'))
                    ];
                    array_push($arr, $innerArr);
                }
            }

            $query = self::$link->prepare("INSERT INTO carts (user_id, product_id, expire) VALUES (:user_id, :product_id, :expire)");
            $count = count($arr);
            for ($i = 0; $i < $count; $i++) {
                $query->bindParam(':user_id', $arr[$i]['user_id']);
                $query->bindParam(':product_id', $arr[$i]['product_id']);
                $query->bindParam(':expire', $arr[$i]['expire']);
                $query->execute();
            }
            setcookie('cart', '', time() - 86400 * 30, '/');
        }
    }
}