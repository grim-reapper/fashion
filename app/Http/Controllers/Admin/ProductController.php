<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function show()
    {

    }

    public function create()
    {
        $categories = Category::all();
        return view('admin.products.add', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = [
          'product_name' => $request->name,
          'product_id' => $request->product_id,
          'description' => $request->description,
          'price' => $request->price,
          'category_id' => $request->category,
          'primary_color' => $request->color,
          'quantity' => $request->quantity,
            'is_active' => $request->status == 'on' ? true : false,
        ];
        if($request->file()){
            $fileName = time().'_'.$request->file->getClientOriginalName();
            $request->file('file')->move('img/product', $fileName);
            $data['image'] = $fileName;
        }
        $product = Product::create($data);
        if($product){
            return redirect()->back()->with('success', 'Product added successfully');
        }
        return redirect()->back()->with('error', 'An error occurred while adding product');
    }
}
