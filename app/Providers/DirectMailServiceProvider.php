<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Providers;

use App\Contracts\DirectMail;
use App\Services\PhiMail\IncomingMessageHandler;
use App\Services\PhiMail\PhiMail;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class DirectMailServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
    }

    public function provides()
    {
        return [DirectMail::class];
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->app->bind(
            DirectMail::class,
            function () {
                // Return a fake if we are unit testing.

                // From Michalis:
                // I put this in while trying to make CircleCI not fail. I suspect it's because tests trigger DM
                // This is a just-in-case. I don't know if it will actually get hit ever, or if laravel notification testing covers it. In any case, even if this gets hit, it won't cause issues.

                if ($this->app->environment('testing')) {
                    new class() implements DirectMail {
                        /**
                         * @return mixed
                         */
                        public function receive()
                        {
                            // TODO: Implement receive() method.
                        }

                        /**
                         * @param $outboundRecipient
                         * @param null $binaryAttachmentFilePath
                         * @param null $binaryAttachmentFileName
                         * @param null $ccdaAttachmentPath
                         * @param null $body
                         * @param null $subject
                         *
                         * @return mixed
                         */
                        public function send(
                            $outboundRecipient,
                            $binaryAttachmentFilePath = null,
                            $binaryAttachmentFileName = null,
                            $ccdaAttachmentPath = null,
                            User $patient = null,
                            $body = null,
                            $subject = null
                        ) {
                            // TODO: Implement send() method.
                        }
                    };
                }

                return new PhiMail(
                    app()->make(IncomingMessageHandler::class)
                );
            }
        );
    }
}
