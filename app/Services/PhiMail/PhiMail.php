<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\PhiMail;

use App\Contracts\DirectMail;
use App\User;
use Illuminate\Support\Facades\Log;

class PhiMail implements DirectMail
{
    /**
     * @var IncomingMessageHandler
     */
    protected $incomingMessageHandler;
    /**
     * @var PhiMailConnector
     */
    private $connector;
    
    public function __construct(PhiMailConnector $connector, IncomingMessageHandler $incomingMessageHandler)
    {
        $this->connector              = $connector;
        $this->incomingMessageHandler = $incomingMessageHandler;
    }
    
    public function __destruct()
    {
        optional($this->connector)->close();
    }
    
    public function loadFile(
        $filename
    ) {
        return file_get_contents($filename);
    }
    
    /**
     * @return bool
     * @throws \Exception
     */
    public function receive()
    {
        try {
            while ($message = $this->connector->check()) {
                if ( ! $message->isMail()) {
                    // Process a status update for a previously sent message.
//                        echo ("Status message for id = " . $message->messageId . "\n");
//                        echo ("  StatusCode = " . $message->statusCode . "\n");
//                        if ($message->info != null) echo ("  Info = " . $message->info . "\n");
                    if ('failed' == $message->statusCode) {
                        // ...do something about a failed message...
                        // $message->messageId will match the messageId returned
                        // when you originally sent the corresponding message
                        // See the API documentation for information about
                        // status notification types and their meanings.
                        
                        Log::error(
                            "DirectMail Message Fail. Message ID: `$message->messageId`. Logged from:".__METHOD__.':'.__LINE__
                        );
                    }
                    
                    // This signals the server that the status update can be
                    // safely removed from the queue,
                    // i.e. it has been successfully received and processed.
                    // Note: this is NOT the same method used to acknowledge
                    // regular messages.
                    $this->connector->acknowledgeStatus();
                }
                
                // If you are checking messages for an address group,
                // $message->recipient will contain the address in that
                // group to which this message should be delivered.
//                Log::critical("A new message is available for " . $message->recipient . "\n");
//                Log::critical("from " . $message->sender . "; id "
//                    . $message->messageId . "; #att=" . $message->numAttachments
//                    . "\n");
                
                $dm = $this
                    ->incomingMessageHandler
                    ->createNewDirectMessage($message);
                
                for ($i = 0; $i <= $message->numAttachments; ++$i) {
                    // Get content for part i of the current message.
                    $showRes = $this->connector->show($i);
                    
                    $this
                        ->incomingMessageHandler
                        ->handleMessageAttachment($dm, $showRes);
                    
                    // Store the list of attachments and associated info. This info is only
                    // included with message part 0.
                    if (0 == $i) {
                        $this
                            ->incomingMessageHandler
                            ->storeMessageSubject($dm, $showRes);
                    }
                }
                // This signals the server that the message can be safely removed from the queue
                // and should only be sent after all required parts of the message have been
                // retrieved and processed.:log
                $this->connector->acknowledgeMessage();
                
                Log::info('Number of Attachments: '.$message->numAttachments);
                
                if ($message->numAttachments > 0) {
                    $this->notifyAdmins($message->numAttachments);
                    
                    $message = "Checked EMR Direct Mailbox. There where {$message->numAttachments} attachment(s). \n";
                    
                    echo $message;
                    
                    sendSlackMessage('#background-tasks', $message);
                }
            }
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }
    
    /**
     * @param $outboundRecipient
     * @param $binaryAttachmentFilePath
     * @param $binaryAttachmentFileName
     * @param null $ccdaAttachmentPath
     * @param User|null $patient
     *
     * @return SendResult[]|bool
     * @throws \Exception
     */
    public function send(
        $outboundRecipient,
        $binaryAttachmentFilePath,
        $binaryAttachmentFileName,
        $ccdaAttachmentPath = null,
        User $patient = null
    ) {
        try {
            // After authentication, the server has a blank outgoing message
            // template. Begin building this message by adding a recipient.
            // Multiple recipients can be added by calling this command more
            // than once. A separate message will be sent for each recipient.
            // The server returns information about the recipient if the
            // address entered is accepted, otherwise an exception is thrown.
            $recipient = $this->connector->addRecipient($outboundRecipient);
            
            if ($patient) {
                // Optionally, set the Subject of the outgoing message.
                // This will override the default message Subject set by the server.
                $this->connector->setSubject('Message from '.$patient->saasAccountName());
                
                // Add the main body of the message.
                $this->connector->addText("This is message regarding patient {$patient->getFullName()}.");
            }
            
            if ($ccdaAttachmentPath) {
                // Add a CDA attachment and let phiMail server assign a filename.
                $this->connector->addCDA(self::loadFile($ccdaAttachmentPath));
            }
            
            if ($binaryAttachmentFilePath) {
                // Add a binary attachment and specify the attachment filename.
                $this->connector->addRaw(self::loadFile($binaryAttachmentFilePath), $binaryAttachmentFileName);
            }
            
            // Optionally, request a final delivery notification message.
            // Note that not all HISPs can provide this notification when requested.
            // If the receiving HISP does not support this feature, the message will
            // result in a failure notification after the timeout period has elapsed.
            // This command will override the default setting set by the server.
            //
            //$this->connector->setDeliveryNotification(true);
            
            // Send the message. srList will contain one entry for each recipient.
            // If more than one recipient was specified, then each would have an entry.
            $srList = $this->connector->send();
            
            if (app()->environment('worker')) {
                //Report to Slack
                foreach ($srList as $sr) {
                    $status = $sr->succeeded
                        ? " succeeded id={$sr->messageId}"
                        : "failed err={$sr->errorText}";
                    
                    sendSlackMessage('#background-tasks', "Send to {$sr->recipient}: ${status} \n");
                }
            }
        } catch (\Exception $e) {
            $this->handleException($e);
        }
        
        return $srList ?? false;
    }
    
    private function handleException(\Exception $e)
    {
        $message     = $e->getMessage()."\n".$e->getFile()."\n".$e->getLine();
        $traceString = $e->getTraceAsString()."\n";
        
        Log::critical($message);
        Log::critical($traceString);
        
        throw $e;
    }
    
    /**
     * This is to help notify us of the status of CCDs we receive.
     *
     * @param $numberOfCcds
     */
    private function notifyAdmins(
        $numberOfCcds
    ) {
        if (app()->environment('local')) {
            return;
        }
        
        $link = route('import.ccd.remix');
        
        sendSlackMessage(
            '#ccd-file-status',
            "We received {$numberOfCcds} CCDs from EMR Direct. \n Please visit {$link} to import."
        );
    }
}
