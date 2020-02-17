<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\PhiMail;

use App\DirectMailMessage;
use App\Jobs\ImportCcda;
use Carbon\Carbon;
use CircleLinkHealth\SharedModels\Entities\Ccda;

/**
 * Handle an incoming message from EMR Direct Mail API.
 *
 * Class IncomingMessageHandler
 */
class IncomingMessageHandler
{
    /**
     * Creates a new Direct Message.
     *
     * @return DirectMailMessage
     */
    public function createNewDirectMessage(CheckResult $message)
    {
        return DirectMailMessage::create(
            [
                'message_id'      => $message->messageId,
                'from'            => $message->sender,
                'to'              => $message->recipient,
                'body'            => $message->info,
                'num_attachments' => $message->numAttachments,
            ]
        );
    }

    /**
     * Handles the message's attachments.
     */
    public function handleMessageAttachment(DirectMailMessage &$dm, ShowResult $showRes)
    {
        if (str_contains($showRes->mimeType, 'plain')) {
            $dm->body = $showRes->data;
            $dm->save();
        } elseif (str_contains($showRes->mimeType, 'xml') && false !== stripos($showRes->data, '<ClinicalDocument')) {
            $this->storeAndImportCcd($showRes, $dm);
        }
        //todo: fix how to know we got from upg => check direct mails from from the db from UPG
        elseif (str_contains($showRes->mimeType, 'pdf') && str_contains($dm->from, 'upg')){
            $this->storePdf($showRes, $dm);
        }
        else {
            $path = storage_path('dm_id_'.$dm->id.'_attachment_'.Carbon::now()->toAtomString());
            file_put_contents($path, $showRes->data);
            $dm->addMedia($path)
                ->toMediaCollection("dm_{$dm->id}_attachments");
        }
    }

    /**
     * Store the subject of the message.
     *
     * @param $dm
     */
    public function storeMessageSubject(&$dm, ShowResult $showRes)
    {
        // Headers are set by the sender and may include Subject, Date, additional addresses to which the message was sent, etc.
        // Do NOT use the To: header to determine the address to which this message should be delivered internally; use $message->recipient instead.
        foreach ($showRes->headers as $header) {
            if (false !== ($pos = strpos($header, 'Subject:'))) {
                $dm->subject = trim(substr($header, $pos + 8));
                $dm->save();
            }
        }
    }

    /**
     * Stores and imports a CCDA.
     *
     * @param $attachment
     */
    private function storeAndImportCcd(
        $attachment,
        DirectMailMessage $dm
    ) {
        $ccda = Ccda::create(
            [
                'direct_mail_message_id' => $dm->id,
                'user_id'                => null,
                'xml'                    => $attachment->data,
                'source'                 => Ccda::EMR_DIRECT,
            ]
        );

        ImportCcda::withChain([
                                  //if upg change status.
                                  //re-run until we get pdf?
                                  //call itself every 30secs
//                                  new ProcessPdfIfYouShould()
                              ])->dispatch($ccda)->onQueue('low');
    }

    private function storePdf(){
        //read pdf
        //put media on UPG along with patient json after you read
        (new ProcessThisPdf())->dispatch();
    }
}
