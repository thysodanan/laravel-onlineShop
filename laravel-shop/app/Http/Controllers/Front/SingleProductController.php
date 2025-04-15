<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Color;
use App\Models\Products;
use Illuminate\Http\Request;

class SingleProductController extends Controller
{
    public function singleProduct(string $id){
        $product = Products::with(['Images','Categories','Brands'])->find($id);

        $related_products = Products::with('Images')->where('brand_id',$product->brand_id)
        ->where('price','>=',$product->price - 100)
        ->where('price','<=',$product->price + 100)
        ->where('status',1)
        ->limit(4)->get();



        //convert string  to array
        $colorIds = explode(',', $product->color);

        // Fetch colors from the database
        $colors = Color::whereIn('id', $colorIds)->get();
        
        
        

        if (!$product) {
            abort(404, 'Product not found');
        }


        return view('front-end.single-product', [
            'product' => $product,
            'colors'  => $colors,
            'images' => $product->Images, 
            'category' => $product->Categories,
            'brand'  => $product->Brands,
            'related_products' => $related_products
        ]);
    }

}
