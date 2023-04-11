<?php 

namespace App\Project\Models;
use App\Core\Model;
use App\Project\Services\RedisService;

class User extends Model
{
    protected $table = 'users';

    public static function getLink() {
        return static::$link;
    }  
}