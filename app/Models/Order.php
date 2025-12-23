<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable = [
      'customerId',
      // 'storeId',
      // 'status'
  ];

  public function customer() {
    return $this->belongsToMany(Customer::class);
  }

  // public function store() {
  //   return $this->belongsToMany(Store::class);
  // }

  public function order_product(){
    return $this->hasMany(Order_product::class);
  }
}
