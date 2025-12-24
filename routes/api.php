<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TaskController;
use Illuminate\Console\View\Components\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

// Route::get('/img', [AuthController::class, 'showImage']);



// Auth
Route::post('/CreateCustomer', [AuthController::class, 'CreateCustomer']);
Route::post('/CreateStore', [AuthController::class, 'CreateStore']);
Route::post('/LogIn', [AuthController::class, 'LogIn']);
Route::post('/Check/{username}', [AuthController::class, 'Check']);
Route::get('/ResendCode/{username}', [AuthController::class, 'Resend_Code']);
Route::get('/ShowDomains', [ProductController::class, 'Show_Domains']);
Route::get('/ShowDomain/{id}', [ProductController::class, 'Show_Domain']);
Route::get('/test-files', function () {
    return Storage::files('public/images');
});
Route::get('/images/{filename}', function ($filename) {
    $path = storage_path('app/public/images/' . $filename);

    if (!File::exists($path)) {
        abort(404);
    }

    return response()->file($path);
});
Route::middleware('auth:sanctum')->group(function () {
  
  Route::get('/Logout', [AuthController::class, 'Logout']);
  
  //Profile
  Route::get('/ShowProfile', [TaskController::class, 'Show_Profile']);
  Route::Post('/EditProfile', [TaskController::class, 'Edit_Profile']);
  Route::get('/ShowStoreDetails/{name}', [TaskController::class, 'Show_Details']);

  //Product
  Route::get('/ShowWallet', [AdminController::class, 'Show_Wallet']);
  Route::Post('/ChargeWallet', [AdminController::class, 'Charge_Wallet']);
  Route::Post('/CreateProduct', [ProductController::class, 'Create_Product']);
  Route::Post('/EditProduct/{id}', [ProductController::class, 'Edit_Product']);
  Route::get('/DeleteProduct/{id}', [ProductController::class, 'Delete_Product']);
  Route::get('/ShowProducts', [ProductController::class, 'Show_All']);
  Route::get('/ShowByOwner', [ProductController::class, 'Show_By_Owner']);
  Route::get('/ShowDetails/{id}', [ProductController::class, 'Show_Details']);
  Route::get('/Report/{id}', [TaskController::class, 'Report']);
  Route::get('/IsReported/{id}', [TaskController::class, 'Is_Reported']);

  //offer
  Route::get('/ShowOffers', [TaskController::class, 'Show_Offers']);
  Route::get('/SelectOffer/{id}', [TaskController::class, 'Select_Offer']);
  Route::get('/CancelOffer', [TaskController::class, 'Cancel_Offer']);
  Route::get('/RechargeOffer', [TaskController::class, 'Recharge_Offer']);

  //Search
  Route::Post('/ShowWithDomain', [ProductController::class, 'Show_With_Domain']);
  Route::Post('/SearchInDomains', [TaskController::class, 'Search_In_Domains']);
  Route::Post('/SearchInLocations', [TaskController::class, 'Search_In_Locations']);
  Route::Post('/Search', [TaskController::class, 'Search']);
  Route::Post('/SearchInOwnerProduct', [TaskController::class, 'SearchInOwnerProduct']);
  Route::Post('/SearchInFollowingProduct', [TaskController::class, 'SearchInFollowingProduct']);

  //Order
  Route::post('/CreateOrder', [OrderController::class, 'Create_Order']);
  Route::get('/ShowOrders', [OrderController::class, 'Show']);
  Route::get('/DeleteOrder/{id}', [OrderController::class, 'Delete_Order']);
  Route::post('/EditOrder/{id}', [OrderController::class, 'Edit_Order']);
  Route::get('/ShowMiniOrders/{id}', [OrderController::class, 'Show_Mini_Orders']);
  Route::get('/DeleteMiniOrder/{id}', [OrderController::class, 'Delete_Mini_Order']);
  Route::post('/UpdateMiniOrder/{id}', [OrderController::class, 'Update_Order']);

  //Follow
  Route::get('/Follow/{id}', [TaskController::class, 'Follow']);
  Route::get('/IsFollowed/{name}', [TaskController::class, 'IsFollowed']);
  Route::get('/ShowFollowingProducts', [TaskController::class, 'Show_Following_Products']);
  Route::get('/ShowFollowers', [TaskController::class, 'Show_Followers']);
  Route::get('/ShowFollowing', [TaskController::class, 'Show_Following']);

  //Admin
  Route::get('/Block/{id}', [AdminController::class, 'Block']);
  Route::get('/IsBlocked/{id}', [AdminController::class, 'Is_Blocked']);
  Route::get('/ShowReportedProducts', [AdminController::class, 'Show_Reported_Products']);
  Route::get('/ShowRProductDetails/{id}', [AdminController::class, 'Show_R_Product_Details']);
  Route::get('/ShowAllStores', [AdminController::class, 'Show_All_Stores']);
  Route::get('/ShowStoreDetails2/{name}', [AdminController::class, 'Show_Store_Details']);
  Route::get('/DeleteProduct2/{id}', [AdminController::class, 'Delete_Product2']);
  Route::get('/DeleteStore/{id}', [AdminController::class, 'Delete_Store']);
  Route::get('/ShowUpdates', [AdminController::class, 'Show_Updates']);
  Route::post('/AcceptRequest/{id}', [AdminController::class, 'Accept_Request']);
  Route::get('/RejectRequest/{id}', [AdminController::class, 'Reject_Request']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
  return $request->user();
});
