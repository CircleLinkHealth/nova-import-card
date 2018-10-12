<?php

namespace App\Jobs;

use App\Services\GoogleDrive;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

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
     * @throws \Exception
     */
    public function handle(GoogleDrive $drive)
    {
        $this->firstOrCreateAndStreamCarePlanPdf($drive);
    }

    private function getLocalFilesystemHandle()
    {
        return \Storage::disk('storage');
    }

    private function folderName()
    {
        return "{$this->patient->first_name} {$this->patient->last_name} CLH ID:{$this->patient->id}";
    }

    private function fullCloudPath()
    {
        return "{$this->folderId}/{$this->folderName()}";
    }

    /**
     * Create a PDF of the CarePlan and stream it to Google Drive
     *
     * @param GoogleDrive $drive
     *
     * @return bool
     * @throws \Exception
     */
    private function firstOrCreateAndStreamCarePlanPdf(GoogleDrive $drive)
    {
        $googleDriveDir = $this->firstOrCreatePatientDirectory($drive);

        while ($file = $drive->fileExists($googleDriveDir['path'], 'CarePlan.pdf')) {
            $deleted = $drive->getFilesystemHandle()->delete($file['path']);
        }

        $pdfPath = $this->patient->carePlan->toPdf();

        if ( ! $pdfPath) {
            throw new \Exception("`$pdfPath` not created");
        }


        $put = $drive->getFilesystemHandle()
                     ->putStream($this->pdfCarePlanPath($googleDriveDir), fopen($pdfPath, 'r+'));

        if ( ! $put) {
            throw new \Exception("Failed uploading PDF CarePlan to `{$googleDriveDir}`. ");
        }

        $pathStartingAtStorage = substr($pdfPath, stripos($pdfPath, "storage/") + 8);

        $deleted = $this->getLocalFilesystemHandle()
                        ->delete($pathStartingAtStorage);

        return true;
    }

    /**
     * The full path to the Careplan.pdf on Google Drive
     *
     * @param array $googleDriveDir
     *
     * @return string
     */
    private function pdfCarePlanPath(array $googleDriveDir)
    {
        return "{$googleDriveDir['path']}/CarePlan.pdf";
    }

    /**
     * Get the directory with the patient's name. If it doesn't exist, create it.
     *
     * @param GoogleDrive $drive
     *
     * @return mixed
     */
    private function firstOrCreatePatientDirectory(GoogleDrive $drive)
    {
        if ( ! $drive->directoryExists($this->folderId, $this->folderName())) {
            $drive->getFilesystemHandle()->makeDirectory($this->fullCloudPath());
        }

        return $drive->getDirectory($this->folderId, $this->folderName());
    }
}
