<?php 

namespace App\Project\Models;
use App\Core\Model;

class Order extends Model
{
    protected $table = 'orders';
    
    public static function getLink() {
        return static::$link;
    }
}