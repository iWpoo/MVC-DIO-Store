<?php

namespace App\Project\Controllers;

use App\Core\Controller;
use App\Project\Requests\Request;
	
class SearchController extends BaseController
{
    public function search()
    {
        if (isset($_GET['q'])) {
            $search = htmlspecialchars($_GET['q']);

            $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
            $limit = 30;
            $offset = max(0, $limit * ($page - 1));
            $total_pages = round($this->productService->countRow() / $limit, 0);

            $sort_type = $_GET['sort_products'] ?? 'popularity';
            $priceFrom = $_GET['price_from'] ?? false;
            $priceTo = $_GET['price_to'] ?? false;

            $products = $this->productService->search($search, $offset, $limit, $sort_type, $priceFrom, $priceTo);
        }

        return $this->render('products/search.twig', [
            'search' => $search ?? null,
            'products' => $products ?? null,
            'sort_value' => $sort_type ?? null,
            'price_from' => $priceFrom ?? null,
            'price_to' => $priceTo ?? null,
            'page' => $page ?? null,
            'total_pages' => $total_pages ?? null,
            'urlPages' => $this->cutUrlString('&page='),
            'auth' => $this->authService->verifyAuth(),
            'cart_qty' => $_COOKIE['cart_qty'] ?? null
        ]);
    }

    public function searchByCategory()
    {
        if (isset($_GET['category'])) {
            $category = htmlspecialchars($_GET['category']);

            $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
            $limit = 30;
            $offset = max(0, $limit * ($page - 1));
            $total_pages = round($this->productService->countRow() / $limit, 0);

            $sort_type = $_GET['sort_products'] ?? 'popularity';
            $priceFrom = $_GET['price_from'] ?? false;
            $priceTo = $_GET['price_to'] ?? false;
        
            $products = $this->productService->searchByCategory($category, $offset, $limit, $sort_type, $priceFrom, $priceTo);
        }

        return $this->render('products/search_category.twig', [
            'category' => $category ?? null,
            'products' => $products ?? null,
            'sort_value' => $sort_type ?? null,
            'price_from' => $priceFrom ?? null,
            'price_to' => $priceTo ?? null,
            'page' => $page ?? null,
            'total_pages' => $total_pages ?? null,
            'urlPages' => $this->cutUrlString('&page='),
            'auth' => $this->authService->verifyAuth(),
            'cart_qty' =>  $_COOKIE['cart_qty'] ?? null        
        ]);
    }

    public function searchBySubcategory()
    {
        if (isset($_GET['subcategory'])) {
            $subcategory = htmlspecialchars($_GET['subcategory']);

            $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
            $limit = 30;
            $offset = max(0, $limit * ($page - 1));
            $total_pages = round($this->productService->countRow() / $limit, 0);

            $sort_type = $_GET['sort_products'] ?? 'popularity';
            $priceFrom = $_GET['price_from'] ?? false;
            $priceTo = $_GET['price_to'] ?? false;
        
            $products = $this->productService->searchBySubcategory($subcategory, $offset, $limit, $sort_type, $priceFrom, $priceTo);
        }

        return $this->render('products/search_subcategory.twig', [
            'subcategory' => $subcategory ?? null,
            'products' => $products ?? null,
            'sort_value' => $sort_type ?? null,
            'price_from' => $priceFrom ?? null,
            'price_to' => $priceTo ?? null,
            'page' => $page ?? null,
            'total_pages' => $total_pages ?? null,
            'urlPages' => $this->cutUrlString('&page='),
            'auth' => $this->authService->verifyAuth(),
            'cart_qty' =>  $_COOKIE['cart_qty'] ?? null
        ]);
    }
}