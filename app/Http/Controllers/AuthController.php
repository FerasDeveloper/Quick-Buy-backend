<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Domain;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{

  // public function showImage(){
  //   $product = Product::query()->first();

  //   return view('image', compact('product'));
  // }

  public function CreateCustomer(Request $request)
  {

    $request->validate([
      'username' => 'required|unique:users',
      'F_name' => 'required',
      'L_name' => 'required',
      'email' => 'required|unique:users|email',
      'password' => 'required|min:5',
      'user_type' => 'required',
      'number' => 'required|numeric',
      'location' => 'required'
    ]);

    $code = rand(11111, 99999);

    $user = User::query()->create([
      'username' => $request['username'],
      'email' => $request['email'],
      'password' => $request['password'],
      'user_type' => $request['user_type'],
      'verification_code' => $code
    ]);

    $user['token'] = $user->createToken('AccessToken')->plainTextToken;

    if (isset($user)) {
      $customer = Customer::query()->create([
        'F_name' => $request['F_name'],
        'L_name' => $request['L_name'],
        'userId' => $user['id'],
        'number' => $request['number'],
        'location' => $request['location']
      ]);
    }

    $Message = "Hello {$user->username}!
    \n\n Welcome to Quick Buy. 
    \n There is one more step befor you access the store. verify your email by this verification code:
    \n\n                     {$code} 
    \n\n Thank you for using our Quick Buy.
    \n Best regards.";

    Mail::raw($Message, function ($message) use ($user) {
      $message->to($user->email)
        ->subject('Quick Buy - Email Verification');
    });

    if (isset($customer)) {
      return response()->json([
        'message' => 'success',
        'token' => $user['token'],
        'User_data' => $user,
        'Customer_data' => $customer
      ]);
    }
  }

  public function Resend_Code($username)
  {

    $user = User::query()->where('username', $username)->first();
    $code = rand(11111, 99999);
    $user->update([
      'verification_code' => $code
    ]);

    $Message = "Hello {$user->username}!
    \n\n Welcome to Quick Buy. 
    \n There is one more step befor you access the store. verify your email by this verification code:
    \n\n                     {$code} 
    \n\n Thank you for using our Quick Buy.
    \n Best regards.";

    Mail::raw($Message, function ($message) use ($user) {
      $message->to($user->email)
        ->subject('Quick Buy - Email Verification');
    });

    return response()->json([
      'message' => 'We have sent the code to your email address'
    ]);
  }

  public function Check(Request $request, string $username)
  {
    $request->validate([
      'verification_code' => 'required',
    ]);

    $user = User::query()->where('username', $username)->first();

    if ($request['verification_code'] == $user['verification_code']) {
      return response()->json([
        'message' => 'your email has been verified successfully'
      ]);
    }

    return response()->json([
      'message' => 'the code is wrong, please try again'
    ]);
  }

  public function CreateStore(Request $request)
  {

    $request->validate([
      'username' => 'required|unique:users',
      'name' => 'required',
      'description' => 'required',
      'email' => 'required|unique:users|email',
      'password' => 'required|min:5',
      'user_type' => 'required',
      'number' => 'required|numeric',
      'location' => 'required',
      'domain' => 'required'
    ]);

    $code = rand(11111, 99999);
    $accountNumber = rand(11111, 99999);

    $domain = Domain::query()->where('name', $request['domain'])->first();

    if (isset($domain)) {
      $user = User::query()->create([
        'username' => $request['username'],
        'email' => $request['email'],
        'password' => $request['password'],
        'user_type' => $request['user_type'],
        'verification_code' => $code
      ]);

      $user['token'] = $user->createToken('AccessToken')->plainTextToken;

      $wallet = Wallet::query()->create([
        'accountNumber' => $accountNumber,
        'userId' => $user['id'],
        'offerId' => '4'
      ]);

      if (isset($user)) {
        $store = Store::query()->create([
          'name' => $request['name'],
          'description' => $request['description'],
          'userId' => $user['id'],
          'number' => $request['number'],
          'location' => $request['location'],
          'domainId' => $domain['id']
        ]);
      }

      $Message = "Hello {$user->username}!
    \n\n Welcome to Quick Buy. 
    \n There is one more step befor you access the store. 
        verify your email by this verification code:
    \n\n                     {$code} 
    \n\n Thank you for using our Quick Buy.
    \n Best regards.";

      Mail::raw($Message, function ($message) use ($user) {
        $message->to($user->email)
          ->subject('Quick Buy - Email Verification');
      });

      if (isset($store)) {
        return response()->json([
          'message' => 'success',
          'token' => $user['token'],
          'User_data' => $user,
          'Store_data' => $store
        ]);
      }
    } else {
      $domain = Domain::query()->first();
      $user = User::query()->create([
        'username' => $request['username'],
        'email' => $request['email'],
        'password' => $request['password'],
        'user_type' => $request['user_type'],
        'verification_code' => $code,
        'reporting_state' => '3'
      ]);

      $wallet = Wallet::query()->create([
        'accountNumber' => 0,
        'userId' => $user['id'],
        'offerId' => '4'
      ]);

      if (isset($user)) {
        $store = Store::query()->create([
          'name' => $request['name'],
          'description' => $request['description'],
          'userId' => $user['id'],
          'number' => $request['number'],
          'location' => $request['location'],
          'domainId' => $domain['id'],
          'specificDomain' => $request['domain'],
        ]);
      }

      if (isset($store)) {
        return response()->json([
          'message' => 'success',
          'User_data' => $user,
          'Store_data' => $store
        ]);
      }
    }
  }

  public function LogIn(Request $request)
  {

    $request->validate([
      'login_type' => 'required',
      'password' => 'required'
    ]);

    $user = User::query()->where('email', $request['login_type'])
      ->orWhere('username', $request['login_type'])->first();

    if (!isset($user)) {
      return response()->json([
        'message' => 'Account does not found'
      ]);
    }

    if ($user['reporting_state'] == 3) {
      return response()->json([
        'message' => 'Wait until your request accepted from Admin.'
      ]);
    }

    if ($user['reporting_state'] == 2) {
      return response()->json([
        'message' => 'Sorry but you are blocked from using this site.'
      ]);
    }

    if ($user) {
      if ($user['password'] == $request['password']) {
        if ($user['reporting_state'] == 2) {
          return response()->json([
            'message' => 'You are panned from using this web application.'
          ]);
        }
        if (!($user['user_type'] == 1)) {
          $customer = Customer::where('userId', $user['id'])->first();
          $store = Store::where('userId', $user['id'])->first();
          if (!isset($customer) && !isset($store)) {
            return response()->json([
              'message' => 'you have not complete your register before .. you can not reach this account',
            ]);
          }
        }

        $user['token'] = $user->createToken('AccessToken')->plainTextToken;
        return response()->json([
          'message' => 'Welcome',
          'token' => $user['token'],
          'data' => $user
        ]);
      } else {
        return response()->json([
          'Your Password is incorrect..Please try again'
        ]);
      }
    }
    return response()->json([
      'Email or Username does not match with Password..Please try again'
    ]);
  }

  public function Logout()
  {
    Auth::user()->currentAccessToken()->delete();
    return response()->json([
      'message' => 'You logged out successfully'
    ]);
  }
}
