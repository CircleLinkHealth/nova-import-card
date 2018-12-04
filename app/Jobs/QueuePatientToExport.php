<?php

namespace App\Jobs;

use App\Note;
use App\Services\GoogleDrive;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

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

    private $driveContents;

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
        \Log::debug("Begin Export {$this->folderName()}");

        $googleDriveDir = $this->firstOrCreatePatientDirectory($drive);

        //Get drive folder contents recursively once at the beginning of the job to conserve API calls
        $this->driveContents = $drive->getContents($googleDriveDir['path'], true);

        $this->firstOrCreateAndStreamCarePlanPdf($drive, $googleDriveDir);

        $notesDir = $this->firstOrCreateNotesDirectory($drive, $googleDriveDir);

        $this->patient->notes->each(function ($note) use ($drive, $notesDir) {
            $this->firstOrCreateAndStreamNotePdf($drive, $notesDir, $note);
        });

        \Log::debug("Finish Export {$this->folderName()}");
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

    private function firstOrCreateAndStreamNotePdf(GoogleDrive $drive, array $googleDriveDir, Note $note)
    {
        $noteFileName = $this->getNoteFileName($note);

        $file = $this->fileExists($noteFileName);

        if ($file) {
            \Log::debug("PDF Note exists in `{$googleDriveDir['path']}/$noteFileName`.");

            return;
            //Delete the file
//            $deleted = $drive->getFilesystemHandle()->delete($file['path']);
        }

        $pdfPath = $note->toPdf();

        if (! $pdfPath) {
            throw new \Exception("`$pdfPath` not created");
        }


        $put = $drive->getFilesystemHandle()
                     ->putStream("{$googleDriveDir['path']}/$noteFileName", fopen($pdfPath, 'r+'));

        if (! $put) {
            throw new \Exception("Failed uploading PDF Note to `{$googleDriveDir['path']}`. ");
        }

        $pathStartingAtStorage = substr($pdfPath, stripos($pdfPath, "storage/") + 8);

        $deleted = $this->getLocalFilesystemHandle()
                        ->delete($pathStartingAtStorage);
    }

    /**
     * Create a PDF of the CarePlan and stream it to Google Drive
     *
     * @param GoogleDrive $drive
     *
     * @param array $googleDriveDir
     *
     * @return void
     * @throws \Exception
     */
    private function firstOrCreateAndStreamCarePlanPdf(GoogleDrive $drive, array $googleDriveDir)
    {
        \Log::debug("Begin Export PDF CarePlan for {$this->fullCloudPath()}.");

        $file = $this->fileExists("CarePlan - {$this->folderName()}.pdf");

        if ($file) {
            \Log::debug("PDF CarePlan exists in {$this->fullCloudPath()}.");

            return;
            //Delete the file
//            $deleted = $drive->getFilesystemHandle()->delete($file['path']);
        }

        $pdfPath = $this->patient->carePlan->toPdf();

        \Log::debug("PDF CarePlan path for `{$this->fullCloudPath()}` is `$pdfPath`.");

        if (! $pdfPath) {
            \Log::debug("PDF CarePlan not created to upload to `{$this->fullCloudPath()}`.");

            throw new \Exception("`$pdfPath` not created");
        }


        $put = $drive->getFilesystemHandle()
                     ->putStream($this->pdfCarePlanPath($googleDriveDir), fopen($pdfPath, 'r+'));

        if (! $put) {
            \Log::debug("PDF CarePlan not uploaded to `{$this->fullCloudPath()}` from `$pdfPath`.");

            throw new \Exception("Failed uploading PDF CarePlan to `{$this->pdfCarePlanPath($googleDriveDir)}`. ");
        }

        $pathStartingAtStorage = substr($pdfPath, stripos($pdfPath, "storage/") + 8);

        $deleted = $this->getLocalFilesystemHandle()
                        ->delete($pathStartingAtStorage);

        if (! $deleted) {
            \Log::debug("PDF CarePlan not deleted from `$pathStartingAtStorage`.");

            throw new \Exception("Failed uploading PDF CarePlan to `{$this->pdfCarePlanPath($googleDriveDir)}`. ");
        }
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
        return "{$googleDriveDir['path']}/CarePlan - {$this->folderName()}.pdf";
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
        $directory = $drive->getDirectory($this->folderId, $this->folderName());

        if (! $directory) {
            $drive->getFilesystemHandle()->makeDirectory($this->fullCloudPath());
            $directory = $drive->getDirectory($this->folderId, $this->folderName());
        }

        return $directory;
    }

    /**
     * Get the patient's note directory. If it doesn't exist, create it first.
     *
     * @param GoogleDrive $drive
     * @param $googleDriveDir
     *
     * @return mixed
     */
    private function firstOrCreateNotesDirectory(GoogleDrive $drive, $googleDriveDir)
    {
        $directory = $this->fileExists('Notes', 'dir');

        if (! $directory) {
            $drive->getFilesystemHandle()->makeDirectory("{$googleDriveDir['path']}/Notes");
            $directory = $drive->getDirectory($googleDriveDir['path'], 'Notes');
        }

        return $directory;
    }

    /**
     * Get the filename for a pdf note
     *
     * @param Note $note
     *
     * @return string
     */
    private function getNoteFileName(Note $note)
    {
        return "{$note->performed_at->toDateTimeString()} - {$note->type} - ID: $note->id";
    }

    public function fileExists($fileNameWithExtension, $type = 'file')
    {
        if (! is_a($this->driveContents, Collection::class)) {
            return false;
        }

        return $this->driveContents
            ->where('type', '=', $type)
            ->where('name', '=', $fileNameWithExtension)
            ->first();
    }
}
