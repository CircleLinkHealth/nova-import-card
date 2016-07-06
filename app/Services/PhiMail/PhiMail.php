<?php namespace App\Services\PhiMail;

use App\CLH\CCD\Importer\QAImportManager;
use App\CLH\CCD\ItemLogger\CcdItemLogger;
use App\CLH\Repositories\CCDImporterRepository;
use App\Models\CCD\Ccda;
use App\Models\CCD\CcdVendor;
use App\Services\PhiMail\PhiMailConnector;
use Illuminate\Support\Facades\Log;

class PhiMail {

    public function loadFile($filename) {
        return file_get_contents($filename);
    }

    public function writeDataFile($filename, $data) {
        return file_put_contents($filename, $data);
    }

    /**
     * @param args the command line arguments
     */
    public function sendReceive() {

        try {
            // Specify which parts of the example to run.
            // Note: Send and receive examples are grouped here for demonstration
            // purposes only. In general, receive operations would run in a separate
            // background process. 
            $send = false;
            $receive = true;

            $phiMailServer = "sandbox.phimail-dev.com";
            $phiMailPort = 32541; // this is the default port #

            $phiMailUser = "circlelinkhealth@test.directproject.net";
            $phiMailPass = "gpcn3z9n";

            $outboundRecipient = "recipient@direct.anotherdomain.com";
            $attachmentSaveDirectory = base_path() . '/storage/ccdas/';

            // Use the following command to enable client TLS authentication, if
            // required. The key file referenced should contain the following 
            // PEM data concatenated into one file:
            //   <your_private_key.pem>
            //   <your_client_certificate.pem>
            //   <intermediate_CA_certificate.pem>
            //   <root_CA_certificate.pem>
            //
            //PhiMailConnector::setClientCertificate("/path/to/client_key.pem", "**my_private_key_passphrase**");

            // This command is recommended for added security to set the trusted 
            // SSL certificate or trust anchor for the phiMail server.
            PhiMailConnector::setServerCertificate(base_path() . '/resources/certificates/EMRDirectTestCA.pem');

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
                    echo($sr->succeeded ? " succeeded id=" : "failed err=");
                    echo($sr->succeeded ? $sr->messageId : $sr->errorText);
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
                    Log::info("============\n");
                    Log::info("Checking mailbox\n");

                    // check next message or status update
                    $cr = $c->check();

                    if ($cr == null) {

                        Log::info("Check returned null; no messages on queue.\n");
                        break;

                    } else if($cr->isMail()) {
                        // If you are checking messages for an address group,
                        // $cr->recipient will contain the address in that
                        // group to which this message should be delivered.
                        Log::info("A new message is available for " . $cr->recipient . "\n");
                        Log::info("from " . $cr->sender . "; id "
                            . $cr->messageId . "; #att=" . $cr->numAttachments
                            . "\n");

                        for ($i = 0; $i <= $cr->numAttachments; $i++) {
                            // Get content for part i of the current message.
                            $showRes = $c->show($i);
                            Log::info("MimeType = " . $showRes->mimeType
                                . "; length=" . $showRes->length . "\n");

                            // List all the headers. Headers are set by the
                            // sender and may include Subject, Date, additional
                            // addresses to which the message was sent, etc.
                            // Do NOT use the To: header to determine the address
                            // to which this message should be delivered
                            // internally; use $cr->recipient instead.
                            foreach ($showRes->headers as $header) {
                                Log::info("Header: " . $header . "\n");
                            }

                            // Process the content; for this example text data 
                            // is echoed to the console and non-text data is
                            // written to files.
                            if (!strncmp($showRes->mimeType, 'text/', 5)) {
                                // ... do something with text parts ...
                                // For this example we assume ascii or utf8 
                                $s = $showRes->data;
                                Log::info("Content:\n" . $s . "\n");
                            } else {
                                // ... do something with binary data ...
                                Log::info("Content: <BINARY>  Writing attachment file "
                                    . $showRes->filename . "\n");
//                                self::writeDataFile($attachmentSaveDirectory . $showRes->filename, $showRes->data);


                                $atPosition = strpos($cr->sender, '@');

                                if (!$atPosition) continue;

                                $senderDomain = substr($cr->sender, $atPosition);

                                $vendorId = Ccda::EMAIL_DOMAIN_TO_VENDOR_MAP[$senderDomain];

                                $vendor = CcdVendor::find($vendorId);

                                $ccda = Ccda::create([
                                    'user_id' => null,
                                    'vendor_id' => $vendorId,
                                    'xml' => $showRes->data,
                                    'source' => Ccda::EMR_DIRECT,
                                ]);

                                $ccdaRepo = new CCDImporterRepository;

                                $json = $ccdaRepo->toJson($ccda->xml);
                                $ccda->json = $json;
                                $ccda->save();

                                $logger = new CcdItemLogger($ccda);
                                $logger->logAll();
                                
                                $importer = new QAImportManager($vendor->program_id, $ccda);
                                $importer->generateCarePlanFromCCD();

                                Log::info("{$showRes->filename} imported successfully");
                            }

                            // Display the list of attachments and associated info. This info is only
                            // included with message part 0.
                            for ($k = 0; $i == 0 && $k < $cr->numAttachments; $k++) {
                                Log::info("Attachment " . ($k + 1)
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

                    } else {

                        // Process a status update for a previously sent message.
                        Log::info("Status message for ID = " . $cr->messageId . "\n");
                        Log::info("  StatusCode = " . $cr->statusCode . "\n");
                        if ($cr->info != null) Log::info("  Info = " . $cr->info . "\n");
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

        } catch (\Exception $e) {
            Log::info($e->getMessage() . "\n");
        }

        try {
            $c->close();
        } catch (\Exception $ignore) { }

        Log::info("============END\n");

    }
}
