<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\UserAddress;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use PhpParser\Node\Expr\AssignOp\Concat;

class ProfileController extends Controller
{
    public function index(){

        $user = User::find(Auth::user()->id);
        $contacts = Contact::where('user_id',Auth::user()->id)->get();
        $userAddress = UserAddress::where('user_id',Auth::user()->id)->first();
        return view('back-end.profile',[
            'user'    => $user,
            'contacts' => $contacts,
            'address' => $userAddress
        ]);

       
    }

    public function changePassword(Request $request){
       

        $validator = Validator::make($request->all(),[
            'current_pass' => 'required',
            'new_pass'    => 'required',
            'c_password' => 'required|same:new_pass'
        ]);


        session()->flash('password');
        

        if($validator->passes()){
            $current_password = $request->current_pass;
            $user = User::find(Auth::user()->id);
            if(password_verify($current_password, $user->password)){
                $user->password =  Hash::make($request->new_pass);
                $user->save();
                session()->flash('success', 'Password changed successfully.');
                return redirect()->back();
            }
        }else{
            return redirect()->back()->withInput()->withErrors($validator);
        }
    }

    public function updateProfile(Request $request){

        
        $validator = Validator::make($request->all(),[
            'name' => ['required','string','max:255'],
            'email' => ['required','string','email','max:255','unique:users,email,'.Auth::user()->id],
            'phone' => ['required','string','max:20','unique:users,phone,'.Auth::user()->id],
        ]);

    
        session()->flash('profile');

        #----------------------User Update start--------------------

        if($validator->passes()){
            $user = User::find(Auth::user()->id);
            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone = $request->phone;


            if(!empty($request->image_name)){
                $imageName = $request->image_name;
                $image_path = public_path('uploads/temp/'.$imageName);
                $user_path  = public_path('uploads/user/'.$imageName);
                if(File::exists($image_path)){
                    File::copy($image_path,$user_path);
                    File::delete($image_path);
                }

                $user->image = $imageName;
            }

            $user->save();

            #--------------User update end-----------------------



            //--------------Conact update or create start----------------
            $findContact = Contact::where('user_id',Auth::user()->id)->first();

            if($findContact != null){
                //update user if already conacts exist
                $allContact = Contact::where('user_id',Auth::user()->id)->get();
                $links = $request->link;
                /*
                    "link" => array:2 [▼
                        0 => "http://facebook/phumentpot"
                        1 => "http://telegram/phumentpot"
                    ]
                 */

                for($i=0;$i<count($allContact);$i++){
                    $allContact[$i]->contact_url = $links[$i];
                    $allContact[$i]->save();
                }

            }else{
                //insert user if not exists
                $links = $request->link;
                for($i=0;$i<count($links);$i++){
                    $contact = new Contact();
                    $contact->user_id = Auth::user()->id;
                    $contact->contact_url = $links[$i];
                    $contact->save();
                }
            }

            //-------------Conact update or create end----------------


            #--------------Adress update or create start----------------
            $findAdress = UserAddress::where('user_id',Auth::user()->id)->first();
            if($findAdress != null){
                //update
                $findAdress->address = $request->address;
                $findAdress->save();

            }else{
                //insert
                $adress = new UserAddress();
                $adress->user_id = Auth::user()->id;
                $adress->address = $request->address;
                $adress->save();
            }
            #-------------Adress update or create start----------------

            


            return redirect()->back()->with('success','Profile update successfully.');
        }else{
            return redirect()->back()->withInput()->withErrors($validator);
        }
    }

    public function changeProfileImage(Request $request){
       
        session()->flash('profile');

        if($request->hasFile('image')){
            $image = $request->file('image');
            $name = time().'.'.$image->getClientOriginalExtension();
            $image->move("uploads/temp/", $name);

            return response([
                'status' => 200,
                'image' => $name,
                'message' => 'Image uploaded successfully.'
            ]);
        }else{
            return response()->json([
                'status' => 500,
                'message' => 'Failed to upload image.'
            ]);
        }

        



    }
}
