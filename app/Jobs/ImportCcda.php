<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Notifications\CcdaImportedNotification;
use App\User;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportCcda implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 120;
    /**
     * @var bool
     */
    protected $notifyUploaderUser;

    /**
     * @var \CircleLinkHealth\SharedModels\Entities\Ccda
     */
    private $ccda;

    /**
     * Create a new job instance.
     */
    public function __construct(Ccda $ccda, bool $notifyUploaderUser = false)
    {
        $this->ccda               = $ccda;
        $this->notifyUploaderUser = $notifyUploaderUser;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        if ($this->ccda->import()) {
            $this->sendCcdaUploadedNotification();
        }
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array
     */
    public function tags()
    {
        return ['import', 'ccda:'.$this->ccda->id];
    }

    private function sendCcdaUploadedNotification()
    {
        if ( ! $this->notifyUploaderUser) {
            return;
        }
        User::findOrFail($this->ccda->user_id)->notify(new CcdaImportedNotification($this->ccda));
    }
}
