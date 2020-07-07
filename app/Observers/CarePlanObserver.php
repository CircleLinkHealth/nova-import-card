<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Observers;

use App\Events\CarePlanWasProviderApproved;
use App\Events\CarePlanWasQAApproved;
use App\Events\CarePlanWasRNApproved;
use App\Events\PdfableCreated;
use App\Services\Calls\SchedulerService;
use Carbon\Carbon;
use CircleLinkHealth\Customer\AppConfig\PatientSupportUser;
use CircleLinkHealth\SharedModels\Entities\CarePlan;
use CircleLinkHealth\SharedModels\Entities\CpmInstruction;
use CircleLinkHealth\SharedModels\Entities\CpmMisc;

class CarePlanObserver
{
    public function addCarePlanPrintedNote(CarePlan $carePlan)
    {
        $date = $carePlan->first_printed->setTimezone($carePlan->patient->timezone ?? 'America/New_York')->format(
            'm/d/Y'
        );
        $time = $carePlan->first_printed->setTimezone($carePlan->patient->timezone ?? 'America/New_York')->format(
            'g:i A T'
        );

        $note = $carePlan->patient->notes()->create(
            [
                'author_id'    => PatientSupportUser::id(),
                'body'         => "Care plan printed for mailing on ${date} at ${time}",
                'type'         => 'CarePlan Printed',
                'performed_at' => Carbon::now()->toDateTimeString(),
            ]
        );
    }

    public function creating(CarePlan $carePlan)
    {
        if ( ! $carePlan->patient) {
            \Log::critical("CarePlan with id:{$carePlan->id} has no patient");

            return;
        }

        if ($carePlan->patient->practice('upg')) {
            $cpmMisc = CpmMisc::whereName('Other')->first();

            $instruction = CpmInstruction::updateOrCreate(
                [
                    'is_default' => true,
                    'name'       => '- Take all of your medications as prescribed.

- Exercise your heart and muscles regularly.

- Maintain a healthy weight.

- Eat heart-healthy foods and avoid overeating.

- Please notify your care team if you are in the hospital by calling (844) 968-1800.

- Get a flu shot every year but check with your provider first. Ask your provider if you should get a pneumococcal (pneumonia) vaccine, tetanus (Tdap) vaccine or a zoster (shingles) vaccine.',
                ]
            );

            $patientMiscExists = $carePlan->patient->cpmMiscs()->where('cpm_misc_id', $cpmMisc->id)->first();

            if ($patientMiscExists) {
                $carePlan->patient->cpmMiscs()->detach($patientMiscExists);
            }

            $patientMisc = $carePlan->patient->cpmMiscs()->attach(
                $cpmMisc->id,
                [
                    'cpm_instruction_id' => $instruction->id,
                ]
            );
        }
    }

    public function saved(CarePlan $carePlan)
    {
        if ($this->shouldScheduleCall($carePlan)) {
            $carePlan->provider_approver_id = null;
            $carePlan->provider_date        = null;
            /** @var SchedulerService $schedulerService */
            $schedulerService = app()->make(SchedulerService::class);
            $schedulerService->ensurePatientHasScheduledCall($carePlan->patient, self::class);
        }

        if ($carePlan->isDirty('first_printed')) {
            $carePlan->load('patient');
            $this->addCarePlanPrintedNote($carePlan);
        }

        if ($carePlan->isDirty('status')) {
            if (CarePlan::RN_APPROVED == $carePlan->status) {
                event(new CarePlanWasRNApproved($carePlan->patient));
            }

            if (CarePlan::QA_APPROVED == $carePlan->status) {
                event(new CarePlanWasQAApproved($carePlan->patient));
            }

            if (CarePlan::PROVIDER_APPROVED == $carePlan->status) {
                event(new CarePlanWasProviderApproved($carePlan->patient));
                event(new PdfableCreated($carePlan));
            }
        }
    }

    /**
     * Listen to the CarePlan saving event.
     */
    public function saving(CarePlan $carePlan)
    {
        if ( ! array_key_exists('care_plan_template_id', $carePlan->getAttributes())) {
            $carePlan->care_plan_template_id = getDefaultCarePlanTemplate()->id;
        }
    }

    private function shouldScheduleCall(CarePlan $carePlan): bool
    {
        if ( ! $carePlan->isDirty('status')) {
            return false;
        }

        if (CarePlan::QA_APPROVED == $carePlan->status) {
            return true;
        }

        return false;
    }
}
