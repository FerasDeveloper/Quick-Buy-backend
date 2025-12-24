<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use App\Models\Product;
use App\Models\Report;
use App\Models\Store;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{

  public function Create_Product(Request $request)
  {

    $request->validate([
      'name' => 'required',
      'description' => 'required',
      'price' => 'required|numeric',
      'amount' => 'required|numeric',
      'available' => 'required',
      'image' => 'required|mimes:jpeg,bmp,png,jpg,gif|max:2048',
    ]);
    $user = Auth::user();
    $store = Store::query()->where('userId', $user['id'])->first();
    $domain = Domain::query()->where('id', $store['domainId'])->first();
    $wallet = Wallet::query()->where('userId', $user['id'])->first();

    if ($wallet['offerId'] == 4) {
      return response()->json([
        'message' => 'Please choose an offer so you can publish.'
      ]);
    }

    if ($request->amount < 1) {
      return response()->json([
        'message' => "Amount must be more than 0"
      ]);
    }

    if ($wallet['postsNumber'] != 0) {

      $product = new Product();
      $product->name = $request->name;
      $product->description = $request->description;
      $product->price = $request->price;
      $product->amount = $request->amount;
      $product->available = $request->available;
      $product->domainId = $domain['id'];
      $product->storeId = $store['id'];

      if ($request->hasFile('image')) {
          $imagePath = $request->file('image')->store('images', 'public');
          $product->image = asset('storage/' . $imagePath);
          $product->save();
      }

      $wallet->postsNumber = $wallet->postsNumber - 1;
      $wallet->save();
      
      if (isset($product)) {
        return response()->json([
          'message' => 'Product has added Successfully.'
        ]);
      }
      return response()->json([
        'message' => 'Please fill the fields.'
      ]);
    } else {
      return response()->json([
        'message' => 'The number of posts you can publish has expired.'
      ]);
    }
  }

  public function Show_All()
  {

    $products = Product::query()->get();
    return response()->json([
      $products
    ]);
  }

  public function Show_By_Owner()
  {

    $user = Auth::user();
    $store = Store::query()->where('userId', $user['id'])->first();
    $products = Product::query()->where('storeId', $store['id'])->get();

    if (isset($products)) {
      return response()->json([
        $products
      ]);
    }

    return response()->json([
      'message' => 'There is no Results.'
    ]);
  }

  public function Show_With_Domain(Request $request)
  {

    $request->validate([
      'domain' => 'required'
    ]);

    $domain = Domain::query()->where('name', $request['domain'])->first();
    $domainId = $domain['id'];
    $products = Product::query()->where('domainId', $domainId)->get();

    if ($products) {
      return response()->json([
        $products
      ]);
    }
  }

  public function Edit_Product(Request $request, $id)
  {
    // التحقق من صحة البيانات
    $request->validate([
      'name' => 'required',
      'description' => 'required',
      'price' => 'required|numeric',
      'available' => 'required',
      'amount' => 'required|numeric'
    ]);

    // جلب المنتج الحالي
    $product = Product::find($id);

    if (!$product) {
      return response()->json([
        'message' => 'Product not found.'
      ], 404);
    }

    // تحديث البيانات الأساسية
    $product->name = $request['name'];
    $product->description = $request['description'];
    $product->price = $request['price'];
    $product->available = $request['available'];
    $product->amount = $request['amount'];

    // إذا تم تحميل صورة جديدة
    if ($request->hasFile('image')) {
      // حذف الصورة القديمة إذا كانت موجودة
      if ($product->image && Storage::disk('public')->exists($product->image)) {
        Storage::disk('public')->delete($product->image);
      }

      // حفظ الصورة الجديدة
      $imagePath = $request->file('image')->store('images', 'public');
      $product->image = asset('storage/' . $imagePath);
    }

    // حفظ التحديثات
    $product->save();

    return response()->json([
      'message' => 'Product Edited Successfully.'
    ]);
  }
  public function Delete_Product($id)
  {

    $user = Auth::user();
    $store = Store::query()->where('userId', $user['id'])->first();

    $product = Product::query()->where('id', $id)->first();

    if ($product['storeId'] == $store['id']) {

      $delete = $product->delete();

      if ($delete) {
        return response()->json([
          'message' => 'Product deleted Successfully.'
        ]);
      } else {
        return response()->json([
          'message' => 'Something went wrong.'
        ]);
      }
    } else {
      return response()->json([
        'message' => 'You can not delete this product.'
      ]);
    }
  }

  public function Show_Details($id)
  {

    $product = Product::query()->where('id', $id)->first();
    $store = Store::query()->where('id', $product['storeId'])->first();
    $reportCount = Report::query()->where('productId', $product->id)->count();

    return response()->json([
      $product,
      $store['name'],
      $reportCount
    ]);
  }

  public function Show_Domains()
  {
    $domain = Domain::query()->get();
    return $domain;
  }

  public function Show_Domain($id){

    $domain = Domain::query()->where('id', $id)->first();
    return $domain;
  }
}
