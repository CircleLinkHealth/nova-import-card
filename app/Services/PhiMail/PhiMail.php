<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\PhiMail;

use CircleLinkHealth\Core\Contracts\DirectMail;
use App\Services\PhiMail\Events\DirectMailMessageReceived;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PhiMail implements DirectMail
{
    const UPG_NAME = 'UPG';

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
     *@throws \Exception
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

            $ccdaContent = $this->upgTemporaryHack($patient);

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
            $this->handleException($e);
        }

        return $srList ?? false;
    }

    /**
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws \Exception
     */
    private function fetchKeyIfNotExists(string $certFileName, string $certPath)
    {
        $storage = Storage::disk('secrets');

        if ( ! is_readable($certPath)) {
            touch($certPath);

            if ( ! is_writeable($certPath)) {
                throw new \Exception("$certPath is not writable");
            }

            if ( ! $storage->has($certFileName)) {
                throw new \Exception("$certFileName not found on remote drive.");
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
                ->handleMessageAttachment($dm, $showRes);

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
        $phiMailUser        = $dmUserAddress ? $dmUserAddress : config('services.emr-direct.user');
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

    private function upgTemporaryHack(?User $patient)
    {
        if ( ! $patient) {
            return null;
        }
        $patient->load('primaryPractice');

        try {
            if (self::UPG_NAME === $patient->primaryPractice->name && $patient->hasCcda()) {
                $content = $patient->ccdas()->orderByDesc('id')->with('media')->first()->getMedia('ccd')->first()->getFile();

                if ($content && ! Str::startsWith($content, ['<?xml'])) {
                    $content = '<?xml version="1.0"?>
<?xml-stylesheet type="text/xsl" href="CDA.xsl"?>'.$content;
                }

                if ($content) {
                    Log::warning('UPG: Attach patient '.$patient->id.' CCDA');

                    return $content;
                }
            }
        } catch (\Exception $e) {
            Log::error('UPG CCDA not attached for patient '.$patient->id);
        }
    }
}
