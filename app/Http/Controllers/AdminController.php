<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use App\Models\Product;
use App\Models\Report;
use App\Models\Store;
use App\Models\UpdateInfo;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{

  public function Show_Wallet()
  {

    $user = Auth::user();
    $wallet = Wallet::query()->where('userId', $user['id'])->first();

    if (isset($wallet)) {
      return response()->json(
        $wallet
      );

      return response()->json([
        'message' => 'Something went wrong.'
      ]);
    }
  }

  public function Charge_Wallet(Request $request)
  {

    $request->validate([
      'accountNumber' => 'required|numeric',
      'balance' => 'required|numeric'
    ]);

    $wallet = Wallet::query()->where('accountNumber', $request['accountNumber'])->first();
    $balance = $wallet['balance'];

    $update = $wallet->update([
      'balance' => $balance + $request['balance']
    ]);

    if ($update) {
      return response()->json([
        'message' => 'Wallet charged Successfully.'
      ]);
    }
    return response()->json([
      'message' => 'Something went wrong.'
    ]);
  }

  public function Block($id)
  {

    $admin = Auth::user();
    $store = Store::query()->where('id', $id)->first();
    $user = User::query()->where('id', $store->userId)->first();

    if ($admin['user_type'] != 1) {
      return response()->json([
        'message' => 'Only Admin can block users.'
      ]);
    }

    if ($user['reporting_state'] == 2) {
      $user->update([
        'reporting_state' => 1
      ]);

      return response()->json([
        'message' => 'User UnBlocked Successfully.'
      ]);
    }

    $block = $user->update([
      'reporting_state' => 2
    ]);

    if ($block) {
      return response()->json([
        'message' => 'User blocked Successfully.'
      ]);
    }

    return response()->json([
      'message' => 'Something went wrong!'
    ]);
  }

  public function Is_Blocked($id)
  {
    $store = Store::query()->where('id', $id)->first();

    if (!$store) {
      return response()->json([
        'success' => false,
        'message' => 'Store not found'
      ], 404);
    }

    $user = User::query()->where('id', $store->userId)->first();

    if (!$user) {
      return response()->json([
        'success' => false,
        'message' => 'User not found'
      ], 404);
    }

    return response()->json([
      'success' => $user->reporting_state == 2
    ]);
  }


  public function Show_Reported_Products()
  {
    $products = Product::get();
    $productsWithCounts = [];

    foreach ($products as $product) {
      $reportCount = Report::where('productId', $product->id)->count();
      if ($reportCount != 0)
        $productsWithCounts[] = [
          'product' => $product,
          'count' => $reportCount,
        ];
    }
    return response()->json($productsWithCounts);
  }

  public function Show_R_Product_Details($id)
  {
    $user = Auth::user();
    if ($user['user_type'] != 1) {
      return response()->json([
        'message' => 'Only Admin can do this.'
      ]);
    }

    $product = Product::query()->where('id', $id)->first();
    $ReportCount = Report::query()->where('productId', $id)->count();

    if (isset($product) && isset($ReportCount)) {
      return response()->json([
        'product' => $product,
        'count' => $ReportCount
      ]);
    }
  }

  public function Show_All_Stores()
  {
    $stores = Store::query()->get();
    return response()->json($stores);
  }

  public function Show_Store_Details($name)
  {
    $user = Auth::user();
    if ($user['user_type'] != 1) {
      return response()->json([
        'message' => 'Only Admin can do this.'
      ]);
    }

    $store = Store::query()->where('name', $name)->first();
    $products = Product::query()->where('storeId', $store['id'])->get();
    $productsCount = Product::query()->where('storeId', $store['id'])->count();
    $reportsCount = 0;

    foreach ($products as $product) {
      $td = Report::query()->where('productId', $product['id'])->count();
      $reportsCount += $td;
    }

    if (isset($store) && isset($productsCount) && isset($reportsCount)) {
      return response()->json([
        $store,
        'products' => $products,
        'productsCount' => $productsCount,
        'reportsCount' => $reportsCount
      ]);
    }
  }

  public function Delete_Product2($id)
  {
    $user = Auth::user();
    if ($user['user_type'] != 1) {
      return response()->json([
        'message' => 'Only Admin can do this.'
      ]);
    }

    $delete = Product::query()->where('id', $id)->delete();

    if ($delete) {
      return response()->json([
        'message' => 'Product deleted Succefully.'
      ]);
    }
    return response()->json([
      'message' => 'Something went wrong!'
    ]);
  }

  public function Delete_Store($id)
  {

    $user = Auth::user();
    if ($user->user_type != 1) {
      return response()->json([
        'message' => 'Only Admin can do this.'
      ]);
    }

    $store = Store::query()->where('id', $id)->first();
    $curUser = User::query()->where('id', $store->userId)->first();
    $delete = $curUser->delete();

    if ($delete) {
      return response()->json([
        'message' => 'Store deleted Successfully'
      ]);
    }
    return response()->json([
      'message' => 'Something went wrong'
    ]);
  }

  public function Show_Updates()
  {

    $user = Auth::user();

    if ($user->user_type != 1) {
      return response()->json([
        'message' => 'only admin can do this'
      ]);
    }

    $updates = UpdateInfo::query()->get();

    if (isset($updates)) {
      return response()->json(
        $updates
      );
    }

    return response()->json([
      'message' => 'Something went wrong'
    ]);
  }

  public function Accept_Request($id, Request $request)
  {

    $request->validate([
      'domain' => 'required',
    ]);

    $user = Auth::user();

    if ($user->user_type != 1) {
      return response()->json([
        'message' => 'only admin can do this'
      ]);
    }

    $update = UpdateInfo::query()->where('id', $id)->first();
    $store = Store::query()->where('id', $update->storeId)->first();

    $domain = Domain::query()->where('name', $request->domain)->first();
    $delete = false;
    if (isset($domain)) {
      $store->update([
        'name' => $update->name,
        'description' => $update->description,
        'number' => $update->number,
        'location' => $update->location,
        'domainId' => $domain->id,
      ]);
      $update->delete();
      $delete = true;
    } else {
      $newDomain = Domain::query()->create([
        'name' => $request->domain
      ]);
      $store->update([
        'name' => $update->name,
        'description' => $update->description,
        'number' => $update->number,
        'location' => $update->location,
        'domainId' => $newDomain->id,
      ]);
      $update->delete();
      $delete = true;
    }

    if ($delete) {
      return response()->json([
        'message' => 'Request Accepted Successfully'
      ]);
    }
    return response()->json([
      'message' => 'Something went wrong'
    ]);
  }

  public function Reject_Request($id)
  {

    $user = Auth::user();
    if ($user->user_type != 1) {
      return response()->json([
        'message' => 'only admin can do this'
      ]);
    }

    $delete = UpdateInfo::query()->where('id', $id)->delete();
    if ($delete) {
      return response()->json([
        'message' => 'Request Rejected Successfully'
      ]);
    }
    return response()->json([
      'message' => 'Something went wrong'
    ]);
  }
}
