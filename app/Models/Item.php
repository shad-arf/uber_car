<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $table = 'item'; // Define table name explicitly

    protected $fillable = [
        'title',
        'description',
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
