<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\User;
use GuzzleHttp\Psr7\Message;
use Illuminate\Http\Request;
use PhpParser\Node\Stmt\TryCatch;
use App\Helpers\ResponseFormatter;
use Laravel\Fortify\Rules\Password;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{ 
    // Fungsi Register
    public function register(Request $request)
    {
        try {

            // mendaftarkan field 
          $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'string', new Password]
          ]); 
          
          // memanggil model User
          User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
          ]);

          $user = User::where('email', $request->email)->first();

          // Mengembalikan Token
          $tokenResult = $user->createToken('authToken')->plainTextToken;

          return ResponseFormatter::success([
            'acces_token' => $tokenResult,
            'token_type' => 'Bearer',
            'user' => $user,
          ], 'User Succes Registered');
        }  catch (Exception $error) {
            return ResponseFormatter::error([
               'message' => 'error :)',
               'error' => $error,
              ], 'Authentication Failed', 500);
        }
    }

    // Method Login
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'email|required',
                'password' => 'required',
            ]);

            $credentials = request(['email', 'password']);
            if (!Auth::attempt($credentials)) {
                return ResponseFormatter::error([
                    'message' => 'Unauthorized',
                ], 'Authentication Failed', 500);
            }

            $user = User::where('email', $request->email)->first();

            if (!Hash::check($request->password, $user->password, [])) {
                throw new \Exception('Invalid Credentials');
            }

            $tokenResult = $user->createToken('authToken')->plainTextToken;
            return ResponseFormatter::success([
                'acces_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user
            ], 'Authenticated');
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $error,
            ], 'Authentication Failed', 500);
        }
    }

    public function fetch(Request $request){
     return ResponseFormatter::success($request->user(), 'Data user succes di ambil');
    }

    public function updateProfile(Request $request)
    {   
        // try {
        //     $data = $request->validate([
        //         'name' => 'required|String',
        //         'username' => 'required|String',
        //         'email' => 'required|email',
        //         'phone' => 'required|String'
        //     ]);        
        //     $user = User::user();
        //     $user->updateData(
        //         $data['name'],
        //         $data['username'],
        //         $data['email'],
        //         $data['phone']
        //     );
        //    return ResponseFormatter::success($user, 'success');
        // } catch (Exception $error) {
        //     return ResponseFormatter::error([
        //        'message' => 'Data Invalid',
        //        'error' => $error,
        //     ]);
        // }
        
        // $data = $request->all();
        // $user = Auth::user();
        // $user->update($data);

        // return ResponseFormatter::success($user, 'Update Profile succes');

        //  $data = $request->validate([
        //     'name' => 'required|String',
        //     'username' => 'required|String',
        //     'email' => 'required|email',
        //     'phone' => 'required|String'
        //  ]);

        //   $user = User::findOrFail($id);
        //   $user->name = $request->name;
        //   $user->username = $request->username;
        //   $user->email = $request->email;
        //   $user->phone = $request->phone;

        //   $user->save;

        //   return ResponseFormatter::success($user, 'Profile pengguna telah diperbarui');
        
        
        //  try {
        // $request->validate([
        //             'name' => 'required|String',
        //             'username' => 'required|String',
        //             'email' => 'required|email',
        //             'phone' => 'required|String'
        //          ]);
        //   $data = $request->all();
        //   $user = Auth::user(); 
        //   $user->name = $request->name;
        //   $user->username = $request->username;
        //   $user->email = $request->email;
        //   $user->phone = $request->phone;
        //   $user->save;
          
        //   return ResponseFormatter::success($user, 'Profile pengguna telah diperbarui');
        //  } catch (Exception $error) {
        //      return ResponseFormatter::error([
        //         'message' => 'Profile gagal diperbarui',
        //         'error' => $error,
        //          505
        //      ]);
        //  }
        
           $data =  $request->validate([
                'name' => 'required|string',
                'username' => 'required|string',
                'email' => 'required|email',
                'phone' => 'required|string'
            ]);
            // $data = $request->all();
            $user = Auth::user();
            $user->name = $request->name;
            $user->username = $request->username;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->save;
            // $user->update($data);        
            return ResponseFormatter::success($user, 'Profil pengguna telah diperbarui');
       
        
          
      

 }
     public function logout(Request $request) {
        $token = $request->user()->tokens()->delete();
        return ResponseFormatter::success($token, 'Token is Revorked');
     }
}