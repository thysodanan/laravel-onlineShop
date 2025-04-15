<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
   
    public function index()
    {
        
        return view('back-end.category');

    }

    public function list(){
        $categories = Category::orderBy("id","DESC")->get();

        return response([
            'status' => 200,
            'categories' => $categories
        ]);
    }


    public function upload(Request $request){

        $validator = Validator::make($request->all(),[
            'image' =>'required'
        ]);

        if($validator->passes()){
            
            $file = $request->file('image');
               
            $imageName = rand(0,999999999) .'.'. $file->getClientOriginalExtension();
            $file->move('uploads/temp', $imageName);

            return response()->json([
                'status' => 200,
                'message' => 'Image Uploaded success',
                'image' => $imageName
            ]);
           
        }else{
            return response()->json([
                'status' => 500,
                'error' => $validator->errors(),
            ]);
        }

        

    }

    public function cancel(Request $request){
        if($request->image){
            $tempDir = public_path("uploads/temp/$request->image");
            if(File::exists($tempDir)){
                File::delete($tempDir);
                return response()->json([
                   'status' => 200,
                   'message' => 'Image Cancelled Successfully',
                ]);
            }
        }
    }
   
   
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(),[
            'name' => 'required|unique:categories,name'
        ]);

        if($validator->passes()){

            //store to db
            $category = new Category();
            $category->name = $request->name;
            $category->status = $request->status;

            //change image directory
            if($request->input("category_image")){

                $tempDir = public_path("uploads/temp/".$request->input("category_image"));
                $cateDir = public_path("uploads/category/".$request->input("category_image"));
    
                if(File::exists($tempDir)){
                    File::copy($tempDir,$cateDir);
                    File::delete($tempDir);
                }
    
                $category->image = $request->input("category_image");
            }
           
            $category->save();

            return response([
                'status' => 200,
                'message' => "Category Created successful"
            ]);

        }else{

            return response()->json([
                'status' => 500,
                'error' => $validator->errors(),
            ]);
        }
        
        
    }

   

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request)
    {
        $category = Category::find($request->id);

        //checking category not found
        if($category == null){

            return response([
               'status' => 404,
               'message' => "Category not found with id "+$request->id
            ]);

        }else{
            return response([
               'status' => 200,
               'category' => $category
            ]);
        }

        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $category = Category::find($request->category_id);

        //checking category not found
        if($category == null){

            return response([
               'status' => 404,
               'message' => "Category not found with id "+$request->category_id
            ]);

        }

        $validator = Validator::make($request->all(),[
            'name' => 'required|unique:categories,name,'.$request->category_id,
        ]);

        if($validator->passes()){
            //update category
            $category->name = $request->name;
            $category->status = $request->status;

            //change image directory
            if($request->input('category_image')){
                $tempDir = public_path("uploads/temp/".$request->input("category_image"));
                $cateDir = public_path("uploads/category/".$request->input("category_image"));
    
                if(File::exists($tempDir)){
                    File::copy($tempDir,$cateDir);
                    File::delete($tempDir);
                }

                //remove old image from category directory
                $cateDir = public_path("uploads/category".$category->image);
                if(File::exists($cateDir)){
                    File::delete($cateDir);
                }

                $image = $request->input("category_image");

            }else if($request->input('cate_old_image')){
                $image = $request->input("cate_old_image");
            }

            $category->image = $image;
            $category->save();

            return response([
               'status' => 200,
               'message' => "Category updated successful"
            ]);
        }else{
            return response()->json([
               'status' => 500,
                'error' => $validator->errors(),
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $category = Category::find($request->id);

        //checking category not found
        if($category == null){

            return response([
               'status' => 404,
               'message' => "Category not found with id "+$request->id
            ]);

        }

        if($category->image != null){
            $cateDir = public_path("uploads/category".$category->image);
            if(File::exists($cateDir)){
                File::delete($cateDir);
            }
        }

        //delete category from db
        $category->delete();

        return response([
           'status' => 200,
           'message' => "Category deleted successful",
        ]);

       
    }
}
