<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Domain;
use App\Models\Following;
use App\Models\Location;
use App\Models\Offer;
use App\Models\Product;
use App\Models\Report;
use App\Models\Store;
use App\Models\UpdateInfo;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;


class TaskController extends Controller
{

  public function Show_Profile()
  {
    $user = Auth::user();
    $response = $user['user_type'] == 3 ? [
      'userInfo' => $user,
      'profileInfo' => null,
      'domain' => null
    ] : [
      'userInfo' => $user,
      'profileInfo' => null
    ];

    if ($user->user_type == 2) {
      $customer = Customer::where('userId', $user->id)->first();
      $response['profileInfo'] = $customer;
    } elseif ($user->user_type == 3) {
      $store = Store::where('userId', $user->id)->first();
      $domain = Domain::query()->where('id', $store['domainId'])->select('name')->first();
      $response['profileInfo'] = $store;
      $response['domain'] = $domain;
    }

    return response()->json($response);
  }

  public function Edit_Profile(Request $request)
  {
    $user = Auth::user();
    $type = $user->user_type;

    $currentUser = User::find($user->id);
    $currentStore = Store::where('userId', $user->id)->first();

    if (isset($currentStore)) {
      $updateInfo = UpdateInfo::where('storeId', $currentStore->id)->first();
      if ($updateInfo) {
        return response()->json(['message' => 'Your request is under review']);
      }
    }

    $commonValidation = [
      'username' => [
        'required',
        Rule::unique('users')->ignore($currentUser->id)
      ],
      'email' => [
        'required',
        'email',
        Rule::unique('users')->ignore($currentUser->id)
      ],
      'password' => [
        'required',
        'min:5'
      ]
    ];

    if ($type == 2) {
      $request->validate(array_merge($commonValidation, [
        'F_name' => 'required',
        'L_name' => 'required',
        'number' => 'required|numeric',
        'location' => 'required'
      ]));

      $customer = Customer::where('userId', $user->id)->first();

      $customer->update([
        'F_name' => $request->F_name !== $customer->F_name ? $request->F_name : $customer->F_name,
        'L_name' => $request->L_name !== $customer->L_name ? $request->L_name : $customer->L_name,
        'number' => $request->number != $customer->number ? $request->number : $customer->number,
        'location' => $request->location !== $customer->location ? $request->location : $customer->location,
      ]);
    } elseif ($type == 3) {
      $request->validate(array_merge($commonValidation, [
        'name' => 'required',
        'description' => 'required',
        'number' => 'required|numeric',
        'location' => 'required',
        'domain' => 'required'
      ]));

      $currentD = $currentStore->domainId;
      $currentDomain = Domain::query()->where('id', $currentD)->first();

      if ($request->domain !== $currentDomain->name) {

        $RequestDomain = Domain::query()->where('name', $request->domain)->first();
        if (isset($RequestDomain)) {
          if ($RequestDomain !== $currentDomain->name) {
            $storeUpdates = [
              'name' => $request->name !== $currentStore->name ? $request->name : $currentStore->name,
              'description' => $request->description !== $currentStore->description ? $request->description : $currentStore->description,
              'number' => $request->number != $currentStore->number ? $request->number : $currentStore->number,
              'location' => $request->location !== $currentStore->location ? $request->location : $currentStore->location,
              'domainId' => $RequestDomain->id
            ];
          }
        } else {
          UpdateInfo::query()->create([
            'name' => $request->name,
            'description' => $request->description,
            'number' => $request->number,
            'location' => $request->location,
            'specificDomain' => $request->domain,
            'storeId' => $currentStore->id
          ]);

          return response()->json([
            'message' => 'Your request has sent.'
          ]);
        }
      } else {
        $storeUpdates = [
          'name' => $request->name !== $currentStore->name ? $request->name : $currentStore->name,
          'description' => $request->description !== $currentStore->description ? $request->description : $currentStore->description,
          'number' => $request->number != $currentStore->number ? $request->number : $currentStore->number,
          'location' => $request->location !== $currentStore->location ? $request->location : $currentStore->location,
          'domainId' => $currentDomain->id
        ];
      }
      $currentStore->update($storeUpdates);
    }

    $userUpdates = [];
    if ($request->username !== $currentUser->username) $userUpdates['username'] = $request->username;
    if ($request->email !== $currentUser->email) $userUpdates['email'] = $request->email;
    $userUpdates['password'] = $request->password;

    if (!empty($userUpdates)) {
      $currentUser->update($userUpdates);
    }

    return response()->json(['message' => 'Success']);
  }

  public function Show_Details($name)
  {

    $store = Store::query()->where('name', $name)->first();
    $products = Product::query()->where('storeId', $store['id'])->get();

    return response()->json([
      $store,
      'products' => $products
    ]);
  }


  public function Search_In_Domains(Request $request)
  {

    $letterss = $request['letters'];
    $letters = strtolower($letterss);
    $query = Domain::query();

    foreach (str_split($letters) as $letter) {
      $query->whereRaw('LOWER(name) LIKE ?', ['%' . $letter . '%']);
    }

    $domains = $query->get();

    return response()->json([
      $domains
    ]);
  }

  public function Search_In_Locations(Request $request)
  {

    $letterss = $request['letters'];
    $letters = strtolower($letterss);
    $query = Store::query();

    foreach (str_split($letters) as $letter) {
      $query->whereRaw('LOWER(location) LIKE ?', ['%' . $letter . '%'])->select('location');
    }

    $locations = $query->get();

    return response()->json([
      $locations
    ]);
  }

  public function Search(Request $request)
  {
    $domainSearch = strtolower($request->input('domain'));
    $locationSearch = $request->input('location');

    if (empty($domainSearch) && empty($locationSearch)) {
      return response()->json(['message' => 'No Result']);
    }

    $domainIds = [];
    if (!empty($domainSearch)) {
      $domainIds = Domain::whereRaw('LOWER(name) LIKE ?', ["%{$domainSearch}%"])
        ->pluck('id')
        ->toArray();
    }

    $storeIds = [];
    if (!empty($locationSearch)) {
      $storeIds = Store::where('location', $locationSearch)
        ->pluck('id')
        ->toArray();
    }

    $query = Product::query();

    if (!empty($domainIds)) {
      $query->whereIn('domainId', $domainIds);
    }

    if (!empty($storeIds)) {
      $query->whereIn('storeId', $storeIds);
    }

    $products = $query->latest()->get();

    if ($products->isNotEmpty()) {
      return response()->json([$products->unique('id')->values()]);
    } else {
      return response()->json(['message' => 'No Result']);
    }
  }

  // public function Search(Request $request)
  //   {

  //     $domain = Domain::query()->where('name', $request['domain'])->first();
  //     $location = Store::query()->where('location', $request['location'])->select('location')->first();

  //     if (isset($domain)) {
  //       if (isset($location)) {
  //         $stores = Store::query()->where('location', $location['location'])->get();
  //         foreach ($stores as $store) {
  //           $product = Product::query()->where('domainId', $domain['id'])
  //             ->where('storeId', $store['id'])->latest()->get();
  //         }
  //       } else {
  //         $product = Product::query()->where('domainId', $domain['id'])->latest()->get();
  //       }

  //       if (isset($product))
  //         return response()->json([
  //           $product
  //         ]);

  //       return response()->json([
  //         'message' => 'No Result'
  //       ]);
  //     }
  //     if (isset($location)) {
  //       $stores = Store::query()->where('location', $location['location'])->get();
  //       foreach ($stores as $store) {
  //         $product = Product::query()->where('storeId', $store['id'])->latest()->get();
  //       }
  //     }
  //     if (isset($product))
  //       return response()->json([
  //         $product
  //       ]);

  //     return response()->json([
  //       'message' => 'No Result'
  //     ]);
  //   }

  public function SearchInOwnerProduct(Request $request)
  {
    $user = Auth::user();
    $store = Store::where('userId', $user->id)->first();

    $letters = strtolower($request->domain);

    $domains = Domain::whereRaw('LOWER(name) LIKE ?', ["%{$letters}%"])
      ->get();

    $products = collect();

    foreach ($domains as $domain) {
      $products = $products->merge(
        Product::query()
          ->where('domainId', $domain->id)
          ->where('storeId', $store->id)
          ->latest()
          ->get()
      );
    }

    return response()->json([
      $products->unique('id')->values()
    ]);
  }

  public function SearchInFollowingProduct(Request $request)
  {

    $domainSearch = strtolower($request->input('domain'));
    $locationSearch = $request->input('location');

    if (empty($domainSearch) && empty($locationSearch)) {
      return response()->json(['message' => 'No Result']);
    }

    $domainIds = [];
    if (!empty($domainSearch)) {
      $domainIds = Domain::whereRaw('LOWER(name) LIKE ?', ["%{$domainSearch}%"])
        ->pluck('id')
        ->toArray();
    }

    $storeIds = [];
    if (!empty($locationSearch)) {
      $storeIds = Store::where('location', $locationSearch)
        ->pluck('id')
        ->toArray();
    }

    $query = Product::query();

    if (!empty($domainIds)) {
      $query->whereIn('domainId', $domainIds);
    }

    if (!empty($storeIds)) {
      $query->whereIn('storeId', $storeIds);
    }

    $products = $query->latest()->get();

    $user = Auth::user();
    $customer = Customer::query()->where('userId', $user->id)->first();
    $followings = Following::query()->where('customerId', $customer->id)->get();

    $data = [];
    foreach ($followings as $following) {
      $store = Store::query()->where('id', $following->storeId)->first();
      foreach ($products as $product) {
        if ($product->storeId == $store->id) {
          $data[] =
            $product;
        }
      }
    }

    if (!empty($data)) {
      return response()->json($data);
    } else {
      return response()->json(['message' => 'No Result']);
    }
  }

  public function Show_Offers()
  {
    $offers = Offer::query()->get();
    $user = Auth::user();
    $wallet = Wallet::query()->where('userId', $user['id'])->first();

    if ($wallet['offerId'] != 4) {

      if ($wallet['postsNumber'] == 0) {
        $offer = Offer::query()->where('id', $wallet['offerId'])->first();
        return response()->json([
          'message' => 'recharge your offer.',
          $wallet,
          $offer,
          $offers
        ]);
      }
      $offer = Offer::query()->where('id', $wallet['offerId'])->first();
      return response()->json([
        'message' => 'You have already selected an offer.',
        $wallet,
        $offer,
        $offers
      ]);
    }

    return response()->json([
      'message' => 'choose an offer.',
      $wallet,
      $offers
    ]);
  }

  public function Select_Offer($id)
  {
    $user = Auth::user();
    $wallet = Wallet::query()->where('userId', $user['id'])->first();
    $balance = $wallet['balance'];
    $postsNumber = $wallet['postsNumber'];
    $offer = Offer::query()->where('id', $id)->first();

    if ($wallet['offerId'] != 4 && $postsNumber != 0 && $wallet['offerId'] == $offer['id']) {
      $wallet->update([
        'postsNumber' => 0,
        'offerId' => 4
      ]);
      return response()->json([
        'message' => 'Done'
      ]);
    }

    if ($postsNumber == 0) {
      if ($balance >= $offer['price']) {
        if ($offer['id'] == $wallet['offerId']) {
          $wallet->update([
            'balance' => $balance - $offer['price'],
            'postsNumber' => $postsNumber + $offer['amount'],
          ]);
          return response()->json([
            'message' => 'Offer recharghed Successfully.'
          ]);
        }
        $wallet->update([
          'balance' => $balance - $offer['price'],
          'postsNumber' => $postsNumber + $offer['amount'],
          'offerId' => $offer['id']
        ]);
        return response()->json([
          'message' => 'Offer selected Successfully.'
        ]);
      }
    }

    if ($balance < $offer['price']) {
      return response()->json([
        'message' => 'Charge your wallet first.'
      ]);
    }

    $wallet->update([
      'balance' => $balance - $offer['price'],
      'postsNumber' => $offer['amount'],
      'offerId' => $offer['id']
    ]);
    return response()->json([
      'message' => 'Offer changed Successfully.'
    ]);
  }

  public function Cancel_Offer()
  {

    $user = Auth::user();
    $wallet = Wallet::query()->where('userId', $user->id)->first();

    $update = $wallet->update([
      'postsNumber' => 0,
      'offerId' => 4
    ]);

    if ($update) {
      return response()->json([
        'message' => 'Done.'
      ]);
    }
    return response()->json([
      'message' => 'Something went wrong.'
    ]);
  }

  public function Recharge_Offer()
  {

    $user = Auth::user();
    $wallet = Wallet::query()->where('userId', $user->id)->first();
    $offer = Offer::query()->where('id', $wallet->offerId)->first();

    if ($wallet->postsNumber > 0) {
      return response()->json([
        'message' => 'Your Offer has available posts to publish.'
      ]);
    }

    if ($wallet->balance < $offer->price) {
      return response()->json([
        'message' => 'Your money is not enough.'
      ]);
    }

    $update = $wallet->update([
      'postsNumber' => $offer->amount,
      'balance' => $wallet->balance - $offer->price
    ]);

    if ($update) {
      return response()->json([
        'message' => 'Done.'
      ]);
    }

    return response()->json([
      'message' => 'Something went wrong.'
    ]);
  }

  public function Follow($id)
  {

    $user = Auth::user();
    $customer = Customer::query()->where('userId', $user['id'])->first();
    $store = Store::query()->where('id', $id)->first();

    $prev = Following::query()
      ->where([
        ['customerId', $customer['id']],
        ['storeId', $store['id']]
      ])->first();

    if (isset($prev)) {

      $prev->delete();
      return response()->json([
        'message' => 'You Unfollowed this store Successfully.'
      ]);
    }

    $follow = Following::query()->create([
      'customerId' => $customer['id'],
      'storeId' => $store['id']
    ]);

    if ($follow) {
      return response()->json([
        'message' => 'You followed this store Successfully.'
      ]);
    }

    return response()->json([
      'message' => 'Something went wrong!'
    ]);
  }

  public function IsFollowed($name){

    $user = Auth::user();
    $customer = Customer::query()->where('userId', $user->id)->first();
    $store = Store::query()->where('name', $name)->first();
    $follow = Following::query()->where('customerId', $customer->id)->where('storeId', $store->id)->first();

    if(isset($follow)){
      return response()->json([
        'success' => 'true'
      ]);
    }
    return response()->json([
      'success' => 'false'
    ]);
  }

  public function Show_Following_Products()
  {
    $user = Auth::user();
    $customer = Customer::query()->where('userId', $user['id'])->first();
    $stores = Following::query()->where('customerId', $customer['id'])->get();

    $products = [];
    foreach ($stores as $store) {
      $storeProducts = Product::query()->where('storeId', $store['storeId'])->get();
      $products = array_merge($products, $storeProducts->toArray());
    }

    if ($products != []) {
      return response()->json($products);
    }

    return response()->json([
      'message' => 'There is no Results.'
    ]);
  }

  public function Show_Followers()
  {

    $user = Auth::user();
    $store = Store::query()->where('userId', $user->id)->first();

    $followers = Following::query()->where('storeId', $store->id)->get();

    if (isset($followers)) {
      $data = [];
      foreach ($followers as $follower) {
        $customer = Customer::query()->where('id', $follower->customerId)->first();
        $user2 = User::query()->where('id', $customer->userId)->first();
        $data[] = [
          'username' => $user2->username
        ];
      }
      return response()->json([
        'success' => true,
        'data' => $data
      ]);
    }
    return response()->json([
      'success' => false,
    ]);
  }

  public function Show_Following(){

    $user = Auth::user();
    $customer = Customer::query()->where('userId', $user->id)->first();
    $followings = Following::query()->where('customerId', $customer->id)->get();

    if(isset($followings)){
      $data = [];
      foreach ($followings as $following) {
        $store = Store::query()->where('id', $following->storeId)->first();
        $data[] = [
          'name' => $store->name
        ];
      }
      return response()->json([
        'success' => true,
        'data' => $data
      ]);
    }
    return response()->json([
      'success' => false
    ]);
  }

  public function Report($id)
  {

    $user = Auth::user();
    $customer = Customer::query()->where('userId', $user['id'])->first();
    $product = Product::query()->where('id', $id)->first();

    $prev = Report::query()->where([
      'customerId' => $customer['id'],
      'productId' => $product['id']
    ])->first();

    if (isset($prev)) {
      $prev->delete();
      return response()->json([
        'message' => 'Report on this post has been cancelled Successfully.'
      ]);
    }

    $report = Report::query()->create([
      'customerId' => $customer['id'],
      'productId' => $product['id']
    ]);

    if ($report) {
      return response()->json([
        'message' => 'Post has been Reported Successfully.'
      ]);
    }

    return response()->json([
      'message' => 'Something went wrong!'
    ]);
  }

  public function Is_Reported($id)
  {

    $user = Auth::user();
    $customer = Customer::query()->where('userId', $user->id)->first();
    $product = Product::query()->where('id', $id)->first();

    $report = Report::query()->where('customerId', $customer->id)->where('productId', $product->id)->first();

    if(isset($report)){
      return response()->json([
        'success' => 'true'
      ]);
    }
    return response()->json([
      'success' => 'false'
    ]);
  }
}
