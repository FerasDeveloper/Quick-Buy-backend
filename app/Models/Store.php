<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;
    protected $fillable = [
      'name',
      'description',
      'number',
      'userId',
      'domainId',
      'location',
      'specificDomain'
  ];

  public function user() {
    return $this->belongsTo(User::class);
  }

  public function domain() {
    return $this->belongsToMany(Domain::class);
  }

  // public function location() {
  //   return $this->belongsToMany(Location::class);
  // }

  public function customer() {
    return $this->belongsToMany(Customer::class);
  }

  public function product() {
    return $this->hasMany(Product::class);
  }

  // public function order() {
  //   return $this->belongsToMany(Order::class);
  // }
  public function order_product() {
    return $this->belongsToMany(Order_product::class);
  }

  public function updateInfo() {
    return $this->belongsTo(UpdateInfo::class);
  }

}
