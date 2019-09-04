<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Exports\PatientProblemsReport;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\NurseInvoices\Traits\DryRunnable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\URL;
use Symfony\Component\Console\Input\InputArgument;

class CreatePatientProblemsReportForPractice extends Command
{
    use DryRunnable;

    /**
     * Company policy for one time reports to expire in two days.
     */
    const EXPIRES_IN_DAYS = 2;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a list of all patients with all problems for a user. Returns signed link that expires in 2 days.';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'reports:all-patient-with-problems';

    public function getArguments()
    {
        return [
            ['practice_id', InputArgument::REQUIRED, 'The practice ID.'],
            ['user_id', InputArgument::REQUIRED, 'The user ID who will have access to download this report.'],
        ];
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $practice = Practice::findOrFail($this->argument('practice_id'));

        $user = User::ofPractice($practice)->where('id', $this->argument('user_id'))->firstOrFail();

        $path   = storage_path(uniqid().'.csv');
        $stored = (new PatientProblemsReport())->forPractice($practice)->store($path);

        if ( ! $stored) {
            throw new \Exception('Could not store report to disk.');
        }

        $mediaCollectionName = 'patients_with_problems_reports';
        $media               = $practice->addMedia($path)->toMediaCollection($mediaCollectionName);

        return URL::temporarySignedRoute('download.media.from.signed.url', now()->addDays(self::EXPIRES_IN_DAYS), [
            'media_id' => $media->id,
            'user_id'  => $user->id,
        ]);
    }
}
