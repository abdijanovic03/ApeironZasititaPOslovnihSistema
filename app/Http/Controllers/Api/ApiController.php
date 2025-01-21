<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Hash;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ApiController extends Controller
{
   public function register(Request $request) 
   {
      $request->validate([
         "name" => "required|string",
         "email"=> "required|string|email|unique:users",
         "password"=>"required|confirmed"
      ]);
      User::create([
         "name" =>$request->name,
         "email"=>$request->email,
         "password"=>bcrypt($request->password),
         "phone_number"=>$request->phone_number
      ]);

      return response()->json([
         "status"=> 201,
         "message"=>"User registered successfully"
      ]);
   }
   public function login(Request $request) 
   {
      $request->validate([
         "email"=> "required|string|email",
         "password"=>"required"
      ]);

      $user = User::where("email", $request->email)->first();

      if(!empty($user)){
         if(Hash::check($request->password, $user->password)){

            $token = $user->createToken("myToken")->accessToken;

            return response()->json([
               "status"=> "200",
               "message"=>"Logged in successfully",
               "token"=>$token
            ]);
            
         }else{
            return response()->json([
               "status"=> 422,
               "message"=>"Passwords dint match"
            ]);
         }
      }else{
         return response()->json([
            "status"=> 422,
            "message"=>"User not found"
         ]);
      }

   }
   public function profile() 
   {
      $user = Auth::user();
      return response()->json([
         "status" => "200",
         "message" =>"Logged in successfully",
         "user" => $user
      ]);
   }
   public function refreshToken() 
   {
      $user = request()->user();
      $token = $user->createToken("newToken");

      $refreshToken = $token->accessToken;

      return response()->json([
         "status" => 201,
         "message"=> "Refresh token",
         "token"=> $refreshToken
      ]);
   }
   public function logout(Request $request) 
   {
      request()->user()->tokens()->delete();
      
      return response()->json([
         "status" => 200,
         "message"=> "User logged out"
      ]);
   }
}
