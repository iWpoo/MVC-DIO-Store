<?php 

namespace App\Project\Models;
use App\Core\Model;

class Favorite extends Model
{
    protected $table = 'favorites';

    // Добавление или удаление товара из избранных
    public function modifyFavorite($user_id, $product_id)
    {
        $fav = $this->findSpecific("WHERE user_id = :user_id AND product_id = :product_id", [':user_id' => $user_id, ':product_id' => $product_id]);
        if (!$fav) {
            $this->create([
                'user_id' => $user_id,
                'product_id' => $product_id
            ]);
        } else {
            $this->delete($fav['id'], 'id');
        }
    }
}