<?php

namespace App\Jobs;

use CircleLinkHealth\Customer\Entities\Practice;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;
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
     * Create a new job instance.
     *
     * @param string $filename
     * @param string $filesystemName
     * @param int $practiceId
     * @param string $mediaCollectionName
     */
    public function __construct(string $filename, string $filesystemName, int $practiceId, string $mediaCollectionName)
    {
        $this->filesystemName      = $filesystemName;
        $this->filename            = $filename;
        $this->practiceId          = $practiceId;
        $this->mediaCollectionName = $mediaCollectionName;
    }
    
    /**
     * Execute the job.
     *
     * @return Media|void
     */
    public function handle()
    {
        $this->createMedia();
    }
    
    /**
     * @return Media
     */
    private function createMedia()
    {
        return Practice::findOrFail($this->practiceId)->addMedia($this->fullPath())->toMediaCollection($this->mediaCollectionName);
    }
    
    private function fullPath()
    {
        return Storage::disk($this->filesystemName)->path($this->filename);
    }
}
