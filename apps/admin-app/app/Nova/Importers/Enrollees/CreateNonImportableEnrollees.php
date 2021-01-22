<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Importers\Enrollees;

use App\Nova\Helpers\Utils;
use Carbon\Carbon;
use CircleLinkHealth\Core\StringManipulation;
use CircleLinkHealth\Customer\Entities\CarePerson;
use CircleLinkHealth\Eligibility\CcdaImporter\CcdaImporterWrapper;
use CircleLinkHealth\Eligibility\SelfEnrollment\Jobs\CreateSurveyOnlyUserFromEnrollee;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Support\Facades\Validator;

class CreateNonImportableEnrollees extends EnrolleeImportingAction
{
    protected int $chunkSize = 50;

    protected function fetchEnrollee(array $row): ?Enrollee
    {
        return Enrollee::with(['user'])
            ->firstOrCreate([
                'mrn'         => $row['mrn'],
                'practice_id' => $this->practiceId,
            ]);
    }

    protected function getActionInput(Enrollee $enrollee, array $row): array
    {
        $row['dob']         = Utils::parseExcelDate($row['dob'] ?? null);
        $row['provider_id'] = $this->getProviderId($row);
        $row['practice_id'] = $this->practiceId;

        return $row;
    }

    protected function performAction(Enrollee $enrollee, array $actionInput): void
    {
        $enrollee->first_name  = ucfirst(strtolower($actionInput['first_name']));
        $enrollee->last_name   = ucfirst(strtolower($actionInput['last_name']));
        $enrollee->provider_id = $actionInput['provider_id'];
        $enrollee->address     = ucwords(strtolower($actionInput['address']));
        $enrollee->address_2   = ucwords(strtolower($actionInput['address_2']));
        $enrollee->home_phone  = (new StringManipulation())->formatPhoneNumberE164($actionInput['phone_number']);
        $enrollee->dob         = optional($actionInput['dob'])->toDateString();
        $enrollee->city        = ucwords(strtolower($actionInput['city']));
        $enrollee->state       = $actionInput['state'];
        $enrollee->zip         = $actionInput['zip'];
        $enrollee->status      = Enrollee::TO_CALL;
        $enrollee->source      = Enrollee::UPLOADED_CSV;
        $enrollee->save();

        $user = $enrollee->user;

        if (is_null($user)) {
            CreateSurveyOnlyUserFromEnrollee::dispatch($enrollee);

            return;
        }

        if ( ! $user->isSurveyOnly()) {
            return;
        }

        CarePerson::updateOrCreate(
            [
                'type'    => CarePerson::BILLING_PROVIDER,
                'user_id' => $user->id,
            ],
            [
                'member_user_id' => $actionInput['provider_id'],
            ]
        );
    }

    protected function shouldPerformAction(Enrollee $enrollee, array $row): bool
    {
        return $row['dob'] instanceof Carbon && ! empty($row['provider_id']);
    }

    protected function validateRow(array $row): bool
    {
        return Validator::make($row, [
            'mrn'          => 'required',
            'first_name'   => 'required',
            'last_name'    => 'required',
            'dob'          => 'required',
            'phone_number' => 'required',
        ])->passes();
    }

    private function getProviderId(array $row): ?int
    {
        return optional(CcdaImporterWrapper::mysqlMatchProvider($row['provider'], $this->practiceId))->id;
    }
}
