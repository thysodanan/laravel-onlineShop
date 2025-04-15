<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BrandController extends Controller
{
    public function index(){

        $categories = Category::orderBy("id","DESC")->get();
        return view('back-end.brand',compact('categories'));
    }

    public function list(Request $request){

        //pagination form
        $limit = 5;
        $page  = $request->page;  //2

        $offset = ($page - 1) * $limit;

        if(!empty($request->search)){
            $brands = Brand::where('name','like','%'.$request->search.'%')
                            ->orderBy("id","DESC")->with('category')
                            ->limit($limit)
                            ->offset($offset)
                            ->get();
            $totalRecord = Brand::where('name','like','%'.$request->search.'%')->count();
        }else{
            $brands = Brand::orderBy("id","DESC")->with('category')
                            ->limit($limit)
                            ->offset($offset)
                            ->get();
            $totalRecord = Brand::count();
        }

        
       
        //totalRecord 
         
        
        $totalPage   = ceil($totalRecord / 5);  // 2.1 => 3

        return response([
            'status' => 200,
            'page' => [
                'totalRecord' => $totalRecord,
                'totalPage'  => $totalPage,
                'currentPage' => $page,
            ],
            'brands' => $brands
        ]);
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(),[
            'name' => 'required|unique:brands,name'
        ]);

        if($validator->passes()){
            $brand = new Brand();
            $brand->name = $request->name;
            $brand->category_id = $request->category;
            $brand->status = $request->status;
            $brand->save();

            return response([
                'status' => 200,
                'message' => "Brand created successful"
            ]);

        }else{
            return response()->json([
                'status' => 500,
                'error' => $validator->errors(),
            ]);
        }
    }


    public function edit(){

    }

    public function update(Request $request){

        
        $validator = Validator::make($request->all(),[
            'name' => 'required|unique:brands,name,'.$request->brand_id,
        ]);

        if($validator->passes()){
            $brand = Brand::find($request->brand_id);

            //checking brand not found
            if($brand == null){
                return response([
                   'status' => 404,
                   'message' => "Brand not found with id "+$request->brand_id
                ]);
            }

            $brand->name = $request->name;
            $brand->category_id = $request->category;
            $brand->status = $request->status;
            $brand->save();

            return response([
                'status' => 200,
                'message' => "Brand updated successful"
            ]);

        }else{
            return response()->json([
                'status' => 500,
                'error' => $validator->errors(),
            ]);
        }


    }

    public function destroy(Request $request){
        $brand = Brand::find($request->id);

        //checking brand not found
        if($brand == null){
            return response([
               'status' => 404,
               'message' => "Brand not found with id "+$request->id
            ]);
        }else{
            $brand->delete();
            return response([
               'status' => 200,
               'message' => "Brand deleted successful",
            ]);
        }

        
    }
}
