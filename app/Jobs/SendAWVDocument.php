<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Notifications\SendCareDocument;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class SendAWVDocument implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private $channels;

    private $input;

    private $media;

    private $patient;

    /**
     * Create a new job instance.
     *
     * @param mixed      $media
     * @param mixed      $patient
     * @param mixed      $channels
     * @param mixed|null $input
     */
    public function __construct($media, $patient, $channels, $input = null)
    {
        $this->media    = $media;
        $this->patient  = $patient;
        $this->channels = $channels;
        $this->input    = $input;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        //if this is dispatched from Care Docs page with *one* channel and input set - input is validated
        if ($this->input) {
            $notifiable = $this->getNotifiableEntity($this->channels, $this->input);

            if ($notifiable) {
                $notifiable->notify(new SendCareDocument($this->media, $this->patient, $this->channels));
            } else {
//                $newUser = (new User())->forceFill([
//                    'name'  => 'Test User',
//                    'email' => 'email@example.com',
//                ]);
                Notification::route($this->channels[0], $this->input)
                    ->notify(new SendCareDocument($this->media, $this->patient));
            }
        }
    }

    private function getNotifiableEntity($channel, $input)
    {
        switch ($channel) {
            case 'email':
                $notifiable = User::whereEmail($input)->first();
                break;
            case 'direct':
                //get location as well
                $notifiable = User::whereHas('emrDirect', function ($emr) use ($input) {
                    $emr->where('address', $input);
                })->first();
                break;
            case 'fax':
                $notifiable = Location::whereFax($input)->first();
                break;
            default:
                $notifiable = null;
        }

        return $notifiable;
    }
}
