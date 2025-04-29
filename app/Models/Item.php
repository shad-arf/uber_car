<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $table = 'items'; // Define table name explicitly

    protected $fillable = [
        'title',
        'description',
        'address',
        'date',
        'phone',
        'is_taken',
        'user_id',
        'destination',
        'time',
        'post_type',
        
    ];

    /**
     * Relationship: An item belongs to a user.
     */
    public function users()
    {
        return $this->belongsTo(User::class);
    }
}
