<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Providers;

use App\Call;
use App\CarePlan;
use App\CarePlanTemplate;
use App\EligibilityBatch;
use App\Models\CCD\Problem;
use App\Models\ProblemCode;
use App\Observers\CallObserver;
use App\Observers\CarePlanObserver;
use App\Observers\CarePlanTemplateObserver;
use App\Observers\EligibilityBatchObserver;
use App\Observers\HolidayObserver;
use App\Observers\NurseContactWindowObserver;
use App\Observers\PageTimerObserver;
use App\Observers\PatientMonthlySummaryObserver;
use App\Observers\PatientObserver;
use App\Observers\PracticeObserver;
use App\Observers\ProblemCodeObserver;
use App\Observers\ProblemObserver;
use App\Observers\RevisionObserver;
use App\Observers\SaasAccountObserver;
use App\Observers\UserObserver;
use CircleLinkHealth\Customer\Entities\Holiday;
use CircleLinkHealth\Customer\Entities\NurseContactWindow;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\SaasAccount;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\TimeTracking\Entities\PageTimer;
use Illuminate\Support\ServiceProvider;
use Venturecraft\Revisionable\Revision;

class ObserversServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        CarePlan::observe(CarePlanObserver::class);
        CarePlanTemplate::observe(CarePlanTemplateObserver::class);
        EligibilityBatch::observe(EligibilityBatchObserver::class);
        NurseContactWindow::observe(NurseContactWindowObserver::class);
        Holiday::observe(HolidayObserver::class);
        PageTimer::observe(PageTimerObserver::class);
        Patient::observe(PatientObserver::class);
        PatientMonthlySummary::observe(PatientMonthlySummaryObserver::class);
        Practice::observe(PracticeObserver::class);
        ProblemCode::observe(ProblemCodeObserver::class);
        Revision::observe(RevisionObserver::class);
        SaasAccount::observe(SaasAccountObserver::class);
        User::observe(UserObserver::class);
        Call::observe(CallObserver::class);
        Problem::observe(ProblemObserver::class);
    }

    /**
     * Register the application services.
     */
    public function register()
    {
    }
}
