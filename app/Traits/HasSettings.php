<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 16/03/2017
 * Time: 1:24 AM
 */

namespace App\Traits;


use App\Settings;

trait HasSettings
{
    /**
     * Get the settings.
     */
    public function settings()
    {
        return $this->morphMany(Settings::class, 'settingsable', 'settingsable_type', 'settingsable_id');
    }

    /**
     * Updates or Creates settings.
     *
     * @param Settings $settings
     */
    public function syncSettings(Settings $settings)
    {
        $args = [];

        foreach ($settings->getFillable() as $fieldName) {
            $args[$fieldName] = $settings->{$fieldName};
        }

        Settings::updateOrCreate([
            'settingsable_type' => self::class,
            'settingsable_id'   => $this->id,
        ], $args);
    }
}