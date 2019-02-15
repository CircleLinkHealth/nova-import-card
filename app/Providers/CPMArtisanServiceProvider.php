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
        //remove migration commands from devCommands
        $devCommands = [];
        foreach ($this->devCommands as $key => $value) {
            if ( ! str_contains($key, ['Migrate'])) {
                $devCommands[$key] = $value;
            }
        }
        $this->devCommands = $devCommands;
        
        //remove migration commands from commands
        $commands = [];
        foreach ($this->commands as $key => $value) {
            if ( ! str_contains($key, ['Migrate'])) {
                $commands[$key] = $value;
            }
        }
        $this->commands = $commands;
    }
}
