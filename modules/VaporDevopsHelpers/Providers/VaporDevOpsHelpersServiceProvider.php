<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\VaporDevOpsHelpers\Providers;

use CircleLinkHealth\Core\Vapor\DevOpsHelpers\Commands\DeleteAllSecrets;
use CircleLinkHealth\Core\Vapor\DevOpsHelpers\Commands\SyncEnvFiles;
use CircleLinkHealth\Core\Vapor\DevOpsHelpers\Commands\UploadSecretsFromFile;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class VaporDevOpsHelpersServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function provides()
    {
        return [
            UploadSecretsFromFile::class,
            DeleteAllSecrets::class,
            SyncEnvFiles::class,
        ];
    }

    public function register()
    {
        return $this->commands([
            UploadSecretsFromFile::class,
            DeleteAllSecrets::class,
            SyncEnvFiles::class,
        ]);
    }
}
