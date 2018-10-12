<?php

namespace App\Jobs;

use App\Services\GoogleDrive;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class QueuePatientToExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var User
     */
    private $patient;

    /**
     * @var string
     */
    private $folderId;

    /**
     * Create a new job instance.
     *
     * @param User $user
     * @param $folderId
     */
    public function __construct(User $user, $folderId)
    {
        //
        $this->patient  = $user;
        $this->folderId = $folderId;
    }

    /**
     * Execute the job.
     *
     * @param GoogleDrive $drive
     *
     * @return void
     */
    public function handle(GoogleDrive $drive)
    {
        $pdfPath = $this->patient->carePlan->toPdf();

        if ( ! $pdfPath) {
            Log::critical("`$pdfPath` not created" . __FILE__);

            return;
        }

        if ( ! $drive->directoryExists($this->folderId, $this->folderName())) {
            $drive->getFilesystemHandle()->makeDirectory($this->fullCloudPath());
        }

        $googleDriveDir = $drive->getDirectory($this->folderId, $this->folderName());

        $put = $drive->getFilesystemHandle()->put("{$googleDriveDir['path']}/{$this->folderName()}",
            fopen($pdfPath, 'r+'));

        if ( ! $put) {
            Log::debug('error');
        }
    }

    private function folderName()
    {
        return "{$this->patient->first_name} {$this->patient->last_name} CLH ID:{$this->patient->id}";
    }

    private function fullCloudPath()
    {
        return "{$this->folderId}/{$this->folderName()}";
    }
}
