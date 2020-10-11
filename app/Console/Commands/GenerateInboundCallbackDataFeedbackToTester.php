<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\PostmarkInboundMail;
use App\Traits\Tests\PostmarkCallbackHelpers;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Traits\PracticeHelpers;
use CircleLinkHealth\Customer\Traits\UserHelpers;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Faker\Factory;
use Illuminate\Console\Command;

class GenerateInboundCallbackDataFeedbackToTester extends Command
{
    use PostmarkCallbackHelpers;
    use PracticeHelpers;
    use UserHelpers;

    const LIMIT = 2;
    const START = 1;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'If {--userType} is set it will create data given the userType and return them to console for tester to work with.
    If {--userType} {--save} is set it will create data given the userType and save them to postmark_inbound_mail.
    If {--runAll} is set it will create data for all userTypes and save them to postmark_inbound_mail';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:inboundCallbackData {--userType=} {--save} {--runAll}';
    private \CircleLinkHealth\Customer\Entities\User $careAmbassador;
    /**
     * @var User
     */
    private $patient;
    /**
     * @var Enrollee|\Illuminate\Database\Eloquent\Model
     */
    private $patientEnrollee;

    /**
     * @var \Illuminate\Database\Eloquent\Model|PostmarkInboundMail
     */
    private $postmarkRecord;
    private \CircleLinkHealth\Customer\Entities\Practice $practice;
    /**
     * @var array|bool|string|null
     */
    private $runAll;
    /**
     * @var array|bool|string|null
     */
    private $save;
    /**
     * @var array|string|null
     */
    private $userType;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->practice       = $this->nekatostrasPractice();
        $this->careAmbassador = $this->createUser($this->practice->id, 'care-ambassador');
    }

    /**
     * @return array
     */
    public function dataOfStatusType(string $patientType, bool $requestToWithdraw, bool $nameIsSelf)
    {
        $n           = self::START;
        $inboundData = collect();
        while ($n <= self::LIMIT) {
            $this->createPatientData($patientType);
            $this->save
                ? $this->createPostmarkCallbackData($requestToWithdraw, $nameIsSelf)
                : $this->generatePostmarkCallbackData($requestToWithdraw, $nameIsSelf);

            $inboundData->push($this->postmarkRecord);
            ++$n;
        }

        return $inboundData->toArray();
    }

    /**
     * @return array
     */
    public function dataOfTypeNotConsentedCaUnassigned(string $patientType, bool $requestToWithdraw, bool $nameIsSelf)
    {
        $n           = self::START;
        $inboundData = collect();
        while ($n <= self::LIMIT) {
            $this->createPatientData($patientType);
            $this->patientEnrollee->update(
                [
                    'care_ambassador_user_id' => null,
                ]
            );

            $this->save
                ? $this->createPostmarkCallbackData($requestToWithdraw, $nameIsSelf)
                : $this->generatePostmarkCallbackData($requestToWithdraw, $nameIsSelf);
            $inboundData->push($this->postmarkRecord);
            ++$n;
        }

        return $inboundData->toArray();
    }

    /**
     * @param  mixed $saveData
     * @return array
     */
    public function dataOfTypeSameNumber(string $patientType, bool $requestToWithdraw, bool $nameIsSelf)
    {
        $n           = self::START;
        $inboundData = collect();
        while ($n <= self::LIMIT) {
            $this->createPatientData($patientType);
            $this->save
                ? $this->createPostmarkCallbackData($requestToWithdraw, $nameIsSelf)
                : $this->generatePostmarkCallbackData($requestToWithdraw, $nameIsSelf);

            $this->patient->phoneNumbers
                ->first()
                ->update(
                    [
                        'number' => 1234567890,
                    ]
                );

            $inboundData->push($this->postmarkRecord);
            ++$n;
        }

        return $inboundData->toArray();
    }

    /**
     * @return array
     */
    public function dataOfTypeSamePhoneAndName(string $patientType, bool $requestToWithdraw, bool $nameIsSelf)
    {
        $n         = self::START;
        $faker     = Factory::create();
        $firstName = $faker->firstName;
        $lastName  = $faker->lastName;
        $phone     = $faker->phoneNumber;

        $inboundData = collect();
        while ($n <= self::LIMIT) {
            $this->createPatientData($patientType);
            $this->patient->phoneNumbers
                ->first()
                ->update(
                    [
                        'number' => $phone,
                    ]
                );

            $this->patient->update([
                'display_name' => $firstName.' '.$lastName,
                'first_name'   => $firstName,
                'last_name'    => $lastName,
            ]);

            $this->patient->fresh();
            $this->save
                ? $this->createPostmarkCallbackData($requestToWithdraw, $nameIsSelf)
                : $this->generatePostmarkCallbackData($requestToWithdraw, $nameIsSelf);

            if ($inboundData->isEmpty()) {
                $inboundData->push($this->postmarkRecord);
            }

            ++$n;
        }

        return $inboundData->toArray();
    }

    /**
     * @return array
     */
    public function dataOfTypeSelfEnrollableCaUnassigned(string $patientType, bool $requestToWithdraw, bool $nameIsSelf)
    {
        $n           = self::START;
        $inboundData = collect();
        while ($n <= self::LIMIT) {
            $this->createPatientData($patientType);
            $this->save
                ? $this->createPostmarkCallbackData($requestToWithdraw, $nameIsSelf)
                : $this->generatePostmarkCallbackData($requestToWithdraw, $nameIsSelf);
            $this->patientEnrollee->update(
                [
                    'care_ambassador_user_id' => null,
                ]
            );

            $inboundData->push($this->postmarkRecord);
            ++$n;
        }

        return $inboundData->toArray();
    }

    /**
     * @return array/**
     *
     */
    public function dataOfTypeUnmatchable(string $patientType, bool $requestToWithdraw, bool $nameIsSelf)
    {
        $n         = self::START;
        $faker     = Factory::create();
        $firstName = $faker->firstName;
        $lastName  = $faker->lastName;
        $phone     = $faker->phoneNumber;

        $inboundData = collect();
        while ($n <= self::LIMIT) {
            $this->createPatientData($patientType);
            $this->save
                ? $this->createPostmarkCallbackData($requestToWithdraw, $nameIsSelf)
                : $this->generatePostmarkCallbackData($requestToWithdraw, $nameIsSelf);
            $this->patient->phoneNumbers
                ->first()
                ->update(
                    [
                        'number' => $phone,
                    ]
                );

            $this->patient->update([
                'display_name' => $firstName.' '.$lastName,
                'first_name'   => $firstName,
                'last_name'    => $lastName,
            ]);

            $inboundData->push($this->postmarkRecord);
            ++$n;
        }

        return $inboundData->toArray();
    }

    public function handle()
    {
        $this->userType = $this->option('userType');
        $this->save     = $this->option('save');
        $this->runAll   = $this->option('runAll');

        if ( ! $this->userType && ! $this->runAll) {
            $this->warn('Please enter user type.');

            return;
        }

        if ($this->runAll) {
            $this->save               = true;
            $postmarkGeneratedDataIds = $this->runAllFunctions();

            $this->info('Data for all patient types migrated in:
            [postmark_inbound_mail, calls, and unresolved_postmark_callbacks]');

            if ($this->confirm('Do you wish to process the generated data?
            This will run ProcessPostmarkInboundMailJob foreach generated data.')) {
                \Artisan::call('process:postmark-inbound-mail', [
                    'recordsId' => $postmarkGeneratedDataIds->toArray(),
                ]);
            }

            return;
        }

        $inboundData = ['No callback data'];

        if ($this->isTrue('enrolled')) {
            $inboundData = $this->createUsersOfTypeEnrolled();
        }

        if ($this->isTrue('not_enrolled')) {
            $inboundData = $this->createUsersOfTypeConsentedButNotEnrolled();
        }

        if ($this->isTrue('queued_for_self_enrolment_but_ca_unassigned')) {
            $inboundData = $this->createUsersOfTypeQueuedForEnrolmentButNotCAssigned();
        }

        if ($this->isTrue('inbound_callback_name_is_self')) {
            $inboundData = $this->createUsersOfTypeNameIsSelf();
        }

        if ($this->isTrue('patient_requests_to_withdraw')) {
            $inboundData = $this->createUsersOfTypeRequestedToWithdraw();
        }

        if ($this->isTrue('patient_requests_to_withdraw_and_name_is_self')) {
            $inboundData = $this->createUsersOfTypeRequestedToWithdrawAndNameIsSelf();
        }

        if ($this->isTrue('phone_number_will_not_match_but_will_match_by_name')) {
            $inboundData = $this->multiMatchPhone();
        }

        if ($this->isTrue('not_consented_ca_assigned')) {
            $inboundData = $this->createUsersOfTypeNotConsentedAssignedToCa();
        }

        if ($this->isTrue('not_consented_ca_unassigned')) {
            $inboundData = $this->createUsersOfTypeNotConsentedUnassignedCa();
        }

        if ($this->isTrue('patients_have_same_number_same_phone')) {
            $inboundData = $this->multiMatchPatientsWithSameNumberAndName();
        }

        if ( ! $this->save) {
            $this->info(implode(", \n", $inboundData));
        }
    }

    /**
     * @return array/**
     *
     */
    private function createUsersOfTypeConsentedButNotEnrolled()
    {
        $limit       = self::LIMIT;
        $inboundData = $this->dataOfStatusType(Enrollee::CONSENTED, false, false);
        $this->info("Generated $limit patients of type:[CONSENTED BUT NOT ENROLLED].");

        return $inboundData;
    }

    /**
     * @return array
     */
    private function createUsersOfTypeEnrolled()
    {
        $limit       = self::LIMIT;
        $inboundData = $this->dataOfStatusType(Enrollee::ENROLLED, false, false);
        $this->info("Generated $limit patients of type:[ENROLLED].");

        return $inboundData;
    }

    /**
     * @return array
     */
    private function createUsersOfTypeNameIsSelf()
    {
        $limit       = self::LIMIT;
        $inboundData = $this->dataOfStatusType(Enrollee::ENROLLED, false, true);
        $this->info("Generated $limit patients of type:[Name Is SELF].");

        return $inboundData;
    }

    /**
     * @return array
     */
    private function createUsersOfTypeNotConsentedAssignedToCa()
    {
        $limit       = self::LIMIT;
        $inboundData = $this->dataOfStatusType(Enrollee::ELIGIBLE, false, false);
        $this->info("Generated $limit patients of type:[NOT CONSENTED BUT CA ASSIGNED.]");

        return $inboundData;
    }

    /**
     * @return array
     */
    private function createUsersOfTypeNotConsentedUnassignedCa()
    {
        $limit       = self::LIMIT;
        $inboundData = $this->dataOfTypeNotConsentedCaUnassigned(Enrollee::ELIGIBLE, false, false);
        $this->info("Generated $limit patients of type:[NOT CONSENTED AND CA NOT ASSIGNED.]");

        return $inboundData;
    }

    /**
     * @return array
     */
    private function createUsersOfTypeQueuedForEnrolmentButNotCAssigned()
    {
        $limit       = self::LIMIT;
        $inboundData = $this->dataOfTypeSelfEnrollableCaUnassigned(Enrollee::QUEUE_AUTO_ENROLLMENT, false, false);
        $this->info("Generated $limit patients of type:[Queued for self enrolment but not CA assigned].");

        return $inboundData;
    }

    /**
     * @return array
     */
    private function createUsersOfTypeRequestedToWithdraw()
    {
        $limit       = self::LIMIT;
        $inboundData = $this->dataOfStatusType(Enrollee::ENROLLED, true, false);
        $this->info("Generated $limit patients of type:[Requested To Withdraw].");

        return $inboundData;
    }

    /**
     * @return array
     */
    private function createUsersOfTypeRequestedToWithdrawAndNameIsSelf()
    {
        $limit       = self::LIMIT;
        $inboundData = $this->dataOfStatusType(Enrollee::ENROLLED, true, true);
        $this->info("Generated $limit patients of type [Requested To Withdraw And Name Is SELF]");

        return $inboundData;
    }

    /**
     * @return bool
     */
    private function isTrue(string $constType)
    {
        return $this->userType === $constType;
    }

    /**
     * @return array
     */
    private function multiMatchPatientsWithSameNumberAndName()
    {
        $customLimit = 1;
        $inboundData = $this->dataOfTypeSamePhoneAndName(Enrollee::ENROLLED, false, false);
        $this->info("Generated $customLimit patients of type:[Same name and number].");

        return $inboundData;
    }

    /**
     * @return array
     */
    private function multiMatchPhone()
    {
        $limit       = self::LIMIT;
        $inboundData = $this->dataOfTypeSameNumber(Enrollee::ENROLLED, false, false);
        $this->info("Generated $limit patient of type:[Patients with same number different name].");

        return $inboundData;
    }

    private function noMatch()
    {
        $limit       = self::LIMIT;
        $inboundData = $this->dataOfTypeUnmatchable(Enrollee::ENROLLED, false, false);
        $this->info("Generated $limit patient of type:[Patients that will not result in any match. Will post to #carecoach_ops_alerts].");

        return $inboundData;
    }

    /**
     * @return \Collection|\Illuminate\Support\Collection
     */
    private function runAllFunctions()
    {
        $generatedPostmarkIds = collect();
        $generatedPostmarkIds->push(...collect($this->createUsersOfTypeEnrolled())->pluck('id'));
        $generatedPostmarkIds->push(...collect($this->createUsersOfTypeConsentedButNotEnrolled())->pluck('id'));
        $generatedPostmarkIds->push(...collect($this->createUsersOfTypeQueuedForEnrolmentButNotCAssigned())->pluck('id'));
        $generatedPostmarkIds->push(...collect($this->createUsersOfTypeNameIsSelf())->pluck('id'));
        $generatedPostmarkIds->push(...collect($this->createUsersOfTypeRequestedToWithdraw())->pluck('id'));
        $generatedPostmarkIds->push(...collect($this->createUsersOfTypeRequestedToWithdrawAndNameIsSelf())->pluck('id'));
        $generatedPostmarkIds->push(...collect($this->multiMatchPhone())->pluck('id'));
        $generatedPostmarkIds->push(...collect($this->createUsersOfTypeNotConsentedAssignedToCa())->pluck('id'));
        $generatedPostmarkIds->push(...collect($this->createUsersOfTypeNotConsentedUnassignedCa())->pluck('id'));
        $generatedPostmarkIds->push(...collect($this->multiMatchPatientsWithSameNumberAndName())->pluck('id'));
        $generatedPostmarkIds->push(...collect($this->noMatch())->pluck('id'));

        return $generatedPostmarkIds;
    }
}
