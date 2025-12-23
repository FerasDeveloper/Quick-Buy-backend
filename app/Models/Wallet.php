<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;
    protected $fillable = [
      'accountNumber',
      'balance',
      'userId',
      'postsNumber',
      'offerId'
  ];

  public function user() {
    return $this->belongsTo(User::class);
  }

  public function offer() {
    return $this->belongsTo(Offer::class);
  }

}
