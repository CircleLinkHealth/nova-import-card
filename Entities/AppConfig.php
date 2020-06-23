<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Entities;

use Illuminate\Support\Collection;

/**
 * CircleLinkHealth\Core\Entities\AppConfig.
 *
 * @property int            $id
 * @property string         $config_key
 * @property string         $config_value
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method   static         \Illuminate\Database\Eloquent\Builder|\App\AppConfig whereConfigKey($value)
 * @method   static         \Illuminate\Database\Eloquent\Builder|\App\AppConfig whereConfigValue($value)
 * @method   static         \Illuminate\Database\Eloquent\Builder|\App\AppConfig whereCreatedAt($value)
 * @method   static         \Illuminate\Database\Eloquent\Builder|\App\AppConfig whereId($value)
 * @method   static         \Illuminate\Database\Eloquent\Builder|\App\AppConfig whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\AppConfig newModelQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\AppConfig newQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\AppConfig query()
 * @property int|null                                                                                    $revision_history_count
 */
class AppConfig extends BaseModel
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
     * @var Collection|null
     */
    private static $config = null;

    private static function setup()
    {
        if (static::$config) {
            return;
        }

        static::$config = static::all()->map(function ($item) {
            return [
                'config_key'   => $item->config_key,
                'config_value' => $item->config_value,
            ];
        });
    }

    /**
     * Returns the AppConfig value for the given key.
     *
     * @param null $default
     *
     * @return string|string[]|null
     */
    public static function pull(string $key, $default = null)
    {
        static::setup();
        $conf = static::$config
            ->where('config_key', '=', $key);

        $len = $conf->count();
        if (1 === $len) {
            return $conf->first()['config_value'];
        }

        if ($len > 1) {
            return $conf
                ->map(function ($item) {
                    return $item['config_value'];
                })->toArray();
        }

        return $default;
    }

    public static function set(string $key, $value)
    {
        $conf = AppConfig::updateOrCreate(
            [
                'config_key' => $key,
            ],
            [
                'config_value' => $value,
            ]
        );

        if ($conf) {
            static::setup();
            static::$config->push([
                'config_key'   => $conf->config_key,
                'config_value' => $conf->config_value,
            ]);

            return $conf->config_value;
        }

        return null;
    }
}
