<?php 

namespace App\Project\Models;
use App\Core\Model;
use Predis\Client;

class User extends Model
{
    protected $table = 'users';

    public function redis()
    {
        $redis = new Client([
            'scheme' => 'tcp',
            'host' => '127.0.0.1',
            'port' => 6379,
        ]);
        return $redis;
    }

    public function auth($user_id)
    {
        // Генерируем токен сессии
        $token = bin2hex(random_bytes(42));
        $key = '5fQ-1VloNNPMsvo5HK_ylkX^%9%5+=B8';
        $iv = 'iI^WL%GPVow6Ae3t';
        $encrypted_token = openssl_encrypt($token, 'AES-128-CBC', $key, 0, $iv);

        // Сохраняем токен в Redis
        $this->redis()->setex($encrypted_token, 86400 * 30, $user_id);

        // Сохраняем токен в cookie на стороне клиента
        setcookie('session_token', $token, time() + 86400 * 30, '/', 'localhost', false, true);
    }

    public function verifyAuth()
    {
        // Проверка на отсутствие токена
        if (!isset($_COOKIE['session_token'])) {
            return false;
        }

        $token = $_COOKIE['session_token'];
        $key = '5fQ-1VloNNPMsvo5HK_ylkX^%9%5+=B8';
        $iv = 'iI^WL%GPVow6Ae3t';
        $encrypted_token = openssl_encrypt($token, 'AES-128-CBC', $key, 0, $iv);
        $user = $this->redis()->get($encrypted_token);

        if ($user) {
            $ttl = $this->redis()->ttl($encrypted_token);
            if ($ttl < 86400 * 7) {
                $this->redis()->del($encrypted_token);
                $this->auth($user);
            }
            return $user;
        }
        return false;
    }
}