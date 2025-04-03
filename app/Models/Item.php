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
    ];

    /**
     * Relationship: An item belongs to a user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
