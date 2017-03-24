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
     * Sync settings.
     *
     * @param Settings $settings
     */
    public function syncSettings(Settings $settings)
    {
        $args = [];

        foreach ($settings->getFillable() as $fieldName) {
            $args[$fieldName] = $settings->{$fieldName} ?? false;
        }

        if ($this->settings->isEmpty()) {
            return $this->settings()->create($args);
        }

        $settings = $this->settings()->delete();

        return $this->settings()->create($args);
    }

    /**
     * Get the settings.
     */
    public function settings()
    {
        return $this->morphMany(Settings::class, 'settingsable', 'settingsable_type', 'settingsable_id');
    }
}