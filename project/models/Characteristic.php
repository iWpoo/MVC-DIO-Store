<?php 

namespace App\Project\Models;
use App\Core\Model;

class Characteristic extends Model
{
    protected $table = 'characteristics';

    // Получение характеристик товара с учетом кэширования
    public function getChar($product_id)
    {
        $ch = $this->findAll("WHERE product_id = :product_id", [':product_id' => $product_id]);
        return $this->caching("ch:$product_id", $ch, 3600 * 6);
    }
}