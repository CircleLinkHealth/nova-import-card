<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\PhiMail;

use App\Contracts\DirectMail;
use App\DirectMailMessage;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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

    public function __construct(IncomingMessageHandler $incomingMessageHandler)
    {
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
     * @throws \Exception
     *
     * @return bool
     */
    public function receive()
    {
        $this->initPhiMailConnection();

        if ( ! is_a($this->connector, PhiMailConnector::class)) {
            return false;
        }

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
                } else {
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

                    if ($message->numAttachments > 0) {
                        $this->notifyAdmins($dm);

                        $message = "Checked EMR Direct Mailbox. There where {$message->numAttachments} attachment(s). \n";

                        sendSlackMessage('#background-tasks', $message);
                    }
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
     * @param null       $ccdaAttachmentPath
     * @param mixed|null $body
     * @param mixed|null $subject
     *
     * @throws \Exception
     *
     * @return bool|SendResult[]
     */
    public function send(
        $outboundRecipient,
        $binaryAttachmentFilePath = null,
        $binaryAttachmentFileName = null,
        $ccdaAttachmentPath = null,
        User $patient = null,
        $body = null,
        $subject = null
    ) {
        //add case when everything is null
        $this->initPhiMailConnection();

        if ( ! is_a($this->connector, PhiMailConnector::class)) {
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
                if ( ! $subject) {
                    $this->connector->setSubject('Message from '.$patient->saasAccountName());
                }

                // Add the main body of the message.
                if ( ! $body) {
                    $this->connector->addText("This is message regarding patient {$patient->getFullName()}.");
                }
            }

            if ($body) {
                $this->connector->addText($body);
            }
            if ($subject) {
                $this->connector->setSubject($subject);
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

            if (isProductionEnv()) {
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

    /**
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function fetchKeyIfNotExists(string $certFileName, string $certPath)
    {
        $storage = Storage::disk('secrets');

        if ( ! is_readable($certPath) && $storage->has($certFileName)) {
            touch($certPath);

            if ( ! is_writeable($certPath)) {
                throw new \Exception("$certPath is not writable");
            }

            $contents = $storage->get($certFileName);

            $written = file_put_contents($certPath, $contents);

            if (false === $written) {
                throw new \Exception("Could not write `$certFileName` to `$certPath`.");
            }
        }
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
     * @throws \Exception
     */
    private function initPhiMailConnection()
    {
        $phiMailUser        = config('services.emr-direct.user');
        $phiMailPass        = config('services.emr-direct.password');
        $clientCertPath     = base_path(config('services.emr-direct.conc-keys-pem-path'));
        $serverCertPath     = base_path(config('services.emr-direct.server-cert-pem-path'));
        $clientCertFileName = config('services.emr-direct.client-cert-filename');
        $serverCertFileName = config('services.emr-direct.server-cert-filename');

        $this->fetchKeyIfNotExists($serverCertFileName, $serverCertPath);
        $this->fetchKeyIfNotExists($clientCertFileName, $clientCertPath);

        // Use the following command to enable client TLS authentication, if
        // required. The key file referenced should contain the following
        // PEM data concatenated into one file:
        //   <your_private_key.pem>
        //   <your_client_certificate.pem>
        //   <intermediate_CA_certificate.pem>
        //   <root_CA_certificate.pem>
        //
        PhiMailConnector::setClientCertificate(
            $clientCertPath,
            config('services.emr-direct.pass-phrase')
        );

        // This command is recommended for added security to set the trusted
        // SSL certificate or trust anchor for the phiMail server.
        PhiMailConnector::setServerCertificate($serverCertPath);

        $phiMailServer = config('services.emr-direct.mail-server');
        $phiMailPort   = config('services.emr-direct.port');

        $this->connector = new PhiMailConnector($phiMailServer, $phiMailPort);
        $this->connector->authenticateUser($phiMailUser, $phiMailPass);
    }

    /**
     * This is to help notify us of the status of CCDs we receive.
     */
    private function notifyAdmins(
        DirectMailMessage $dm
    ) {
        if (app()->environment('local')) {
            return;
        }

        $link        = route('import.ccd.remix');
        $messageLink = route('direct-mail.show', [$dm->id]);

        sendSlackMessage(
            '#ccd-file-status',
            "We received a message from EMR Direct. \n Click here to see the message {$messageLink}. \n If a CCD was included in the message, it has been imported. Click here {$link} to QA and Import."
        );
    }
}
