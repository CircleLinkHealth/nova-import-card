<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

/**
 * App\UserMeta.
 *
 * @property int                                                                            $umeta_id
 * @property int                                                                            $user_id
 * @property string|null                                                                    $meta_key
 * @property string|null                                                                    $meta_value
 * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
 * @property \App\User                                                                      $wpUser
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserMeta whereMetaKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserMeta whereMetaValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserMeta whereUmetaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserMeta whereUserId($value)
 * @mixin \Eloquent
 */
class UserMeta extends BaseModel
{
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['umeta_id', 'user_id', 'meta_key', 'meta_value'];
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'umeta_id';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'usermeta';

    public function wpUser()
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }
}
