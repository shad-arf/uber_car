<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeedBack extends Model
{
    use HasFactory;
      // Mass-assignable fields
      protected $table = 'feed_backs';
      protected $fillable = [
        'name',
        'message',
    ];

}
