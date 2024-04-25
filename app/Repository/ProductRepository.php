<?php

namespace App\Repository;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class ProductRepository
{
    
    public function getProducts($category = null)
    {
        $user_id = auth()->id();
        $searchQuery = request()->search;
        $request = Product::query();
        if($searchQuery && $searchQuery != '') {
            $request->where(function($query) use ($searchQuery)
            {
                $query->where('product_name', 'LIKE', '%' . $searchQuery . '%');
                $query->orWhere('product_brand', 'LIKE', '%' . $searchQuery . '%');
                $query->orWhereHas('category', function($q) use ($searchQuery) {
                    $q->where(function($q) use ($searchQuery) {
                        $q->where('category_name', 'LIKE', '%' . $searchQuery . '%');
                    });
                });
            });
        }
        
        
        return $request->where('category_id', $category);

//        return $request->where(['respondent_id' => $user_id, 'request_status' => $request_status])
//            ->with('user:id,first_name,last_name,email')
//            ->orderBy('date_created', 'desc');
    }

}