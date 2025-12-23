<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order_product extends Model
{
    use HasFactory;

    protected $table = 'order_products';

    protected $fillable = [
      'amount',
      'orderId',
      'productId',
      'status',
      'storeId'
  ];

  public function order(){
    return $this->belongsTo(Order::class);
  }

  public function product(){
    return $this->hasOne(Product::class);
  }

  public function store() {
    return $this->belongsToMany(Store::class);
  }
}


// <?php

// namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;

// class Order_product extends Model
// {
//     use HasFactory;

//     protected $table = 'order_products';

//     protected $fillable = [
//       'amount',
//       'orderId',
//       'productId',
//   ];

//   public function order(){
//     return $this->belongsTo(Order::class);
//   }

//   public function product(){
//     return $this->hasOne(Product::class);
//   }
// }
