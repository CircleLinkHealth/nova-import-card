<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\DirectMailMessage;
use App\Services\PhiMail\Events\DirectMailMessageReceived;
use App\Services\PhiMail\Incoming\Handlers\Pdf;
use App\Services\PhiMail\Incoming\Handlers\XML;
use CircleLinkHealth\Customer\Entities\Media;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class UPG0506Demo extends Command
{
    const TEST_FIRST_NAME = 'Barbara';

    const TEST_LAST_NAME = 'Zznigro';

    /**
     * These are the test data found in test ccd and pdf located in storage/files-for-demos/upg0506.
     */
    const TEST_MRN = '334417';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Helper commands for UPG0506 demo';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:upg0506 {providerDm?} {--pdf} {--ccd} {--delete}';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->option('pdf')) {
            $pdfDm = $this->createNewDemoDirectMessage();
            $pdf   = new Pdf($pdfDm, file_get_contents(storage_path('files-for-demos/upg0506/upg0506-care-plan.pdf')));
            $pdf->handle();
            event(new DirectMailMessageReceived($pdfDm));

            $this->info('demo pdf sent');
        }

        if ($this->option('ccd')) {
            $ccdDm = $this->createNewDemoDirectMessage();
            $ccd   = new XML($ccdDm, file_get_contents(storage_path('files-for-demos/upg0506/upg0506-ccda.xml')));
            $ccd->handle();
            event(new DirectMailMessageReceived($ccdDm));

            $this->info('demo ccd sent');
        }

        if ($this->option('delete')) {
            $this->clearTestData();

            $this->info('test data deleted');
        }
    }

    private function clearCcdaData($ccda)
    {
        if ($ccda) {
            $ccda->media()
                ->get()
                ->each(function ($media) {
                    $media->delete();
                });

            \DB::table('media')
                ->where('custom_properties->is_pdf', 'true')
                ->where('custom_properties->is_upg0506', 'true')
                ->where('custom_properties->care_plan->demographics->name->family', self::TEST_LAST_NAME)
                ->delete();

            $dm = $ccda->directMessage()->first();

            if ($dm) {
                $dm->media()
                    ->get()
                    ->each(function ($media) {
                        $media->delete();
                    });
                $dm->delete();
            }

            $ccda->importedMedicalRecord()->forceDelete();

            $patient = $ccda->getPatient();

            if ($patient) {
                $patient->patientSummaries()->delete();
                $patient->forceDelete();
            }

            $ccda->forceDelete();
        }
    }

    private function clearTestData()
    {
        if ( ! isProductionEnv()) {
            try {
                $ccdas = Ccda::where('mrn', self::TEST_MRN)
                    ->get();

                foreach ($ccdas as $ccda) {
                    $this->clearCcdaData($ccda);
                }

                $pdf = Media::where('custom_properties->is_upg0506', 'true')
                    ->where('custom_properties->care_plan->demographics->mrn_number', self::TEST_MRN)
                    ->first();

                if ($pdf) {
                    $pdf->delete();
                }

                User::whereFirstName(self::TEST_FIRST_NAME)
                    ->whereLastName(self::TEST_LAST_NAME)
                    ->get()
                    ->each(function (User $u) {
                        $u->ccdas()->get()->each(function ($ccda) {
                            $this->clearCcdaData($ccda);
                        });

                        $u->carePlanAssessment()->delete();
                        $u->patientSummaries()->delete();
                        $u->forceDelete();
                    });
            } catch (\Exception $exception) {
                \Log::info('UPG0506 demo error on deleting test data', [
                    'exception' => $exception->getMessage(),
                ]);
            }
        }
    }

    /**
     * Creates a new Direct Message.
     *
     * @param CheckResult $message
     *
     * @return DirectMailMessage
     */
    private function createNewDemoDirectMessage()
    {
        return DirectMailMessage::create(
            [
                'message_id'      => Str::uuid(),
                'from'            => $this->argument('providerDm') ?: 'drraph@upg.ssdirect.aprima.com',
                'to'              => config('services.emr-direct.user'),
                'body'            => 'This is a demo message.',
                'num_attachments' => collect([$this->option('ccd'), $this->option('pdf')])->filter()->count(),
            ]
        );
    }
}
