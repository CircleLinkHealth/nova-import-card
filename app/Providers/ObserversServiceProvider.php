<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Providers;

use CircleLinkHealth\SharedModels\Entities\Call;
use App\Models\Addendum;
use CircleLinkHealth\SharedModels\Entities\Note;
use App\Observers\AddendumObserver;
use App\Observers\AppConfigObserver;
use App\Observers\CallObserver;
use App\Observers\CarePlanObserver;
use App\Observers\CarePlanTemplateObserver;
use App\Observers\EligibilityBatchObserver;
use App\Observers\EnrolleeObserver;
use App\Observers\HolidayObserver;
use App\Observers\MedicationObserver;
use App\Observers\NoteObserver;
use App\Observers\NurseContactWindowObserver;
use App\Observers\NurseInvoiceDailyDisputeObserver;
use App\Observers\NurseInvoiceExtrasObserver;
use App\Observers\OutgoingSmsObserver;
use App\Observers\PageTimerObserver;
use App\Observers\PatientMonthlySummaryObserver;
use App\Observers\PatientObserver;
use App\Observers\PracticeObserver;
use App\Observers\ProblemCodeObserver;
use App\Observers\ProblemObserver;
use App\Observers\RevisionObserver;
use App\Observers\SaasAccountObserver;
use App\Observers\TwilioCallObserver;
use App\Observers\UserObserver;
use App\OutgoingSms;
use App\TwilioCall;
use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\Customer\Entities\Holiday;
use CircleLinkHealth\Customer\Entities\NurseContactWindow;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\SaasAccount;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\EligibilityBatch;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use CircleLinkHealth\SharedModels\Entities\NurseInvoiceDailyDispute;
use CircleLinkHealth\NurseInvoices\Entities\NurseInvoiceExtra;
use CircleLinkHealth\Revisionable\Entities\Revision;
use CircleLinkHealth\SharedModels\Entities\CarePlan;
use CircleLinkHealth\SharedModels\Entities\CarePlanTemplate;
use CircleLinkHealth\SharedModels\Entities\Medication;
use CircleLinkHealth\SharedModels\Entities\Problem;
use CircleLinkHealth\SharedModels\Entities\ProblemCode;
use CircleLinkHealth\SharedModels\Entities\PageTimer;
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
        OutgoingSms::observe(OutgoingSmsObserver::class);
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
        TwilioCall::observe(TwilioCallObserver::class);
    }

    /**
     * Register the application services.
     */
    public function register()
    {
    }
}
