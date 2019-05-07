<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Traits;

use CircleLinkHealth\Customer\Entities\Settings;

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
}
