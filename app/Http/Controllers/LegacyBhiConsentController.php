<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\Http\Requests\CreateLegacyBhiConsentDecision;
use CircleLinkHealth\SharedModels\Entities\Note;
use CircleLinkHealth\SharedModels\Services\SchedulerService;
use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Events\PatientConsentedToService;
use CircleLinkHealth\Core\Exceptions\InvalidArgumentException;
use CircleLinkHealth\Customer\AppConfig\PatientSupportUser;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\Patient;
use Illuminate\Support\Facades\Cache;

class LegacyBhiConsentController extends Controller
{
    public function store(CreateLegacyBhiConsentDecision $request, $practiceId, $patientId)
    {
        if (1 === (int) $request->input('decision')) {
            $note = $this->createNote($patientId, Patient::BHI_CONSENT_NOTE_TYPE);
        } elseif (0 === (int) $request->input('decision')) {
            $note = $this->createNote($patientId, Patient::BHI_REJECTION_NOTE_TYPE);
        } elseif (2 === (int) $request->input('decision')) {
            $this->storeNotNowResponse($patientId);
        }

        return redirect()->back();
    }

    private function createNote($patientId, $type)
    {
        if ( ! in_array($type, [Patient::BHI_REJECTION_NOTE_TYPE, Patient::BHI_CONSENT_NOTE_TYPE])) {
            throw new InvalidArgumentException("`${type}` is not a valid type for a legacy BHI consent note type");
        }
        $consenderName = auth()->user()->display_name;

        if (Patient::BHI_CONSENT_NOTE_TYPE == $type) {
            event(new PatientConsentedToService($patientId, ChargeableService::BHI));
            $body = "The patient consented to receiving BHI services. \n 'Consented action taken by: $consenderName'";
        } else {
            $body = "The patient did not consent to receiving BHI services. \n 'Not consented action taken by: $consenderName'";
        }

        return Note::create([
            'patient_id'   => $patientId,
            'author_id'    => PatientSupportUser::id(), //This user is CLH patient support
            'body'         => $body,
            'type'         => $type,
            'performed_at' => Carbon::now()->toDateTimeString(),
        ]);
    }

    /**
     * @param $patientId
     *
     * @return mixed|null
     */
    private function getNextCallDate($patientId)
    {
        $nextCall = SchedulerService::getNextScheduledCall($patientId, true);

        return null !== $nextCall ? $nextCall->scheduled_date : null;
    }

    /**
     * When the User clicks `Not Now` we want to hide the banner till next scheduled call.
     * If next Scheduled call is null will show banner again tomorrow.
     *
     * @param $nextScheduledCallDate
     *
     * @return int
     */
    private function remainingTimeToShowBhiBannerAgain($nextScheduledCallDate)
    {
        $now = Carbon::now();
        //tomorrow at 7 am
        $tomorrow = $now->copy()->addDay()->startOfDay()->addHours(7);

        return null !== $nextScheduledCallDate
            ? Carbon::parse($nextScheduledCallDate)->setTime(11, 30)->diffInMinutes($now)
            : $tomorrow->diffInMinutes($now);
    }

    /**
     * @param $patientId
     */
    private function storeNotNowResponse($patientId)
    {
        $key                   = auth()->user()->getLegacyBhiNursePatientCacheKey($patientId);
        $nextScheduledCallDate = $this->getNextCallDate($patientId);

        $remainingTimeToShowBhiBannerAgainInMinutes = $this->remainingTimeToShowBhiBannerAgain($nextScheduledCallDate);

        Cache::put($key, $remainingTimeToShowBhiBannerAgainInMinutes, $remainingTimeToShowBhiBannerAgainInMinutes);
    }
}
