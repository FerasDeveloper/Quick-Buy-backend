<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;
    protected $fillable = [
      'name',
  ];

  // public function store() {
  //   return $this->hasMany(Store::class);
  // }

  // public function customer() {
  //   return $this->hasMany(Customer::class);
  // }
  
}
