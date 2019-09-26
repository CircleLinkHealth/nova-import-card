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
 *
 * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AppConfig newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AppConfig newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AppConfig query()
 *
 * @property int|null $revision_history_count
 */
class AppConfig extends \CircleLinkHealth\Core\Entities\BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['config_key', 'config_value'];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'app_config';

    /**
     * Returns the AppConfig value for the given key.
     *
     * @param string $key
     * @param null   $default
     *
     * @return string|null
     */
    public static function pull(string $key, $default = null)
    {
        $conf = static::whereConfigKey($key)->first();

        return $conf
            ? $conf->config_value
            : $default;
    }
}
