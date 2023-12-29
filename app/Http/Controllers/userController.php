<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


class UserController extends Controller
{
     public function UserDashboard(){
          return view('user.index');
      }
  
      
  
      public function UserLogout(Request $request): RedirectResponse
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
      public function UserProfile(){
          $id= Auth:: user()->id;
          $profileData=User:: find($id);
          return view('user.user_profile_view',compact('profileData'));
          
      }
      public function UserProfileStore(Request $request){
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
      public function UserChangePassword(){
          $id= Auth:: user()->id;
          $profileData=User:: find($id);
          return view('user.user_change_password',compact('profileData'));
      }
      public function UserUpdatepassword(Request $request){
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
    }
