<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;
    protected $fillable = [
      'F_name',
      'L_name',
      'number',
      'location',
      'userId'
  ];

  public function user() {
    return $this->belongsTo(User::class);
  }

  public function report() {
    return $this->hasMany(Report::class);
  }

  public function order() {
    return $this->hasMany(Order::class);
  }

  // public function location() {
  //   return $this->belongsToMany(Location::class);
  // }

  public function store() {
    return $this->belongsToMany(Store::class);
  }

}
