<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use CircleLinkHealth\Customer\Entities\SaasAccount;
use Illuminate\Console\Command;

class UploadSelfEnrolmentImagesToS3 extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upload signatures and logos to S3, for self enrolment feature.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upload:enrolment-files';

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
     * @return int
     */
    public function handle()
    {
        $data     = \File::allFiles('public/img/logos');
        $fileName = pathinfo($data[0])['filename'];
        $path     = storage_path($data[0]);
        $saved    = file_put_contents($path, $data[0]);

        if ( ! $saved) {
            $this->info('E what do to re parea');
        }

        $model = \Cache::remember("$fileName", 2, function () {
            return SaasAccount::whereSlug('circlelink-health')->firstOrFail();
        });

        $model->addMedia($path)
            ->toMediaCollection($fileName);

        $x = optional(
            SaasAccount::whereSlug('circlelink-health')
                ->first()
                ->getMedia("$fileName")
                ->sortByDesc('id')
                ->first()
        )->getFile();

        $r = 1;
    }
}
