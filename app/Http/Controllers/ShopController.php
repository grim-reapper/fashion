<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\Size;
use App\Repository\ProductRepository;
use Cart;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    
    protected $productRepo;
    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepo = $productRepository;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index($category = null)
    {
        $products = $this->productRepo->getProducts($category)->paginate();
        $categories = Category::all();
        $colors = Color::all();
        $sizes = Size::all();
        return view('shop.index', compact('products', 'categories', 'colors', 'sizes'));
    }
}
