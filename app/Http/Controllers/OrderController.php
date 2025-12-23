<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Order_product;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use function PHPUnit\Framework\isEmpty;

class OrderController extends Controller
{

  public function Show()
  {
    $user = Auth::user();
    $store = Store::where('userId', $user->id)->first();
    $customer = Customer::where('userId', $user->id)->first();
    if ($store) {
      $orders = Order_product::where('storeId', $store->id)->get();
      
      if ($orders->isNotEmpty()) {
        $formattedOrders = [];

        foreach ($orders as $order) {
          $currOrder = Order::query()->where('id', $order->orderId)->first();
          $currCustomer = Customer::query()->where('id', $currOrder->customerId)->first();
          $currUser = User::query()->where('id', $currCustomer->userId)->first();
          $formattedOrders[] = [
            'order' => $order,
            'customerName' => $currUser->username
          ];
        }

        return response()->json([
          'success' => true,
          'data' => $formattedOrders
        ]);
      }

      return response()->json([
        'success' => false,
        'message' => 'No orders found for this store.'
      ]);
    } elseif ($customer) {
      $orders = Order::where('customerId', $customer->id)->get();

      if ($orders->isNotEmpty()) {
        $formattedOrders = [];

        foreach ($orders as $order) {
          $ss = Order_product::query()->where('orderId', $order->id)->get();
          $arr = [];
          foreach ($ss as $s) {
            $currStore = Store::query()->where('id', $s->storeId)->first();
            $currUser = User::query()->where('id', $currStore->userId)->first();
            $arr[] = [
              'storeName' => $currStore->name,
              'username' => $currUser->username
            ];
          }
          $formattedOrders[] = [
            'order' => $order,
            'storesName' => $arr
          ];
        }

        return response()->json([
          'success' => true,
          'data' => $formattedOrders
        ]);
      }
    }

    return response()->json([
      'success' => false,
      'message' => 'No orders found for this customer.'
    ]);
  }

  // elseif (isset($customer)) {
  //   $orders = Order::query()->where('customerId', $customer['id'])->get();
  //   $ordersCount = Order::query()->where('customerId', $customer['id'])->count();
  //   if ($ordersCount != 0) {
  //     return response()->json([
  //       $orders
  //     ]);
  //   }
  //   return response()->json([
  //     'message' => 'There is no result.',
  //   ]);
  // }

  public function Show_Mini_Orders($id)
  {

    $order = Order::query()->where('id', $id)->first();
    $orders = Order_product::query()->where('orderId', $order['id'])->get();
    if (isset($orders)) {
      $data = [];
      foreach ($orders as $order) {
        $product = Product::query()->where('id', $order->productId)->first();
        $data[] = [
          'product' => $product,
          'status' => $order->status,
          'amount' => $order->amount
        ];
      }
      return response()->json([
        $data
      ]);
    }
    return response()->json([
      'message' => 'There is no results.'
    ]);
  }

  public function Delete_Mini_Order($id)
  {

    $mini_order = Order_product::query()->where('productId', $id)->first();
    $order = Order::query()->where('id', $mini_order->orderId)->first();

    if (isset($mini_order)) {
      $delete = $mini_order->delete();
      if ($delete) {
        $remainingOrders = Order_product::where('orderId', $order->id)->count();
        if ($remainingOrders == 0) {
          $order->delete();
          return response()->json([
            'message' => 'Order is Empty.'
          ]);;
        }
        return response()->json([
          'message' => 'Order deleted Successfully.'
        ]);
      }
    }
    return response()->json([
      'message' => 'Something went wrong!'
    ]);
  }


  public function Create_Order(Request $request)
  {
    DB::beginTransaction();

    try {
      $validated = $request->validate([
        'products' => 'required',
      ]);

      $user = Auth::user();
      $customer = Customer::where('userId', $user->id)->firstOrFail();

      $order = Order::create(['customerId' => $customer->id]);

      foreach ($request->products as $product) {
        Log::info("Creating product: " . json_encode($product));
        Order_product::create([
          'amount' => $product['amount'],
          'orderId' => $order->id,
          'productId' => $product['productId'],
          'storeId' => $product['storeId']
        ]);
      }

      DB::commit();

      return response()->json([
        'message' => 'Order created successfully',
        'orderId' => $order->id
      ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
      DB::rollBack();
      return response()->json(['errors' => $e->errors()], 422);
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error("Error: " . $e->getMessage()); // وهنا
      return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
    }
  }

  public function Update_Order($id, Request $request)
  {
    if ($request->amount <= 0) {
      return response()->json([
        'message' => 'amount must be more than 0'
      ]);
    }

    $product = Product::query()->where('id', $id)->first();

    if($request->amount > $product->amount){
      return response()->json([
        'message' => "the max product's amount is " . $product->amount
      ]);
    }

    $miniOrder = Order_product::query()->where('productId', $id)->first();

    if ($miniOrder) {
      $miniOrder->amount = $request->amount;
      $miniOrder->save();

      return response()->json([
        'message' => 'Quantity updated successfully'
      ]);
    }

    return response()->json([
      'message' => 'Update failed'
    ], 404);
  }

  public function Delete_Order($id)
  {

    $delete = Order::query()->where('id', $id)->delete();
    if (isset($delete)) {
      return response()->json([
        'message' => 'Order deleted Successfully.'
      ]);
    }

    return response()->json([
      'message' => 'Something went wrong!'
    ]);
  }

  public function Edit_Order(Request $request, $id)
  {
    $request->validate([
      'status' => 'required'
    ]);
    $order = Order_product::query()->where('id', $id)->first();
    $update = $order->update([
      'status' => $request->status
    ]);
    if($update){
      return response()->json([
        'message' => 'Order updated Successfully'
      ]);
    }
    return response()->json([
      'message' => 'Something went wrong.'
    ]);
  }

}
