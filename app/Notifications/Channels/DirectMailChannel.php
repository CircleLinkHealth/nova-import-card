<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications\Channels;

use App\Contracts\DirectMail;
use Illuminate\Notifications\Notification;

class DirectMailChannel
{
    protected $dm;

    public function __construct(DirectMail $dm)
    {
        $this->dm = $dm;
    }

    /**
     * Send the given notification.
     *
     * @param mixed $notifiable
     * @param \Illuminate\Notifications\Notification $notification
     */
    public function send($notifiable, Notification $notification)
    {
        if ($notifiable->emr_direct_address) {
            $data = $this->getParams($notifiable, $notification);

            $this->dm->send(
                $notifiable->emr_direct_address,
                $data['filePath'],
                $data['fileName'],
                $data['ccdaAttachmentPath'],
                $data['patient'],
                $data['body'],
                $data['subject']);
        }
    }

    private function getParams($notifiable, Notification $notification): array
    {
        $data = $notification->toDirectMail($notifiable);

        $filePath = array_key_exists('filePath', $data)
            ? $data['filePath']
            : null;

        return [
            'filePath'           => $filePath,
            'fileName'           => str_substr_after($filePath, '/'),
            'ccdaAttachmentPath' => array_key_exists('ccdaAttachmentPath', $data)
                ? $data['ccdaAttachmentPath']
                : null,
            'patient'            => array_key_exists('patient', $data)
                ? $data['patient']
                : null,
            'body'               => array_key_exists('body', $data)
                ? $data['body']
                : null,
            'subject'            => array_key_exists('subject', $data)
                ? $data['subject']
                : null,
        ];


    }
}
