<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Providers;

use Illuminate\Foundation\Providers\ArtisanServiceProvider;

class CpmArtisanServiceProvider extends ArtisanServiceProvider
{
    public function __construct($app)
    {
        parent::__construct($app);

        if (array_key_exists('MigrateMake', $this->devCommands)) {
            unset($this->devCommands['MigrateMake']);
        }
    }
}
