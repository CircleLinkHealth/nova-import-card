<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Addendum extends Model
{
    protected $fillable = [
        'addendumable_type',
        'addendumable_id',
        'author_user_id',
        'body',
    ];

    /**
     * Get all of the owning addendumable models.
     */
    public function addendumable()
    {
        return $this->morphTo();
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }
}
