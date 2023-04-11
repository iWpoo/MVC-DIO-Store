<?php 

namespace App\Project\Services;
use Predis\Client;

class RedisService
{
    public function redis()
    {
        $redis = new Client([
            'scheme' => REDIS_SCHEME,
            'host' => REDIS_HOST,
            'port' => REDIS_PORT,
        ]);
        return $redis;
    }

    public function caching($key, $data, $expireTime)
    {
        if ($this->redis()->exists($key)) {
            $object = $this->redis()->get($key);
            return unserialize($object);
        }

        $this->redis()->setex($key, $expireTime, serialize($data));
        return $data;
    }
}