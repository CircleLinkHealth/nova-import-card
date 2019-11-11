<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Traits;

trait ArrayableNotification
{
    public function notificationData($notifiable)
    {
        return [
            'sender_id'       => $this->senderId(),
            'receiver_id'     => $notifiable->id,
            'patient_name'    => $this->getPatientName(),
            'note_id'         => $this->noteId(), //for activities will be null till task is completed. then will be updated
            'attachment_id'   => $this->getAttachment()->id,
            'redirect_link'   => $this->redirectLink(),
            'description'     => $this->description(),
            'attachment_type' => $this->attachmentType(),
            'subject'         => $this->getSubject(),
            'sender_name'     => $this->senderName(),
        ];
    }
}
