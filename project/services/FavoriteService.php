<?php 

namespace App\Project\Services;

use App\Project\Models\Favorite;

class FavoriteService
{
    // Добавление или удаление товара из избранных
    public function modifyFavorite($user_id, $product_id)
    {
        $fav = (new Favorite)->findSpecific("WHERE user_id = :user_id AND product_id = :product_id", [':user_id' => $user_id, ':product_id' => $product_id]);
        if (!$fav) {
            (new Favorite)->create([
                'user_id' => $user_id,
                'product_id' => $product_id
            ]);
        } else {
            (new Favorite)->delete($fav['id'], 'id');
        }
    }
}