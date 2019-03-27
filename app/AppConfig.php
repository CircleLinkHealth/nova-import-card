<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

/**
 * App\AppConfig.
 *
 * @property int            $id
 * @property string         $config_key
 * @property string         $config_value
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AppConfig whereConfigKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AppConfig whereConfigValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AppConfig whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AppConfig whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AppConfig whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class AppConfig extends \CircleLinkHealth\Core\Entities\BaseModel
{
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['config_key', 'config_value'];

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'app_config';
}
