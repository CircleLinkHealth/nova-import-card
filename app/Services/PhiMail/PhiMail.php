<?php namespace App\Services\PhiMail;

use App\CLH\Repositories\CCDImporterRepository;
use App\Jobs\TrainCcdaImporter;
use App\Models\MedicalRecords\Ccda;
use App\User;
use Illuminate\Support\Facades\Log;
use Maknz\Slack\Facades\Slack;

class PhiMail
{
    protected $ccdas = [];
    private $connector;

    public function __construct()
    {
        if (!$this->initPhiMailConnection()) {
            return false;
        }
    }

    private function initPhiMailConnection()
    {
        try {
            $phiMailUser = env('EMR_DIRECT_USER');
            $phiMailPass = env('EMR_DIRECT_PASSWORD');

            // Use the following command to enable client TLS authentication, if
            // required. The key file referenced should contain the following
            // PEM data concatenated into one file:
            //   <your_private_key.pem>
            //   <your_client_certificate.pem>
            //   <intermediate_CA_certificate.pem>
            //   <root_CA_certificate.pem>
            //
            PhiMailConnector::setClientCertificate(
                base_path() . env('EMR_DIRECT_CONC_KEYS_PEM_PATH'),
                env('EMR_DIRECT_PASS_PHRASE')
            );

            // This command is recommended for added security to set the trusted
            // SSL certificate or trust anchor for the phiMail server.
            PhiMailConnector::setServerCertificate(base_path() . env('EMR_DIRECT_SERVER_CERT_PEM_PATH'));

            $phiMailServer = env('EMR_DIRECT_MAIL_SERVER');
            $phiMailPort = env('EMR_DIRECT_PORT');

            $this->connector = new PhiMailConnector($phiMailServer, $phiMailPort);
            $this->connector->authenticateUser($phiMailUser, $phiMailPass);

            return true;
        } catch (\Exception $e) {
            $this->handleException($e);
        }

        return false;
    }

    private function handleException(\Exception $e)
    {
        $message = $e->getMessage() . "\n" . $e->getFile() . "\n" . $e->getLine();
        $traceString = $e->getTraceAsString() . "\n";

        Log::critical($message);
        Log::critical($traceString);
    }

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

    /**
     * @param args the command line arguments
     */
    public function sendReceive(
        $outboundReceipient = null
    ) {
        try {
            if ($outboundReceipient) {
                $this->send($outboundReceipient);
            }

            //Receive mail
            $this->receive();
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }

    public function send(
        $outboundRecipient,
        $binaryAttachmentFilePath = null,
        $binaryAttachmentFileName = null,
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

            // Optionally, set the Subject of the outgoing message.
            // This will override the default message Subject set by the server.
            $this->connector->setSubject('Message from CircleLink Health');

            if ($patient) {
                // Add the main body of the message.
                $this->connector->addText("This is message regarding patient {$patient->fullName}.");
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


                sendSlackMessage('#background-tasks', "Send to {$sr->recipient}: $status \n");
            }

        } catch (\Exception $e) {
            $this->handleException($e);
        }

        return $srList ?? false;

    }

    public function loadFile(
        $filename
    ) {
        return file_get_contents($filename);
    }

    public function receive()
    {
        if (!$this->connector) {
            return false;
        }

        try {
            while (true) {
                // check next message or status update
                $message = $this->connector->check();

                if ($message == null) {
                    break;
                }

                if (!$message->isMail()) {
                    // Process a status update for a previously sent message.
//                        echo ("Status message for id = " . $message->messageId . "\n");
//                        echo ("  StatusCode = " . $message->statusCode . "\n");
//                        if ($message->info != null) echo ("  Info = " . $message->info . "\n");
                    if ($message->statusCode == "failed") {
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

                for ($i = 0; $i <= $message->numAttachments; $i++) {
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
                        // ... do something with text parts ...
//                        Log::critical('The plain text part of the mail');
//                        Log::critical($showRes->data);
                        self::writeDataFile(storage_path(str_random(20) . '.txt'), $showRes->data);
                    } elseif (str_contains($showRes->mimeType, 'xml')) {
                        //save ccd to file
                        self::writeDataFile(storage_path(str_random(20) . '.xml'), $showRes->data);
                        $this->importCcd($showRes);
                    }

                    // Display the list of attachments and associated info. This info is only
                    // included with message part 0.
//                    for ($k = 0; $i == 0 && $k < $message->numAttachments; $k++) {
//                        Log::critical("Attachment " . ($k + 1)
//                            . ": " . $showRes->attachmentInfo[$k]->mimeType
//                            . " fn:" . $showRes->attachmentInfo[$k]->filename
//                            . " Desc:" . $showRes->attachmentInfo[$k]->description
//                            . "\n");
//                    }
                }
                // This signals the server that the message can be safely removed from the queue
                // and should only be sent after all required parts of the message have been
                // retrieved and processed.:log
                $this->connector->acknowledgeMessage();

//                Log::critical('Number of Attachments: ' . $message->numAttachments);

                if ($message->numAttachments > 0) {
                    $this->notifyAdmins($message->numAttachments);

                    $message = "Checked EMR Direct Mailbox. There where {$message->numAttachments} attachment(s). \n";

                    echo $message;

                    sendSlackMessage('#background-tasks', $message);
                }

//                Log::critical('***************');
            }
        } catch (\Exception $e) {
            $this->handleException($e);
        }

        echo '===END===';
    }

    private function writeDataFile(
        $filename,
        $data
    ) {
        return file_put_contents($filename, $data);
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

        dispatch(new TrainCcdaImporter($this->ccda));
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

        $link = route('view.files.ready.to.import');

        sendSlackMessage('#ccd-file-status',
            "We received {$numberOfCcds} CCDs from EMR Direct. \n Please visit {$link} to import.");
    }

}
