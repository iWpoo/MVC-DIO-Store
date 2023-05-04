<?php

namespace App\Project\Controllers;

use App\Core\Controller;
use App\Project\Services\AuthService;
use App\Project\Services\CartService;
use App\Project\Services\FavoriteService;
use App\Project\Services\MailService;
use App\Project\Services\OrderService;
use App\Project\Services\ProductService;
use App\Project\Services\RedisService;

class BaseController extends Controller
{
    protected $authService;
    protected $cartService;
    protected $favoriteService;
    protected $mailService;
    protected $orderService;
    protected $productService;
    protected $redisService;

    public function __construct()
    {
        $this->authService = new AuthService;
        $this->cartService = new CartService;
        $this->favoriteService = new FavoriteService;
        $this->mailService = new MailService;
        $this->orderService = new OrderService;
        $this->productService = new ProductService;
        $this->redisService = new RedisService;
    }
}
