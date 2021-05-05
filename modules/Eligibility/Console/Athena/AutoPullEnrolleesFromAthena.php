<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Console\Athena;

use Aws\S3\S3Client;
use Aws\Sdk;
use AwsExtended\Config;
use AwsExtended\ConfigInterface;
use AwsExtended\S3Pointer;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Eligibility\Jobs\Athena\ProcessTargetPatientsForEligibilityInBatches;
use CircleLinkHealth\Eligibility\Jobs\ChangeBatchStatus;
use CircleLinkHealth\Eligibility\ProcessEligibilityService;
use CircleLinkHealth\Eligibility\Services\AthenaAPI\Actions\DetermineEnrollmentEligibility;
use CircleLinkHealth\SharedModels\Entities\EligibilityBatch;
use Ramsey\Uuid\Uuid;

class AutoPullEnrolleesFromAthena extends \Illuminate\Console\Command
{
    const MAX_DAYS_TO_PULL_AT_ONCE = 1;
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull eligible patients from Athena API.';
    protected $options;
    protected $service;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'athena:autoPullEnrolleesFromAthena {athenaPracticeId? : The Athena EHR practice id. `external_id` on table `practices`}
                                                                        {from? : From date yyyy-mm-dd}
                                                                        {to? : To date yyyy-mm-dd}
                                                                        {offset? : Offset results from athena api using number of target patients in the table}
                                                                        {batchId? : The Eligibility Batch Id}';

    /**
     * Create a new command instance.
     */
    public function __construct(ProcessEligibilityService $service)
    {
        parent::__construct();

        $this->service = $service;

        $this->options = [
            'filterProblems'      => true,
            'filterInsurance'     => false,
            'filterLastEncounter' => false,
        ];
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->argument('athenaPracticeId')) {
            $practices = Practice::whereHas('ehr', function ($ehr) {
                $ehr->where('name', 'Athena');
            })
                ->where('external_id', $this->argument('athenaPracticeId'))
                ->get();
        } else {
            $practices = Practice::whereHas('ehr', function ($ehr) {
                $ehr->where('name', 'Athena');
            })
                ->whereHas('settings', function ($settings) {
                    $settings->where('api_auto_pull', 1);
                })
                ->get();
        }

        if (0 == $practices->count()) {
            if (isProductionEnv()) {
                sendSlackMessage(
                    '#parse_enroll_import',
                    "No Practices with checked 'api-auto-pull' setting were found for the weekly Athena Data Pull."
                );
            } else {
                return null;
            }
        }

        $client = new CustomSqsClient(new Config(
            array_merge([
                'version' => 'latest',
                'http'    => [
                    'timeout'         => 60,
                    'connect_timeout' => 60,
                ],
            ], config('queue.connections.sqs')),
            'media',
            'https://sqs.us-east-1.amazonaws.com/670139022924/superadmin-low-production',
            Config::IF_NEEDED
        ));

        foreach ($practices as $practice) {
            $message = $client->sendMessage(json_encode($this->orchestrateEligibilityPull($practice)));
//            Bus::chain($this->orchestrateEligibilityPull($practice))
//                ->onQueue(getCpmQueueName(CpmConstants::LOW_QUEUE))
//                ->dispatch();
        }
    }

    /**
     * @throws \Illuminate\Auth\AuthenticationException
     * @return array                                    Array of Job objects
     */
    private function getAppointmentsJobs(Carbon $startDate, Carbon $endDate, int $athenaPracticeId, bool $offset, int $batchId): array
    {
        $service = app(DetermineEnrollmentEligibility::class);
        if ($startDate->diffInDays($endDate) > self::MAX_DAYS_TO_PULL_AT_ONCE) {
            $jobs        = [];
            $currentDate = $startDate->copy();
            do {
                $chunkStartDate = $currentDate->copy();
                $chunkEndDate   = $chunkStartDate->copy()->addDays(self::MAX_DAYS_TO_PULL_AT_ONCE);

                if ($chunkEndDate->isAfter($endDate)) {
                    $chunkEndDate = $endDate;
                }

                $jobs = array_merge($jobs, $service->getPatientIdFromAppointments($athenaPracticeId, $chunkStartDate, $chunkEndDate, $offset, $batchId));

                $currentDate = $chunkEndDate->copy()->addDay();
            } while ($currentDate->lt($endDate));

            return $jobs;
        }

        return $service->getPatientIdFromAppointments($athenaPracticeId, $startDate, $endDate, $offset, $batchId);
    }

    private function orchestrateEligibilityPull($practice)
    {
        $to   = Carbon::now()->format('Y-m-d');
        $from = Carbon::now()->subMonth()->format('Y-m-d');

        $offset = false;

        if ($this->argument('offset')) {
            $offset = $this->argument('offset');
        }

        if ($this->argument('from')) {
            $from = Carbon::createFromFormat('Y-m-d', $this->argument('from'));
        }

        if ($this->argument('to')) {
            $to = Carbon::createFromFormat('Y-m-d', $this->argument('to'));
        }

        $batch = null;

        if ($this->argument('batchId')) {
            $batch = EligibilityBatch::find($this->argument('batchId'));
        }

        if ( ! $batch) {
            $batch = $this->service->createBatch(EligibilityBatch::ATHENA_API, $practice->id, $this->options);
        }

        return array_merge(
            $this->getAppointmentsJobs(
                $from,
                $to,
                $practice->external_id,
                $offset,
                $batch->id,
            ),
            [new ChangeBatchStatus($batch->id, $practice->id, EligibilityBatch::STATUSES['not_started'])],
            (new ProcessTargetPatientsForEligibilityInBatches($batch->id))
                ->splitToBatches(),
            [new ChangeBatchStatus($batch->id, $practice->id, EligibilityBatch::STATUSES['complete'])],
        );
    }
}

/**
 * Class SqsClient.
 */
class CustomSqsClient implements SqsClientInterface
{
    /**
     * The client factory.
     *
     * @var \Aws\Sdk
     */
    protected $clientFactory;

    /**
     * The configuration object containing all the options.
     *
     * @var \AwsExtended\ConfigInterface
     */
    protected $config;

    /**
     * The S3 client to interact with AWS.
     *
     * @var \Aws\S3\S3Client
     */
    protected $s3Client;

    /**
     * The AWS client to push messages to SQS.
     *
     * @var \Aws\Sqs\SqsClient
     */
    protected $sqsClient;

    /**
     * SqsClient constructor.
     *
     * @param configInterface $configuration
     *                                       The configuration object
     *
     * @throws \InvalidArgumentException if any required options are missing or
     *                                   the service is not supported
     */
    public function __construct(ConfigInterface $configuration)
    {
        $this->config = $configuration;
    }

    /**
     * Routes all unknown calls to the sqsClient.
     *
     * @param $name
     *   The name of the method to call
     * @param $arguments
     *   The arguments to use
     *
     * @return mixed
     *               The return of the call
     */
    public function __call($name, $arguments)
    {
        // Send any unknown method calls to the SQS client.
        return call_user_func_array([$this->getSqsClient(), $name], $arguments);
    }

    /**
     * {@inheritdoc}
     */
    public function getS3Client()
    {
        if ( ! $this->s3Client) {
            $this->s3Client = $client = new S3Client(
                [
                    'version'     => 'latest',
                    'credentials' => [
                        'key'    => config('filesystems.disks.media.key'),
                        'secret' => config('filesystems.disks.media.secret'),
                    ],
                    'region' => config('filesystems.disks.media.region'),
                    'bucket' => config('filesystems.disks.media.bucket'),
                ]
            );
        }

        return $this->s3Client;
    }

    /**
     * {@inheritdoc}
     */
    public function getSqsClient()
    {
        if ( ! $this->sqsClient) {
            $this->sqsClient = $this->getClientFactory()->createSqs();
        }

        return $this->sqsClient;
    }

    /**
     * {@inheritdoc}
     */
    public function isTooBig($message, $max_size = null)
    {
        // The number of bytes as the number of characters. Notice that we are not
        // using mb_strlen() on purpose.
        $max_size = $max_size
            ?: static::MAX_SQS_SIZE_KB;

        return strlen($message) > $max_size * 1024;
    }

    /**
     * {@inheritdoc}
     */
    public function receiveMessage($queue_url = null)
    {
        $queue_url = $queue_url
            ?: $this->config->getSqsUrl();
        // Get the message from the SQS queue.
        $result = $this->getSqsClient()->receiveMessage(
            [
                'QueueUrl' => $queue_url,
            ]
        );
        // Detect if this is an S3 pointer message.
        if (S3Pointer::isS3Pointer($result)) {
            $args = $result->get(1);

            // Get the S3 document with the message and return it.
            return $this->getS3Client()->getObject(
                [
                    'Bucket' => $args['s3BucketName'],
                    'Key'    => $args['s3Key'],
                ]
            );
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function sendMessage($message, $queue_url = null)
    {
        switch ($this->config->getSendToS3()) {
            case ConfigInterface::ALWAYS:
                $use_sqs = false;
                break;
            case ConfigInterface::NEVER:
                $use_sqs = true;
                break;
            case ConfigInterface::IF_NEEDED:
                $use_sqs = ! $this->isTooBig($message);
                break;
            default:
                $use_sqs = true;
                break;
        }
        $use_sqs = $use_sqs || ! $this->config->getBucketName();
        if ( ! $use_sqs) {
            // First send the object to S3. The modify the message to store an S3
            // pointer to the message contents.
            $key     = $this->generateUuid().'.json';
            $receipt = $this->getS3Client()->upload(
                $this->config->getBucketName(),
                $key,
                $message
            );
            // Swap the message for a pointer to the actual message in S3.
            $message = (string) (new S3Pointer($this->config->getBucketName(), $key, $receipt));
        }
        $queue_url = $queue_url
            ?: $this->config->getSqsUrl();

        return $this->getSqsClient()->sendMessage(
            [
                'QueueUrl'    => $queue_url,
                'MessageBody' => $message,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * Generate a UUID v4.
     *
     * @return string
     *                The uuid
     */
    protected function generateUuid()
    {
        return Uuid::uuid4()->toString();
    }

    /**
     * Initialize and return the SDK client factory.
     *
     * @return \Aws\Sdk
     *                  The client factory
     */
    protected function getClientFactory()
    {
        if ($this->clientFactory) {
            return $this->clientFactory;
        }
        $this->clientFactory = new Sdk($this->config->getConfig());

        return $this->clientFactory;
    }
}
