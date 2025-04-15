<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Color;
use App\Models\ProductImage;
use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('back-end.product');
    }

    public function list(Request $request){

        $limit = 10;

        $page  = $request->page;

        $offset = ($page - 1) * $limit;


        if($request->search != null){
            $products = Products::with(['Images','Categories','Brands'])
                       ->where('name','like','%'.$request->search.'%')
                       ->orWhereHas('Categories',function($feild) use ($request) {
                           $feild->where('name','like','%'.$request->search.'%');
                       })
                       ->orWhereHas('Brands',function($feild) use ($request) {
                           $feild->where('name','like','%'.$request->search.'%');
                       })
                       ->limit($limit)
                       ->offset($offset)
                       ->get();
            

            #Total Record
            $totalRecord = Products::with(['Images','Categories','Brands'])
                       ->where('name','like','%'.$request->search.'%')
                       ->orWhereHas('Categories',function($feild) use ($request) {
                           $feild->where('name','like','%'.$request->search.'%');
                       })
                       ->orWhereHas('Brands',function($feild) use ($request) {
                           $feild->where('name','like','%'.$request->search.'%');
                       })
                       ->count();
            
           
             

        }else{
            $products = Products::orderBy("id","DESC")->with(['Images','Categories','Brands'])
                                 ->limit($limit)
                                 ->offset($offset)
                                 ->get();

            #Total Record
            $totalRecord = Products::count();
        }

        $totalPage   = ceil($totalRecord / $limit);
        
        return response([
            'status' => 200,
            'page' => [
                'totalRecord' => $totalRecord,
                'totalPage'  => $totalPage,
                'currentPage' => $page
            ],
            'products' => $products
        ]);
    }

    public function data(){
        $categories = Category::orderBy("id","DESC")->get();
        $brands = Brand::orderBy("id","DESC")->get();
        $color  = Color::orderBy("id","DESC")->get();

        return response([
            'status' => 200,
            'data' => [
                'categories' => $categories,
                'brands' => $brands,
                'colors' => $color,
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'title' => 'required',
            'price' => 'required|numeric',
            'qty'  => 'required|numeric',

        ]);

        if($validator->passes()){
            //save Product to table in db
            $product = new Products();
            $product->name = $request->title;
            $product->desc = $request->desc;
            $product->price = $request->price;
            $product->qty  = $request->qty;
            $product->category_id = $request->category;
            $product->brand_id = $request->brand;
            $product->color   = implode(",",$request->color);  
            //[4,3,2] => "4,3,2"
            $product->user_id = Auth::user()->id;
            $product->status = $request->status;

            $product->save();

            //Save to images table in db
            if($request->image_uploads != null){
                $images = $request->image_uploads;
                foreach($images as $img){
                    $image = new ProductImage();
                    $image->image  = $img;
                    $image->product_id = $product->id;

                    //move image to product directory 
                    if(File::exists(public_path("uploads/temp/$img"))){

                         //copy
                         File::copy(public_path("uploads/temp/$img"),public_path("uploads/product/$img"));

                         //delete from temp directory
                         File::delete(public_path("uploads/temp/$img"));
 
                    }

                    $image->save();
                }
            }

            
            return response([
                'status' => 200,
                'message' => "Product created successfully",
            ]);

        }else{
            return response()->json([
                'status' => 500,
                'message' => 'Validation Failed',
                'errors' => $validator->errors()
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request)
    {
        $product = Products::find($request->id);
        $productImages = ProductImage::where('product_id',$request->id)->get();
        $brands  = Brand::orderBy('id','DESC')->get();
        $categories = Category::orderBy('id','DESC')->get();
        $colors  = Color::orderBy('id','DESC')->get();

        return response([
            'status' => 200,
            'data'  => [
                'product' => $product,
                'productImages' => $productImages,
                'brands' => $brands,
                'categories' => $categories,
                'colors' => $colors,
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'title' => 'required',
            'price' => 'required|numeric',
            'qty'  => 'required|numeric',

        ]);

        if($validator->passes()){
            //save Product to table in db
            $product = Products::find($request->product_id);
            $product->name = $request->title;
            $product->desc = $request->desc;
            $product->price = $request->price;
            $product->qty  = $request->qty;
            $product->category_id = $request->category;
            $product->brand_id = $request->brand;
            $product->color   = implode(",",$request->color);  
            //[4,3,2] => "4,3,2"
            $product->user_id = Auth::user()->id;
            $product->status = $request->status;

            $product->save();

            //Save to images table in db
            if($request->image_uploads != null){
                $images = $request->image_uploads;
                foreach($images as $img){
                    $image = new ProductImage();
                    $image->image  = $img;
                    $image->product_id = $product->id;

                    //move image to product directory 
                    if(File::exists(public_path("uploads/temp/$img"))){

                         //copy
                         File::copy(public_path("uploads/temp/$img"),public_path("uploads/product/$img"));

                         //delete from temp directory
                         File::delete(public_path("uploads/temp/$img"));
 
                    }

                    $image->save();
                }
            }

            
            return response([
                'status' => 200,
                'message' => "Product Updated successfully",
            ]);

        }else{
            return response()->json([
                'status' => 500,
                'message' => 'Validation Failed',
                'errors' => $validator->errors()
            ]);
        }
    }


    public function destroy(Request $request)
    {
        
        #Delete image from folder
        $productImages = ProductImage::where('product_id',$request->id)->get();

        if($productImages != null){
            foreach($productImages as $image){
                File::delete(public_path("uploads/product/$image->image"));
            }
        }

        #Delete product from db
        Products::find($request->id)->delete();

        return response([
            'status' => 200,
            'message' => 'Product deleted successful'
        ]);

    }
}
