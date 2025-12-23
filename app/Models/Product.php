<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class   Product extends Model
{
  use HasFactory;
  protected $fillable = [
    'name',
    'description',
    'price',
    'amount',
    'available',
    'image',
    'domainId',
    'storeId',
  ];

  public function customer()
  {
    return $this->belongsToMany(Domain::class);
  }

  public function order()
  {
    return $this->belongsToMany(Order::class);
  }

  public function report()
  {
    return $this->hasMany(Report::class);
  }

  public function order_product(){
    return $this->belongsTo(Order_product::class);
  }

  public function store()
  {
    return $this->belongsToMany(Store::class);
  }
  // مشان بحث الموقع
  public function stores()
  {
    return $this->belongsTo(Store::class, 'storeId');
  }
}
