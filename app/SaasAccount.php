<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\HasMedia\Interfaces\HasMedia;

class SaasAccount extends \App\BaseModel implements HasMedia
{
    use HasMediaTrait,
        SoftDeletes;

    protected $fillable = [
        'name',
        'logo_path',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function practices() {
        return $this->hasMany(Practice::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users() {
        return $this->hasMany(User::class);
    }
}
