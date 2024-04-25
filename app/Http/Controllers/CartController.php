<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Repository\ProductRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
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
    public function index()
    {
        
        return view('cart.index', compact());
    }
    
    public function addToCart(Request $request)
    {
        
        Session::put('referer', $request->headers->get('referer'));
        
        if ($request->product_id && is_numeric($request->product_id)) {
            
            $discount = 0;
            if ($request->has('discount') && $request->discount > 0 ) {
                $discount = $request->discount;
            }
            
            $this->cartRepo->addItemToCart(
                $request->doc_id,
                $request->item_type,
                $request->item_category,
                ['discount' => $discount, 'period' => $period, 'only_drs' => $only_drs]
            );
        } elseif (is_string($request->doc_id)) {
            $this->cartRepo->addLdeItemToCart($request->doc_id, $request->item_type, $request->item_category);
        }
        return redirect()->route('cart');
    }
}
