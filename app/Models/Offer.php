<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'price', 'amount'];

    public function wallet() {
      return $this->hasMany(Wallet::class);
    }
}
