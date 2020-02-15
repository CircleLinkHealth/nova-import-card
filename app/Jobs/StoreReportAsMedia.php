<?php

namespace App\Jobs;

use CircleLinkHealth\Customer\Entities\Practice;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Spatie\MediaLibrary\Models\Media;

class StoreReportAsMedia implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var \Illuminate\Filesystem\FilesystemAdapter
     */
    protected $storage;
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
     * @param \Illuminate\Filesystem\FilesystemAdapter $storage
     * @param string $filename
     * @param int $practiceId
     * @param string $mediaCollectionName
     */
    public function __construct(\Illuminate\Filesystem\FilesystemAdapter $storage, string $filename, int $practiceId, string $mediaCollectionName)
    {
        $this->storage = $storage;
        $this->filename = $filename;
        $this->practiceId = $practiceId;
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
        return $this->storage->path($this->filename);
    }
}
