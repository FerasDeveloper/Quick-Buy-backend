<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Domain extends Model
{
    use HasFactory;
    protected $fillable = [
      'name',
  ];

  public function store()
  {
    return $this->hasMany(Store::class);
  }

  public function product()
  {
    return $this->hasMany(Product::class);
  }
}
