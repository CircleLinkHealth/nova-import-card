<?php

namespace App\Providers;

use Illuminate\Foundation\Providers\ArtisanServiceProvider;

class CPMArtisanServiceProvider extends ArtisanServiceProvider
{
    public function __construct(\Illuminate\Contracts\Foundation\Application $app)
    {
        parent::__construct($app);
        
        $this->disableCoreArtisanCommands();
    }
    
    /**
     * Disables specific artisan commands
     */
    private function disableCoreArtisanCommands()
    {
        //Remove the ability to create migrations, in favor of having migrations in a separate repository
        //https://github.com/CircleLinkHealth/module-migrations
        unset($this->devCommands['MigrateMake']);
    }
}
