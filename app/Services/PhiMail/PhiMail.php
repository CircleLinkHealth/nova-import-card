<?php namespace App\Services\PhiMail;

use App\CLH\CCD\Importer\QAImportManager;
use App\CLH\CCD\ItemLogger\CcdItemLogger;
use App\CLH\Repositories\CCDImporterRepository;
use App\Models\CCD\Ccda;
use App\Models\CCD\CcdVendor;
use App\User;
use Illuminate\Support\Facades\Log;
use Maknz\Slack\Facades\Slack;

class PhiMail
{

    /**
     * @param args the command line arguments
     */
    public function sendReceive()
    {

        try {
            $fileNames = [];
            $ccdas = [];

            // Specify which parts of the example to run.
            // Note: Send and receive examples are grouped here for demonstration
            // purposes only. In general, receive operations would run in a separate
            // background process.
            $send = false;
            $receive = true;

            $phiMailServer = env('EMR_DIRECT_MAIL_SERVER');
            $phiMailPort = env('EMR_DIRECT_PORT'); // this is the default port #

            $phiMailUser = env('EMR_DIRECT_USER');
            $phiMailPass = env('EMR_DIRECT_PASSWORD');

            $outboundRecipient = "recipient@direct.anotherdomain.com";
//            $attachmentSaveDirectory = base_path() . '/storage/ccdas/';

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

            $c = new PhiMailConnector($phiMailServer, $phiMailPort);
            $c->authenticateUser($phiMailUser, $phiMailPass);

            // Sample code to send a Direct message.
            if ($send) {
                echo("Sending a CDA as an attachment\n");

                // After authentication, the server has a blank outgoing message
                // template. Begin building this message by adding a recipient.
                // Multiple recipients can be added by calling this command more
                // than once. A separate message will be sent for each recipient.
                $recip = $c->addRecipient($outboundRecipient);

                // The server returns information about the recipient if the
                // address entered is accepted, otherwise an exception is thrown.
                // How you use this recipient information is up to you...
                echo('Recipient Info = ' . $recip . "\n");

                // Optionally, set the Subject of the outgoing message.
                // This will override the default message Subject set by the server.
                $c->setSubject('Test Subject sent by PHP connector');

                // Add the main body of the message.
                $c->addText("This is the main message content. A CDA is attached.");

                // Add a CDA attachment and let phiMail server assign a filename.
                $c->addCDA(self::loadFile("/tmp/Test_cda.xml"));

                // Optionally, add a binary attachment and specify the
                // attachment filename yourself.
                $c->addRaw(self::loadFile("/tmp/Test_pdf.pdf"), "test.pdf");

                // Optionally, request a final delivery notification message.
                // Note that not all HISPs can provide this notification when requested.
                // If the receiving HISP does not support this feature, the message will
                // result in a failure notification after the timeout period has elapsed.
                // This command will override the default setting set by the server.
                //
                //$c->setDeliveryNotification(true);

                // Send the message. srList will contain one entry for each recipient.
                // If more than one recipient was specified, then each would have an entry.
                $srList = $c->send();
                foreach ($srList as $sr) {
                    echo("Send to " . $sr->recipient);
                    echo($sr->succeeded
                        ? " succeeded id="
                        : "failed err=");
                    echo($sr->succeeded
                        ? $sr->messageId
                        : $sr->errorText);
                    echo("\n");
                }
            }

            // Sample code to check for any incoming messages. Generally, this
            // code would run in a separate background process to poll the
            // phiMail server at regular intervals for new messages. In production
            // $phiMailUser above would be set to an address group to efficiently
            // retrieve messages for all addresses in the address group, rather
            // than iterating through individual addresses.  Please see the
            // API documentation for further information about address groups.
            if ($receive) {
                while (true) {
                    echo("============\n");
                    echo("Checking mailbox\n");

                    // check next message or status update
                    $cr = $c->check();

                    if ($cr == null) {

                        Slack::to('#background-tasks-dev')
                            ->send("Checked EMR Direct Mailbox. There where no messages. \n" . env('DB_DATABASE'));
                        break;

                    } else {
                        if ($cr->isMail()) {
                            // If you are checking messages for an address group,
                            // $cr->recipient will contain the address in that
                            // group to which this message should be delivered.
                            Log::critical("A new message is available for " . $cr->recipient . "\n");
                            Log::critical("from " . $cr->sender . "; id "
                                . $cr->messageId . "; #att=" . $cr->numAttachments
                                . "\n");

                            for ($i = 0; $i <= $cr->numAttachments; $i++) {
                                // Get content for part i of the current message.
                                $showRes = $c->show($i);

                                Log::critical("MimeType = " . $showRes->mimeType
                                    . "; length=" . $showRes->length . "\n");

                                // List all the headers. Headers are set by the
                                // sender and may include Subject, Date, additional
                                // addresses to which the message was sent, etc.
                                // Do NOT use the To: header to determine the address
                                // to which this message should be delivered
                                // internally; use $cr->recipient instead.
                                foreach ($showRes->headers as $header) {
                                    Log::critical("Header: " . $header . "\n");
                                }

                                // Process the content; for this example text data
                                // is echoed to the console and non-text data is
                                // written to files.

                                if (str_contains($showRes->mimeType, 'plain')) {
                                    // ... do something with text parts ...
                                    Log::critical('The plain text part of the mail');
                                    Log::critical($showRes->data);
                                    self::writeDataFile(storage_path(str_random(20) . '.txt'), $showRes->data);
                                } elseif (str_contains($showRes->mimeType, 'xml')) {
                                    //save ccd to file
                                    self::writeDataFile(storage_path(str_random(20) . '.xml'), $showRes->data);
                                    $import = $this->importCcd($cr->sender, $showRes);

                                    if (!$import) {
                                        continue;
                                    }

                                    $ccdas[] = $import;
                                }

                                // Display the list of attachments and associated info. This info is only
                                // included with message part 0.
                                for ($k = 0; $i == 0 && $k < $cr->numAttachments; $k++) {
                                    Log::critical("Attachment " . ($k + 1)
                                        . ": " . $showRes->attachmentInfo[$k]->mimeType
                                        . " fn:" . $showRes->attachmentInfo[$k]->filename
                                        . " Desc:" . $showRes->attachmentInfo[$k]->description
                                        . "\n");
                                }
                            }
                            // This signals the server that the message can be safely removed from the queue
                            // and should only be sent after all required parts of the message have been
                            // retrieved and processed.
                            $c->acknowledgeMessage();

                            Log::critical('Number of Attachments: ' . $cr->numAttachments);

                            if ($cr->numAttachments > 0) {
                                $this->notifyAdmins($cr->numAttachments);

                                $message = "Checked EMR Direct Mailbox. There where {$cr->numAttachments} attachment(s). \n";

                                echo $message;

                                Slack::to('#background-tasks')->send($message);
                            }

                            Log::critical('***************');

                        } else {

                            // Process a status update for a previously sent message.
//                        echo ("Status message for id = " . $cr->messageId . "\n");
//                        echo ("  StatusCode = " . $cr->statusCode . "\n");
//                        if ($cr->info != null) echo ("  Info = " . $cr->info . "\n");
                            if ($cr->statusCode == "failed") {
                                // ...do something about a failed message...
                                // $cr->messageId will match the messageId returned
                                // when you originally sent the corresponding message
                                // See the API documentation for information about
                                // status notification types and their meanings.
                            }

                            // This signals the server that the status update can be
                            // safely removed from the queue,
                            // i.e. it has been successfully received and processed.
                            // Note: this is NOT the same method used to acknowledge
                            // regular messages.
                            $c->acknowledgeStatus();
                        }
                    }
                }
            }

        } catch (\Exception $e) {
            $message = $e->getMessage() . "\n" . $e->getFile() . "\n" . $e->getLine();
            $traceString = $e->getTraceAsString() . "\n";

            Log::error($message);
            Log::error($traceString);
        }

        try {
            $c->close();
        } catch (\Exception $ignore) {
        }

        echo("============END\n");

    }

    public function loadFile($filename)
    {
        return file_get_contents($filename);
    }

    public function writeDataFile(
        $filename,
        $data
    ) {
        return file_put_contents($filename, $data);
    }

    public function importCcd(
        $sender,
        $attachment
    ) {
        $atPosition = strpos($sender, '@');

        if (!$atPosition) {
            return false;
        }

        //get the domain of the sender's emr address to see where it came from
        $senderDomain = substr($sender, $atPosition);

        Log::critical("Sender EMR Address Domain: {$senderDomain}");

        $vendorMap = Ccda::EMAIL_DOMAIN_TO_VENDOR_MAP;

        //Map the email domain of the sender to one of our CCD Vendors, or assume carolina meds
        $vendorId = key_exists($senderDomain, $vendorMap)
            ? $vendorMap[$senderDomain]
            : 10;

        $vendor = CcdVendor::find($vendorId);

        $ccda = Ccda::create([
            'user_id'   => null,
            'vendor_id' => $vendorId,
            'xml'       => $attachment->data,
            'source'    => Ccda::EMR_DIRECT,
        ]);

        $ccdaRepo = new CCDImporterRepository;

        $json = $ccdaRepo->toJson($ccda->xml);
        $ccda->json = $json;
        $ccda->save();

        $logger = new CcdItemLogger($ccda);
        $logger->logAll();

        $importer = new QAImportManager($vendor->program_id, $ccda);
        $importer->generateCarePlanFromCCD();

        return [
            'id'       => $ccda->id,
            'fileName' => $attachment->filename,
        ];
    }

    /**
     * This is to help notify us of the status of CCDs we receive.
     *
     *
     * @param User $user
     * @param Ccda $ccda
     * @param $fileNames
     * @param null $line
     * @param null $errorMessage
     */
    public function notifyAdmins($numberOfCcds)
    {
        if (app()->environment('local')) {
            return;
        }

        //the worker generates the route using localhost so I am hardcoding it
//        $link = route('view.files.ready.to.import');
        $link = 'https://www.careplanmanager.com/ccd-importer/qaimport';

        Slack::to('#ccd-file-status')
            ->send("We received {$numberOfCcds} CCDs from EMR Direct. \n Please visit {$link} to import.");
    }
}
