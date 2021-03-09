<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SelfEnrollment\Providers;

use CircleLinkHealth\SelfEnrollment\Console\Commands\CommandsToUpdateOnProduction\InviteEnrolleesOnDemand;
use CircleLinkHealth\SelfEnrollment\Console\Commands\CommandsToUpdateOnProduction\MakeSurveyOnlyUsersForEnrollees;
use CircleLinkHealth\Core\Providers\CoreServiceProvider;
use CircleLinkHealth\SelfEnrollment\Console\Commands\CommandsToUpdateOnProduction\SendSelfEnrollmentRemindersCommand;
use CircleLinkHealth\SelfEnrollment\Console\Commands\EnrollmentFinalAction;
use CircleLinkHealth\SelfEnrollment\Console\Commands\GenerateNbiLetterCommand;
use CircleLinkHealth\SelfEnrollment\Console\Commands\GenerateSelfEnrollmentLetters;
use CircleLinkHealth\SelfEnrollment\Console\Commands\GenerateSelfEnrollmentSurveyCommand;
use CircleLinkHealth\SelfEnrollment\Console\Commands\ManuallyCreateEnrollmentTestData;
use CircleLinkHealth\SelfEnrollment\Console\Commands\SelfEnrollmentManualInviteCommand;
use CircleLinkHealth\SelfEnrollment\Console\Commands\SelfEnrollmentSendErrorFixedCommand;
use CircleLinkHealth\SelfEnrollment\Console\Commands\SendSelfEnrollmentReminders;
use CircleLinkHealth\SelfEnrollment\Console\Commands\UpdateEnrolmentLettersSignatoryName;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use CircleLinkHealth\SharedModels\Observers\EnrolleeObserver;
use Illuminate\Support\ServiceProvider;

class SelfEnrollmentProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Enrollee::observe(EnrolleeObserver::class);
        $this->publishConfigurations();
        $this->publishPublicAssets();
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'selfEnrollment');
        $this->publishes([
            __DIR__.'/../Resources/views' => resource_path('views/vendor/selfEnrollment'),
        ]);
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->commands([
            SelfEnrollmentManualInviteCommand::class,
            SendSelfEnrollmentReminders::class,
            ManuallyCreateEnrollmentTestData::class,
            EnrollmentFinalAction::class,
            GenerateSelfEnrollmentLetters::class,
            GenerateSelfEnrollmentSurveyCommand::class,
            SelfEnrollmentSendErrorFixedCommand::class,
            SendSelfEnrollmentRemindersCommand::class,
            MakeSurveyOnlyUsersForEnrollees::class,
            InviteEnrolleesOnDemand::class
        ]);
        $this->app->register(RouteServiceProvider::class);
        $this->app->register(CoreServiceProvider::class);
    }

    private function publishConfigurations()
    {
        $this->publishes(
            [
                __DIR__.'/../Config/services.php' => config_path('selfEnrollment.php'),
            ],
            'config'
        );

        $this->mergeConfigFrom(
            __DIR__.'/../Config/services.php',
            'selfEnrollment'
        );
    }

    private function publishPublicAssets()
    {
//        $this->publishes([
//            __DIR__.'/../Public' => public_path('vendor/selfEnrollment'),
//        ], 'public');
    }
}
