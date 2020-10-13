<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Importers;

use App\Nova\Actions\ImportEnrollees;
use App\Search\ProviderByName;
use App\SelfEnrollment\Jobs\CreateSurveyOnlyUserFromEnrollee;
use Carbon\Carbon;
use CircleLinkHealth\Core\StringManipulation;
use CircleLinkHealth\Customer\Entities\CarePerson;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\ImportPatientInfo;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;
use Validator;

class Enrollees implements WithChunkReading, OnEachRow, WithHeadingRow, ShouldQueue, WithEvents
{
    use Importable;
    use RegistersEventListeners;

    protected $attributes;

    protected $modelClass;

    protected $resource;

    protected $rowNumber = 2;

    protected $rules;

    private $actionType;

    private $fileName;

    /**
     * @var int
     */
    private $practiceId;

    public function __construct(int $practiceId, $actionType, $fileName)
    {
        ini_set('upload_max_filesize', '200M');
        ini_set('post_max_size', '200M');
        ini_set('max_input_time', 900);
        ini_set('max_execution_time', 900);

        $this->practiceId = $practiceId;
        $this->actionType = $actionType;
        $this->fileName   = $fileName;
    }

    public function chunkSize(): int
    {
        return 100;
    }

    public function markAsIneligible(array $row)
    {
        $enrollee = Enrollee::where('mrn', $row['mrn'])
            ->where('practice_id', $this->practiceId)
            ->first();

        if (is_null($enrollee)) {
            Log::channel('database')->critical("Patient not found for CSV:{$this->fileName}, for row: {$this->rowNumber}.");

            return;
        }

        $enrollee->status = Enrollee::INELIGIBLE;
        $enrollee->save();
    }

    public function message(): string
    {
        return 'File queued for importing.';
    }

    public function onRow(Row $row)
    {
        $row = $row->toArray();

        if (ImportEnrollees::ACTION_MARK_INELIGIBLE == $this->actionType) {
            $this->markAsIneligible($row);
        }
        if (ImportEnrollees::ACTION_MARK_AUTO_ENROLLMENT == $this->actionType) {
            $this->markForAutoEnrollment($row);
        }

        if (ImportEnrollees::ACTION_CREATE_ENROLLEES == $this->actionType) {
            $this->updateOrCreateEnrolleeFromCsv($row);
        }
        ++$this->rowNumber;
    }

    public function rules(): array
    {
        return $this->rules;
    }

    /**
     * Subtracts 100 years off date if it's after 1/1/2000.
     *
     * @return Carbon
     */
    private function correctCenturyIfNeeded(Carbon &$date)
    {
        //If a DOB is after 2000 it's because at some point the date incorrectly assumed to be in the 2000's, when it was actually in the 1900's. For example, this date 10/05/04.
        $cutoffDate = Carbon::createFromDate(2000, 1, 1);

        if ($date->gte($cutoffDate)) {
            $date->subYears(100);
        }

        return $date;
    }

    private function markForAutoEnrollment(array $row)
    {
        //Currently not accomodating for cases where enrollee does not exist.
        $v = Validator::make($row, [
            'eligible_patient_id' => 'required',
            'mrn'                 => 'required',
            'first_name'          => 'required',
            'last_name'           => 'required',
        ]);

        if ($v->fails()) {
            Log::channel('database')->critical("Input Validation for CSV:{$this->fileName}, at row: {$this->rowNumber}, failed.");

            return;
        }

        //Currently not accomodating for cases where enrollee does not exist.
        $enrollee = Enrollee::with(['user', 'enrollmentInvitationLinks'])
            ->whereId($row['eligible_patient_id'])
            ->where('practice_id', $this->practiceId)
            ->where('mrn', $row['mrn'])
            ->where('first_name', $row['first_name'])
            ->where('last_name', $row['last_name'])
            ->first();

        if ( ! $enrollee) {
            Log::channel('database')->critical("Patient not found for CSV:{$this->fileName}, for row: {$this->rowNumber}.");

            return;
        }

        //if enrollee has already been marked or invited return.
        if (Enrollee::QUEUE_AUTO_ENROLLMENT === $enrollee->status || $enrollee->enrollmentInvitationLinks->isNotEmpty()) {
            Log::channel('database')->warning("Patient for CSV:{$this->fileName}, for row: {$this->rowNumber} has already been marked for auto-enrollment.");

            return;
        }

        //set for Auto Enrollment
        $enrollee->status = Enrollee::QUEUE_AUTO_ENROLLMENT;

        //unassign from any care_ambassador to prevent calling patients who have received invitations
        $enrollee->care_ambassador_user_id = null;

        //reset attempt count and requested callback to prevent the CA call queue from accidentally picking these up - edge case
        //this also means we reset their call statuses in general
        $enrollee->attempt_count      = 0;
        $enrollee->requested_callback = null;
        $enrollee->save();
    }

    private function updateOrCreateEnrolleeFromCsv(array $row)
    {
        if ($row['dob']) {
            if (is_numeric($row['dob'])) {
                $row['dob'] = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['dob']);
            }

            $date = $this->validateDob($row['dob']);

            $row['dob'] = $date;
        }

        if (empty($row['dob']) || false === $row['dob']) {
            Log::channel('database')->critical("Import for:{$this->fileName}, Invalid DOB for Enrollee at row: {$this->rowNumber}.");

            return;
        }

        $provider = ProviderByName::first($row['provider']);

        if ( ! $provider) {
            Log::channel('database')->critical("Import for:{$this->fileName}, Provider not found for Enrollee at row: {$this->rowNumber}.");

            return;
        }

        $row['provider_id'] = $provider->id;
        $row['practice_id'] = $this->practiceId;

        $enrollee = Enrollee::updateOrCreate(
            [
                'mrn'         => $row['mrn'],
                'practice_id' => $this->practiceId,
            ],
            [
                'first_name' => ucfirst(strtolower($row['first_name'])),
                'last_name'  => ucfirst(strtolower($row['last_name'])),

                'provider_id' => $provider->id,

                'address'   => ucwords(strtolower($row['address'])),
                'address_2' => ucwords(strtolower($row['address_2'])),
                //we only get one phone number, add as home_phone
                'home_phone' => (new StringManipulation())->formatPhoneNumberE164($row['phone_number']),
                'dob'        => optional(
                    ImportPatientInfo::parseDOBDate($row['dob'])
                )->toDateString(),
                'city'  => ucwords(strtolower($row['city'])),
                'state' => $row['state'],
                'zip'   => $row['zip'],

                'status' => Enrollee::TO_CALL,
                'source' => Enrollee::UPLOADED_CSV,
            ]
        );

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
                'member_user_id' => $provider->id,
            ]
        );
    }

    private function validateDob($dob)
    {
        if ( ! $dob) {
            return false;
        }

        $validator = \Validator::make(['dob' => $dob], ['dob' => 'required|filled|date']);

        if ($validator->fails()) {
            return false;
        }

        try {
            $date = Carbon::parse($dob);

            if ($date->isToday()) {
                return false;
            }

            return $this->correctCenturyIfNeeded($date);
        } catch (\InvalidArgumentException $e) {
            if (Str::contains($dob, '/')) {
                $delimiter = '/';
            } elseif (Str::contains($dob, '-')) {
                $delimiter = '-';
            }
            $date = explode($delimiter, $dob);

            if (count($date) < 3) {
                throw new \Exception("Invalid date $dob");
            }

            $year = $date[2];

            if (2 == strlen($year)) {
                //if date is two digits we are assuming it's from the 1900s
                $year = (int) $year + 1900;
            }

            return Carbon::createFromDate($year, $date[0], $date[1]);
        }
    }
}
