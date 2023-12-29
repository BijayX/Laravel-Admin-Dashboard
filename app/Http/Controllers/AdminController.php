<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


class AdminController extends Controller
{
    public function AdminDashboard(){
        return view('admin.index');
    }

    

    public function AdminLogout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('login');
    }
    public function AdminLogin(){
        return view('auth.login');
        
    }
    public function AdminRegister(){
        return view('auth.register');
    }
    public function AdminRegisterStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'regex:/^[a-zA-Z\s]+$/'],
            'email' => ['required', 'email', 'unique:admins'],
            'password' => ['required', 'confirmed'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

       

        $notification = [
            'message' => 'Admin registration successful. Please log in.',
            'alert-type' => 'success',
        ];

        // return redirect()->route('admin.login')->with($notification);
    }
    public function AdminProfile(){
        $id= Auth:: user()->id;
        $profileData=User:: find($id);
        return view('admin.admin_profile_view',compact('profileData'));
        
    }
    public function AdminProfileStore(Request $request){
        $id= Auth:: user()->id;
        $data=User:: find($id);
        $data->username= $request->username;
        $data->name= $request->name;
        $data->email= $request->email;
        $data->phone= $request->phone;
        $data->address= $request->address;

        if($request->file('photo')){
            $file=$request->file('photo');
            @unlink(public_path('upload/admin_images/'.$data->photo));
            $filename=date('YmdHi').$file->getClientOriginalName();
            $file->move(public_path('upload/admin_images'),$filename);
            $data['photo']=$filename;
        }
        $data->save();
        
        $notification=array(
            'message' => 'Admin Profile Update Successfully',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);

    }
    public function AdminChangePassword(){
        $id= Auth:: user()->id;
        $profileData=User:: find($id);
        return view('admin.admin_change_password',compact('profileData'));
    }
    public function AdminUpdatepassword(Request $request){
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|confirmed'
        ]);
        if(!Hash::check($request->old_password,auth::user()->password)){
            $notification=array(
                'message' => 'Old Password Doesn`t Matched !',
                'alert-type' => 'error'
            );
            return back()->with($notification);
        }
        User::whereId(auth()->user()->id)->update([
            'password' =>Hash::make($request->new_password)
        ]);
        $notification=array(
            'message' => 'Password Change Successfully ',
            'alert-type' => 'success'
        );
        return back()->with($notification);

    }
    // public function AdminRegisterStore(Request $request)
    // {
    //     $request->validate([
    //         'name' => ['required', 'regex:/^[a-zA-Z\s]+$/'],
    //         'username' => ['required', 'regex:/^[a-zA-Z]+$/']
    //     ]);
    
       
    // }
    
}

