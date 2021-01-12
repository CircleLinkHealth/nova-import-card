<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Console\Commands;

use CircleLinkHealth\Customer\Jobs\ProcessPostmarkInboundMailCommand;
use CircleLinkHealth\SharedModels\Services\Postmark\InboundCallbackDataForTesterService;
use Illuminate\Console\Command;

class GenerateInboundCallbackDataFeedbackToTester extends Command
{
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
     * @var array|bool|string|null
     */
    private $save;
    /**
     * @var \Illuminate\Contracts\Foundation\Application|InboundCallbackDataForTesterService|mixed
     */
    private $service;
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
    }

    public function handle()
    {
        $this->service  = app(InboundCallbackDataForTesterService::class);
        $this->userType = $this->option('userType');
        $this->save     = $this->option('save');
        $runAll         = $this->option('runAll');

        if ( ! $this->userType && ! $runAll) {
            $this->warn('Please enter user type.');

            return;
        }

        if ($runAll) {
            $this->save               = true;
            $postmarkGeneratedDataIds = $this->service->runAllFunctions();

            $this->info('Data for all patient types migrated in:
            [postmark_inbound_mail]');

            if ($this->confirm('Do you wish to process the generated data?
            This will run ProcessPostmarkInboundMailJob foreach generated data and populate [calls and unresolved_postmark_callbacks].')) {
                $this->call(ProcessPostmarkInboundMailCommand::class, [
                    'recordIds' => $postmarkGeneratedDataIds->toArray(),
                ]);
            }

            return;
        }

        $inboundData = ['No callback data'];

        if ($this->isTrue('enrolled')) {
            $limit       = self::LIMIT;
            $inboundData = $this->service->createUsersOfTypeEnrolled();
            $this->info("Generated $limit patients of type:[ENROLLED]. SHOULD ASSIGN CALLBACK TO CARE COACH");
        }

        if ($this->isTrue('not_enrolled')) {
            $limit       = self::LIMIT;
            $inboundData = $this->service->createUsersOfTypeConsentedButNotEnrolled($this->save);
            $this->info("Generated $limit patients of type:[CONSENTED BUT NOT ENROLLED]. AVAILABLE ON DASHBOARD");
        }

        if ($this->isTrue('queued_for_self_enrolment_but_ca_unassigned')) {
            $limit       = self::LIMIT;
            $inboundData = $this->service->createUsersOfTypeQueuedForEnrolmentButNotCAssigned($this->save);
            $this->info("Generated $limit patients of type:[Queued for self enrolment but not CA assigned]. AVAILABLE IN DASHBOARD");
        }

        if ($this->isTrue('inbound_callback_name_is_self')) {
            $limit       = self::LIMIT;
            $inboundData = $this->service->createUsersOfTypeNameIsSelf();
            $this->info("Generated $limit patients of type:[Name Is SELF]. SHOULD ASSIGN CALLBACK");
        }

        if ($this->isTrue('patient_requests_to_withdraw')) {
            $limit       = self::LIMIT;
            $inboundData = $this->service->createUsersOfTypeRequestedToWithdraw();
            $this->info("Generated $limit patients of type:[Requested To Withdraw]. AVAILABLE IN DASHBOARD");
        }

        if ($this->isTrue('patient_requests_to_withdraw_and_name_is_self')) {
            $limit       = self::LIMIT;
            $inboundData = $this->service->createUsersOfTypeRequestedToWithdrawAndNameIsSelf();
            $this->info("Generated $limit patients of type [Requested To Withdraw And Name Is SELF] AVAILABLE IN DASHBOARD");
        }

        if ($this->isTrue('not_consented_ca_assigned')) {
            $limit       = self::LIMIT;
            $inboundData = $this->service->createUsersOfTypeNotConsentedAssignedToCa();
            $this->info("Generated $limit patients of type:[NOT CONSENTED BUT CA ASSIGNED.] SHOULD ASSIGN CALLBACK TO CA");
        }

        if ($this->isTrue('not_consented_ca_unassigned')) {
            $limit       = self::LIMIT;
            $inboundData = $this->service->createUsersOfTypeNotConsentedUnassignedCa($this->save);
            $this->info("Generated $limit patients of type:[NOT CONSENTED AND CA NOT ASSIGNED.] AVAILABLE IN DASHBOARD");
        }

        if ($this->isTrue('patients_have_same_name_same_phone')) {
            $limit       = self::LIMIT;
            $inboundData = $this->service->multiMatchPatientsWithSameNumberAndName($this->save);
            $this->info("Generated $limit patients of type:[Same name and number]. AVAILABLE IN DASHBOARD");
        }

        if ($this->isTrue('no_matched_data')) {
            $limit       = self::LIMIT;
            $inboundData = $this->service->noMatch($this->save);
            $this->info("Generated $limit patient of type:[Patients that will not result in any match. Will post to #carecoach_ops_alerts].");
        }

        if ( ! $this->save) {
            $this->info(implode(", \n", $inboundData));
        }
    }

    /**
     * @return bool
     */
    private function isTrue(string $constType)
    {
        return $this->userType === $constType;
    }
}
