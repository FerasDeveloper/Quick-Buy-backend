<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UpdateInfo extends Model
{
    use HasFactory;

    protected $fillable = [
      'name',
      'description',
      'number',
      'location',
      'specificDomain',
      'storeId'
    ];

    public function store() {
      return $this->hasOne(Store::class);
    }

}
