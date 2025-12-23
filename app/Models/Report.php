<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $table = 'reports';

    protected $fillable = [
      'customerId',
      'productId',
  ];

  public function customer() {
    return $this->belongsToMany(Customer::class);
  }

  public function product() {
    return $this->belongsToMany(Product::class);
  }

}
