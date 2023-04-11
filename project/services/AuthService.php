<?php

namespace App\Project\Services;

use App\Project\Services\RedisService;
use App\Project\Models\Cart;

class AuthService
{
    public function auth(array $user)
    {
        // Генерируем токен сессии
        $token = bin2hex(random_bytes(42));
        $hashed_token = hash('sha256', $token);

        // Сохраняем захешированный токен в Redis
        $id = serialize($user);
        (new RedisService)->redis()->setex($hashed_token, 86400 * 30, $id);

        // Сохраняем оригинальный токен в cookie на стороне клиента
        setcookie('session_token', $token, time() + 86400 * 30, '/', 'localhost', false, true);
        
        // Перенести данные корзинки из куки в БД после авторизации
        (new Cart)->addProductsToCart($user['id']);
    }

    public function verifyAuth()
    {
        $redis = new RedisService();

        // Проверка на отсутствие токена
        if (!isset($_COOKIE['session_token'])) {
            return false;
        }

        // Хешируем токен и сравниваем на существование данного токена в Redis
        $token = $_COOKIE['session_token'];
        $hashed_token = hash('sha256', $token);
        $user = $redis->redis()->get($hashed_token);

        // Если токен есть, то возвращаем массив с данными авторизованного пользователя
        if ($user) {
            $ttl = $redis->redis()->ttl($hashed_token);
            /* Если пользователь кинет запрос на оставшееся неделе 
            до окончания срока действия токена, то токен обновляется
            для предотвращения повторной авторизации */
            if ($ttl < 86400 * 7) {
                $redis->redis()->del($hashed_token);
                $this->auth($user);
            }
            return unserialize($user);
        }
        return false;
    }
}