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
     */
    public function syncSettings(Settings $settings)
    {
        $args = [];

        foreach ($settings->getFillable() as $fieldName) {
            $args[$fieldName] = $settings->{$fieldName} ?? false;
        }

        if ($this->settings()->exists()) {
            $deleted = $this->settings()->delete();
        }

        $created = $this->settings()->create($args);

        $this->load('settings');

        return $created;
    }
}
