<?php

namespace App\Jobs;

use App\Contracts\Reports\PracticeDataExportInterface;
use App\Notifications\SendSignedUrlToDownloadPracticeReport;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Spatie\MediaLibrary\Helpers\RemoteFile;
use Spatie\MediaLibrary\Models\Media;

class StoreReportAsMedia implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var \Illuminate\Filesystem\FilesystemAdapter
     */
    protected $filesystemName;
    /**
     * @var string
     */
    protected $filename;
    /**
     * @var int
     */
    protected $practiceId;
    /**
     * @var string
     */
    protected $mediaCollectionName;
    /**
     * @var int
     */
    protected $userId;
    
    /**
     * Create a new job instance.
     *
     * @param string $filename
     * @param string $filesystemName
     * @param int $practiceId
     * @param string $mediaCollectionName
     * @param int $userId
     */
    public function __construct(
        string $filename,
        string $filesystemName,
        int $practiceId,
        string $mediaCollectionName,
        int $userId
    ) {
        $this->filesystemName      = $filesystemName;
        $this->filename            = $filename;
        $this->practiceId          = $practiceId;
        $this->mediaCollectionName = $mediaCollectionName;
        $this->userId              = $userId;
    }
    
    /**
     * Execute the job.
     *
     * @return Media|void
     */
    public function handle()
    {
        try {
            $this->notifyUser($this->createMedia());
        } catch (Exception $e) {
            Log::alert($e->getMessage().' '.$e->getFile().':'.$e->getLine());
        }
    }
    
    /**
     * @return Media
     */
    private function createMedia(): Media
    {
        return Practice::findOrFail($this->practiceId)->addMedia(new RemoteFile($this->filename, $this->filesystemName))->toMediaCollection(
            $this->mediaCollectionName
        );
    }
    
    private function fullPath()
    {
        return Storage::disk($this->filesystemName)->path($this->filename);
    }
    
    /**
     * @param Media $media
     *
     * @return mixed
     */
    public function notifyUser(Media $media)
    {
        $user = User::findOrFail($this->userId);
        
        $signedLink = URL::temporarySignedRoute(
            'download.media.from.signed.url',
            now()->addDays(PracticeDataExportInterface::EXPIRES_IN_DAYS),
            [
                'media_id'    => $media->id,
                'user_id'     => $user->id,
                'practice_id' => $this->practiceId,
            ]
        );
        
        $user->notify(
            new SendSignedUrlToDownloadPracticeReport(get_called_class(), $signedLink, $this->practiceId, $media->id)
        );
    }
}
