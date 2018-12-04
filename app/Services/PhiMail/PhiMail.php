<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\PhiMail;

use App\Contracts\DirectMail;
use App\Jobs\ImportCcda;
use App\Models\MedicalRecords\Ccda;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Maknz\Slack\Facades\Slack;

class PhiMail implements DirectMail
{
    protected $ccdas = [];
    private $connector;

    public function __destruct()
    {
        if (!$this->connector) {
            return;
        }

        try {
            $this->connector->close();
        } catch (\Exception $ignore) {
            Log::critical($ignore);
        }
    }

    public function loadFile(
        $filename
    ) {
        return file_get_contents($filename);
    }

    public function receive()
    {
        if (!$this->initPhiMailConnection()) {
            return false;
        }

        if (!$this->connector) {
            return false;
        }

        try {
            while (true) {
                // check next message or status update
                $message = $this->connector->check();

                if (null == $message) {
                    break;
                }

                if (!$message->isMail()) {
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

                for ($i = 0; $i <= $message->numAttachments; ++$i) {
                    // Get content for part i of the current message.
                    $showRes = $this->connector->show($i);

//                    Log::critical("MimeType = " . $showRes->mimeType
//                        . "; length=" . $showRes->length . "\n");

                    // List all the headers. Headers are set by the
                    // sender and may include Subject, Date, additional
                    // addresses to which the message was sent, etc.
                    // Do NOT use the To: header to determine the address
                    // to which this message should be delivered
                    // internally; use $messagerecipient instead.
//                    foreach ($showRes->headers as $header) {
//                        Log::critical("Header: " . $header . "\n");
//                    }

                    // Process the content; for this example text data
                    // is echoed to the console and non-text data is
                    // written to files.

                    if (str_contains($showRes->mimeType, 'plain')) {
                        Log::info('Plain Mime Type');
                        self::writeDataFile(storage_path(Carbon::now()->toAtomString().'.txt'), $showRes->data);
                    } elseif (str_contains($showRes->mimeType, 'xml')) {
                        Log::info('XML Mime Type');
                        self::writeDataFile(storage_path(Carbon::now()->toAtomString().'.xml'), $showRes->data);
                        $this->importCcd($showRes);
                    } else {
                        Log::info('Other Mime Type');
                        self::writeDataFile(storage_path(Carbon::now()->toAtomString().'.txt'), $showRes->data);
                    }

                    // Display the list of attachments and associated info. This info is only
                    // included with message part 0.
                    for ($k = 0; 0 == $i && $k < $message->numAttachments; ++$k) {
                        Log::info('Attachment '.($k + 1)
                            .': '.$showRes->attachmentInfo[$k]->mimeType
                            .' fn:'.$showRes->attachmentInfo[$k]->filename
                            .' Desc:'.$showRes->attachmentInfo[$k]->description
                            ."\n");
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

    public function send(
        $outboundRecipient,
        $binaryAttachmentFilePath,
        $binaryAttachmentFileName,
        $ccdaAttachmentPath = null,
        User $patient = null
    ) {
        if (!$this->initPhiMailConnection()) {
            return false;
        }

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

            //Report to Slack
            foreach ($srList as $sr) {
                $status = $sr->succeeded
                    ? " succeeded id={$sr->messageId}"
                    : "failed err={$sr->errorText}";

                sendSlackMessage('#background-tasks', "Send to {$sr->recipient}: ${status} \n");
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
    }

    private function importCcd(
        $attachment
    ) {
        $this->ccda = Ccda::create([
            'user_id'   => null,
            'vendor_id' => 1,
            'xml'       => $attachment->data,
            'source'    => Ccda::EMR_DIRECT,
        ]);

        ImportCcda::dispatch($this->ccda)->onQueue('low');
    }

    private function initPhiMailConnection()
    {
        try {
            $phiMailUser = config('services.emr-direct.user');
            $phiMailPass = config('services.emr-direct.password');

            // Use the following command to enable client TLS authentication, if
            // required. The key file referenced should contain the following
            // PEM data concatenated into one file:
            //   <your_private_key.pem>
            //   <your_client_certificate.pem>
            //   <intermediate_CA_certificate.pem>
            //   <root_CA_certificate.pem>
            //
            PhiMailConnector::setClientCertificate(
                base_path().config('services.emr-direct.conc-keys-pem-path'),
                config('services.emr-direct.pass-phrase')
            );

            // This command is recommended for added security to set the trusted
            // SSL certificate or trust anchor for the phiMail server.
            PhiMailConnector::setServerCertificate(base_path().config('services.emr-direct.server-cert-pem-path'));

            $phiMailServer = config('services.emr-direct.mail-server');
            $phiMailPort   = config('services.emr-direct.port');

            $this->connector = new PhiMailConnector($phiMailServer, $phiMailPort);
            $this->connector->authenticateUser($phiMailUser, $phiMailPass);

            return true;
        } catch (\Exception $e) {
            $this->handleException($e);
        }

        return false;
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

    private function writeDataFile(
        $filename,
        $data
    ) {
        return file_put_contents($filename, $data);
    }
}
