<?php

namespace App\Project\Controllers;

use App\Core\Controller;
use App\Project\Requests\Request;
use App\Project\Models\Product;
	
class FavoriteController extends BaseController
{
    public function favorite($params)
    {
        $auth = $this->authService->verifyAuth();
        
        $validator = [
            Request::validateCsrfToken()
        ];
        
        if (Request::validate($validator)) {
            // Удаление или добавление товара в избранное
            $this->favoriteService->modifyFavorite($auth['id'], $params['id']);
            return json_encode(['csrf_token' => $this->generateCsrfToken()]);
        }
    }

    public function favorites()
    {
        $auth = $this->authService->verifyAuth();

        if (!$auth) {
            $this->redirect('/login');
        }

        $products = new Product();

        return $this->render('profile/favorites.twig', [
            'products' => $products->findAll("WHERE id IN (SELECT product_id FROM favorites WHERE user_id = :user_id)", [':user_id' => $auth['id']]),
            'auth' => $auth,
            'cart_qty' => $_COOKIE['cart_qty'] ?? null
        ]);
    }
}