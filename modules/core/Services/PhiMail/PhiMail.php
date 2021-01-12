<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Services\PhiMail;

use CircleLinkHealth\Core\Contracts\DirectMail;
use CircleLinkHealth\Core\DirectMail\Actions\Ccda\GetOrCreateCcdaXml;
use CircleLinkHealth\Core\Services\PhiMail\Events\DirectMailMessageReceived;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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
     * @param null $dmUserAddress
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     *
     * @return bool
     */
    public function receive($dmUserAddress = null)
    {
        $this->initPhiMailConnection($dmUserAddress);

        if ( ! is_a($this->connector, PhiMailConnector::class)) {
            return false;
        }

        try {
            while ($message = $this->connector->check()) {
                $message->isMail()
                    ? $this->handleValidMail($message)
                    : $this->handleInvalidMail($message);
            }
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * @param $outboundRecipient
     * @param $binaryAttachmentFilePath
     * @param $binaryAttachmentFileName
     * @param null       $ccdaContents
     * @param mixed|null $body
     * @param mixed|null $subject
     * @param mixed|null $sender
     *
     * @throws \Exception
     *
     * @return bool|SendResult[]
     */
    public function send(
        $outboundRecipient,
        $binaryAttachmentFilePath = null,
        $binaryAttachmentFileName = null,
        $ccdaContents = null,
        User $patient = null,
        $body = null,
        $subject = null,
        $sender = null
    ) {
        $this->initPhiMailConnection($sender);

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

            if ($binaryAttachmentFilePath) {
                // Add a binary attachment and specify the attachment filename.
                $this->connector->addRaw(self::loadFile($binaryAttachmentFilePath), $binaryAttachmentFileName);
            }

            $ccdaContent = $this->addCcdaIfYouShould($patient);

            if ($ccdaContent) {
                // Add a CDA attachment and let phiMail server assign a filename.
                $this->connector->addCDA($ccdaContent);
            }

            // Optionally, request a final delivery notification message.
            // Note that not all HISPs can provide this notification when requested.
            // If the receiving HISP does not support this feature, the message will
            // result in a failure notification after the timeout period has elapsed.
            // This command will override the default setting set by the server.
            //
            // $this->connector->setDeliveryNotification(true);

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
            return $this->handleException($e);
        }

        return $srList ?? false;
    }

    private function addCcdaIfYouShould(?User $patient)
    {
        if ( ! $patient) {
            return null;
        }
        $patient->load('primaryPractice');

        try {
            if ((bool) $patient->primaryPractice->cpmSettings()->include_ccda_with_dm && $patient->hasCcda()) {
                $content = GetOrCreateCcdaXml::forPatient($patient);

                if ($content && ! Str::startsWith($content, ['<?xml'])) {
                    $content = '<?xml version="1.0"?>
<?xml-stylesheet type="text/xsl" href="CDA.xsl"?>'.$content;
                }

                if ($content) {
                    return $content;
                }
            }
        } catch (\Exception $e) {
            Log::error('UPG CCDA not attached for patient '.$patient->id);
        }
    }

    private function handleException(\Exception $e)
    {
        $message     = $e->getMessage()."\n".$e->getFile()."\n".$e->getLine();
        $traceString = $e->getTraceAsString()."\n";

        Log::critical($message);
        Log::critical($traceString);

        if (auth()->guest()) {
            throw $e;
        }

        return $e->getMessage();
    }

    /**
     * @throws \Exception
     */
    private function handleInvalidMail(CheckResult $cr)
    {
        if ('failed' == $cr->statusCode) {
            Log::error("DirectMail Message Fail. Message ID: `$cr->messageId`. Logged from:".__METHOD__.':'.__LINE__.'  Info = '.$cr->info."\n");
        }

        $this->connector->acknowledgeStatus();
    }

    /**
     * @throws \Exception
     */
    private function handleValidMail(CheckResult $message)
    {
        $dm = $this
            ->incomingMessageHandler
            ->createNewDirectMessage($message);

        for ($i = 0; $i <= $message->numAttachments; ++$i) {
            // Get content for part i of the current message.
            $showRes = $this->connector->show($i);

            $this
                ->incomingMessageHandler
                ->handleMessageAttachment($dm, $showRes->mimeType, $showRes->data);

            // Store the list of attachments and associated info. This info is only
            // included with message part 0.
            if (0 == $i) {
                $this
                    ->incomingMessageHandler
                    ->storeMessageSubject($dm, $showRes);
            }
        }

        $this->incomingMessageHandler->processCcdas($dm);

        $this->connector->acknowledgeMessage();

        event(new DirectMailMessageReceived($dm));
    }

    /**
     * @param null $dmUserAddress
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function initPhiMailConnection($dmUserAddress = null)
    {
        $phiMailUser    = $dmUserAddress ? $dmUserAddress : config('core.services.emr-direct.user');
        $phiMailPass    = config('core.services.emr-direct.password');
        $clientCertPath = base_path('emr-direct-client-cert.pem');
        $serverCertPath = base_path('emr-direct-server-cert.pem');

        // Use the following command to enable client TLS authentication, if
        // required. The key file referenced should contain the following
        // PEM data concatenated into one file:
        //   <your_private_key.pem>
        //   <your_client_certificate.pem>
        //   <intermediate_CA_certificate.pem>
        //   <root_CA_certificate.pem>
        PhiMailConnector::setClientCertificate(
            $clientCertPath,
            config('core.services.emr-direct.pass-phrase')
        );

        // This command is recommended for added security to set the trusted
        // SSL certificate or trust anchor for the phiMail server.
        PhiMailConnector::setServerCertificate($serverCertPath);

        $phiMailServer = config('core.services.emr-direct.mail-server');
        $phiMailPort   = config('core.services.emr-direct.port');

        $this->connector = new PhiMailConnector($phiMailServer, $phiMailPort);
        $this->connector->authenticateUser($phiMailUser, $phiMailPass);
    }
}
