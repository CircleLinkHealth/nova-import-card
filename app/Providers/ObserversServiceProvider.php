<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Providers;

use App\Observers\AddendumObserver;
use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\Core\Observers\AppConfigObserver;
use CircleLinkHealth\Customer\Entities\Holiday;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\NurseContactWindow;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\SaasAccount;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Observers\HolidayObserver;
use CircleLinkHealth\Customer\Observers\LocationObserver;
use CircleLinkHealth\Customer\Observers\NurseContactWindowObserver;
use CircleLinkHealth\Customer\Observers\PatientMonthlySummaryObserver;
use CircleLinkHealth\Customer\Observers\PracticeObserver;
use CircleLinkHealth\Customer\Observers\SaasAccountObserver;
use CircleLinkHealth\Customer\Observers\UserObserver;
use CircleLinkHealth\Eligibility\Entities\EligibilityBatch;
use CircleLinkHealth\Eligibility\Observers\EligibilityBatchObserver;
use CircleLinkHealth\Revisionable\Entities\Revision;
use CircleLinkHealth\Revisionable\Observers\RevisionObserver;
use CircleLinkHealth\SharedModels\Entities\Addendum;
use CircleLinkHealth\SharedModels\Entities\Call;
use CircleLinkHealth\SharedModels\Entities\CarePlan;
use CircleLinkHealth\SharedModels\Entities\CarePlanTemplate;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use CircleLinkHealth\SharedModels\Entities\Medication;
use CircleLinkHealth\SharedModels\Entities\Note;
use CircleLinkHealth\SharedModels\Entities\NurseInvoiceDailyDispute;
use CircleLinkHealth\SharedModels\Entities\NurseInvoiceExtra;
use CircleLinkHealth\SharedModels\Entities\PageTimer;
use CircleLinkHealth\SharedModels\Entities\Problem;
use CircleLinkHealth\SharedModels\Entities\ProblemCode;
use CircleLinkHealth\SharedModels\Observers\CallObserver;
use CircleLinkHealth\SharedModels\Observers\CarePlanObserver;
use CircleLinkHealth\SharedModels\Observers\CarePlanTemplateObserver;
use CircleLinkHealth\SharedModels\Observers\EnrolleeObserver;
use CircleLinkHealth\SharedModels\Observers\MedicationObserver;
use CircleLinkHealth\SharedModels\Observers\NoteObserver;
use CircleLinkHealth\SharedModels\Observers\NurseInvoiceDailyDisputeObserver;
use CircleLinkHealth\SharedModels\Observers\NurseInvoiceExtrasObserver;
use CircleLinkHealth\SharedModels\Observers\PageTimerObserver;
use CircleLinkHealth\SharedModels\Observers\PatientObserver;
use CircleLinkHealth\SharedModels\Observers\ProblemCodeObserver;
use CircleLinkHealth\SharedModels\Observers\ProblemObserver;
use Illuminate\Support\ServiceProvider;

class ObserversServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        AppConfig::observe(AppConfigObserver::class);
        CarePlan::observe(CarePlanObserver::class);
        CarePlanTemplate::observe(CarePlanTemplateObserver::class);
        EligibilityBatch::observe(EligibilityBatchObserver::class);
        NurseContactWindow::observe(NurseContactWindowObserver::class);
        Holiday::observe(HolidayObserver::class);
        Medication::observe(MedicationObserver::class);
        Note::observe(NoteObserver::class);
        NurseInvoiceExtra::observe(NurseInvoiceExtrasObserver::class);
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
        NurseInvoiceDailyDispute::observe(NurseInvoiceDailyDisputeObserver::class);
        Addendum::observe(AddendumObserver::class);
        Enrollee::observe(EnrolleeObserver::class);
        Location::observe(LocationObserver::class);
    }

    /**
     * Register the application services.
     */
    public function register()
    {
    }
}
