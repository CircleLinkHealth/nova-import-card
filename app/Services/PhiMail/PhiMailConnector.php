<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\PhiMail;

/*
 * PhiMailConnector class for phiMail Server
 *
 * This sample class implements the phiMail Server Integration API calls to allow
 * client software to send and receive messages and status information over
 * an encrypted connection to the phiMail Server.
 *
 * EMR Direct Data Exchange Protocol API Version Implemented: v1.3.1
 *
 * Version 1.0.104
 *
 * (c) 2014-2016 EMR Direct. All Rights Reserved.
 * Use of this code is subject to the terms of the phiMail Developer
 * License Agreement ("DLA"). This code may not be used, redistributed or
 * modified without the express written consent of EMR Direct, except as
 * permitted by the DLA.
 *
 * @author EMR Direct
 */

class SendResult
{
    /**
     * Additional error information, if available, when $succeeded == false.
     *
     * @var string|null
     */
    public $errorText;
    /**
     * The unique message id assigned to the message for the given recipient
     * when $succeeded == true. This value is not set if the message could
     * not be sent. This id should be used to correlate with any subsquent
     * status notifications.
     *
     * @var string|null
     */
    public $messageId;

    /**
     * The recipient to whom this result object pertains.
     *
     * @var string
     */
    public $recipient;
    /**
     * True if transmission to the recipient succeeded, false otherwise.
     *
     * @var bool
     */
    public $succeeded;

    public function __construct($r, $s, $m)
    {
        $this->recipient = $r;
        $this->succeeded = $s;
        if ($s) {
            $this->messageId = $m;
        } else {
            $this->errorText = $m;
        }
    }
}

class CheckResult
{
    /**
     * (Notifications only) Additional status information, if available.
     *
     * @var string|null
     */
    public $info;

    /**
     * Type of CheckResult: true if mail message, false if status notification.
     *
     * @var bool
     */
    public $mail;
    /**
     * The message id to which this result pertains.
     *
     * @var string
     */
    public $messageId;
    /**
     * (Incoming mail only) The number of attachments to the message.
     *
     * @var int|null
     */
    public $numAttachments;
    /**
     * (Incoming mail only) The Direct Address of the recipient
     * as specified by the sender.
     *
     * @var string|null
     */
    public $recipient;
    /**
     * (Incoming mail only) The Direct Address of the sender.
     *
     * @var string|null
     */
    public $sender;
    /**
     * (Notifications only) The status code: failed or dispatched.
     *
     * @var string|null
     */
    public $statusCode;

    /**
     * Is this a mail message?
     *
     * @return bool true if this is an incoming mail message, false otherwise
     */
    public function isMail()
    {
        return $this->mail;
    }

    /**
     * Is this a status notification?
     *
     * @return bool true if this is a status message, false otherwise
     */
    public function isStatus()
    {
        return ! $this->mail;
    }

    /**
     * Create a new CheckResult object for a mail message.
     *
     * @param string $r         the recipient for the message
     * @param string $s         the sender of the message
     * @param int    $numAttach the number of attachments in this message
     * @param string $id        the unique message id to which this notification pertains
     *
     * @return CheckResult
     */
    public static function newMail($r, $s, $numAttach, $id)
    {
        $instance                 = new self();
        $instance->mail           = true;
        $instance->messageId      = $id;
        $instance->statusCode     = null;
        $instance->info           = null;
        $instance->recipient      = $r;
        $instance->sender         = $s;
        $instance->numAttachments = $numAttach;

        return $instance;
    }

    /**
     * Create a new CheckResult object for a status notification.
     *
     * @param string $id     the unique message id to which this notification pertains
     * @param string $status status code (failed or dispatched)
     * @param string $info   additional information, if available
     *
     * @return CheckResult
     */
    public static function newStatus($id, $status, $info)
    {
        $instance                 = new self();
        $instance->mail           = false;
        $instance->messageId      = $id;
        $instance->statusCode     = $status;
        $instance->info           = $info;
        $instance->recipient      = null;
        $instance->sender         = null;
        $instance->numAttachments = 0;

        return $instance;
    }
}

class ShowResult
{
    /**
     * Array of information about any available attachemnts (part 0 only).
     *
     * @var AttachmentInfo[]|null
     */
    public $attachmentInfo;
    /**
     * The message part content data.
     *
     * @var string
     */
    public $data;
    /**
     * Optional filename for this message part, if specified by sender
     * (part > 0 only).
     *
     * @var string|null
     */
    public $filename;
    /**
     * Array of message header lines (part 0 only).
     *
     * @var string[]|null
     */
    public $headers;
    /**
     * The number of bytes of data in this message part.
     *
     * @var int
     */
    public $length;
    /**
     * The MIME type of the message part.
     *
     * @var string
     */
    public $mimeType;

    /**
     * The message part returned: 0..n-1.
     *
     * @var int
     */
    public $partNum;

    public function __construct($p, $h, $f, $m, $l, $d, $ai)
    {
        $this->partNum        = $p;
        $this->headers        = $h;
        $this->filename       = $f;
        $this->mimeType       = $m;
        $this->length         = $l;
        $this->data           = $d;
        $this->attachmentInfo = $ai;
    }
}

class AttachmentInfo
{
    /** @var string */
    public $description;

    /** @var string */
    public $filename;
    /** @var string */
    public $mimeType;

    public function __construct($filename, $mimeType, $description)
    {
        $this->filename    = $filename;
        $this->mimeType    = $mimeType;
        $this->description = $description;
    }
}

class PhiMailConnector
{
    const PHIMAIL_API_VERSION  = '1.3.1';
    const PHIMAIL_BUILD        = '104';
    const PHIMAIL_READ_TIMEOUT = 120;
    const PHIMAIL_VERSION      = '1.0';

    private static $context;
    private $socket;

    /**
     * Open a new connection to the phiMail server.
     *
     * @param string $s the phiMail server hostname or IP address
     * @param int    $p the phiMail service port number
     *
     * @throws \Exception if connection cannot be opened
     */
    public function __construct($s, $p)
    {
        if ( ! isset(self::$context)) {
            self::$context = stream_context_create();
        }
        $host        = 'ssl://'.$s.':'.$p;
        $socketTries = 0;
        $socket      = 0;
        while ($socketTries < 3 && ! $socket) {
            ++$socketTries;
            $socket = stream_socket_client(
                $host,
                $err1,
                $err2,
                30,
                STREAM_CLIENT_CONNECT,
                self::$context
            );
        }
        if ( ! $socket) {
            $err = 'Connection failed';
            if ($err1) {
                $err .= ": error ${err1} (${err2})";
            }
            if ('111' == $err1) {
                $err .= ': The server may be offline.';
            }
            throw new \Exception($err);
        }
        // set_stream_timeout returns false on failure
        stream_set_timeout($socket, self::PHIMAIL_READ_TIMEOUT);
        $this->socket = $socket;
        $response     = $this->sendCommand('INFO VER PHP '
            .self::PHIMAIL_VERSION.'.'.self::PHIMAIL_BUILD);
    }

    /**
     * Acknowledge current incoming mail message to remove from queue.
     *
     * @throws \Exception on unexpected failure
     */
    public function acknowledgeMessage()
    {
        $response = $this->sendCommand('DONE');
        if ('OK' != $response) {
            throw new \Exception('Message acknowlegement failed: '.$response);
        }
    }

    /**
     * Acknowledge current status notification to remove from queue.
     *
     * @throws \Exception on unexpected failure
     */
    public function acknowledgeStatus()
    {
        $response = $this->sendCommand('OK');
        if ('OK' != $response) {
            throw new \Exception('Status acknowledgement failed: '.$response);
        }
    }

    /**
     * Add a CCR part to the current outgoing message.
     * The data will be tagged as application/xml.
     *
     * @deprecated use addXML instead
     *
     * @param string $data     the CCR XML to add
     * @param string $filename optional filename to associate with the data
     *
     * @throws \Exception on unexpected failure
     */
    public function addCCR($data, $filename = null)
    {
        $this->addData($data, 'ADD CCR', $filename, 'UTF-8');
    }

    /**
     * Add a CC recipient to the current outgoing message.
     *
     * @param string $recipient the Direct address of the intended CC recipient
     *
     * @throws \Exception if the CC recipient cannot be added
     *
     * @return string information found in the corresponding public certificate
     */
    public function addCCRecipient($recipient)
    {
        $response = $this->sendCommand('CC '.$recipient);
        if ('OK' != $response) {
            throw new \Exception('Add CC recipient failed: '.$response);
        }

        return $this->readLine(); //get recipient info
    }

    /**
     * Add a CDA part to the current outgoing message.
     * The data will be tagged as application/xml.
     *
     * @param string $data     the CDA XML to add
     * @param string $filename optional filename to associate with the data
     *
     * @throws \Exception on unexpected failure
     */
    public function addCDA($data, $filename = null)
    {
        $this->addData($data, 'ADD CDA', $filename, 'UTF-8');
    }

    /**
     * Add preformed MIME content to the current outgoing message.
     *
     * @param string $data the MIME content to add
     *
     * @throws \Exception on unexpected failure
     */
    public function addMIME($data)
    {
        $this->addData($data, 'ADD MIME', null, 'US-ASCII');
    }

    /**
     * Add a binary (raw) data part to the current outgoing message.
     * If a filename is provided, the content-type will be determined
     * based on the filename extension; unrecognized extensions or data without
     * a filename will be tagged as application/octet-stream.
     *
     * @param string $dataBytes the raw data to add
     * @param string $filename  optional filename to associate with the data
     *
     * @throws \Exception on unexpected failure
     */
    public function addRaw($dataBytes, $filename = null)
    {
        $this->addData($dataBytes, 'ADD RAW', $filename);
    }

    /**
     * Add a recipient to the current outgoing message.
     *
     * @param string $recipient the Direct address of the intended recipient
     *
     * @throws \Exception if the recipient cannot be added
     *
     * @return string information found in the corresponding public certificate
     */
    public function addRecipient($recipient)
    {
        $response = $this->sendCommand('TO '.$recipient);
        if ('OK' != $response) {
            throw new \Exception('Add recipient failed: '.$response);
        }

        return $this->readLine(); //get recipient info
    }

    /**
     * Add a text part to the current outgoing message.
     * Text data that starts with &gt;html&lt; will be tagged with
     * content type text/html, otherwise the data will be tagged as text/plain.
     *
     * @param string $data     the text to add
     * @param string $filename optional filename to associate with the data
     *
     * @throws \Exception on unexpected failure
     */
    public function addText($data, $filename = null)
    {
        $this->addData($data, 'ADD TEXT', $filename, 'UTF-8');
    }

    /**
     * Add an XML document to the current outgoing message.
     * The data will be tagged as application/xml.
     *
     * @param string $data     the XML content to add
     * @param string $filename optional filename to associate with the data
     *
     * @throws \Exception on unexpected failure
     */
    public function addXML($data, $filename = null)
    {
        $this->addData($data, 'ADD CDA', $filename, 'UTF-8');
    }

    /**
     * Authenticate user.
     *
     * @param string $user the user name to authenticate
     * @param string $pass the password for the user
     *
     * @throws \Exception if user cannot be authenticated
     */
    public function authenticateUser($user, $pass = null)
    {
        $response = $this->sendCommand('AUTH '.$user.$this->extraParam($pass));
        if ('OK' != $response) {
            throw new \Exception('Authentication failed: '.$response);
        }
    }

    /**
     * Terminate session with server. The server will close the connection
     * upon receiving this signal. Should be followed by close().
     *
     * @deprecated use close() without calling bye() first
     *
     * @throws \Exception on unexpected failure
     */
    public function bye()
    {
        $response = $this->sendCommand('BYE');
        if ('BYE' != $response) {
            throw new \Exception('Bye failed: '.$response);
        }
    }

    /**
     * Change password for the currently authenticated user.
     *
     * @param type $pass the new password
     *
     * @throws \Exception on unexpected failure
     */
    public function changePassword($pass)
    {
        $response = $this->sendCommand('PASS '.$pass);
        if ('OK' != $response) {
            throw new \Exception('Password change failed: '.$response);
        }
    }

    /**
     * Check message queue for new status notification or incoming mail message.
     *
     * @throws \Exception on unexpected failure
     *
     * @return CheckResult|null the first item on queue, or null if queue is empty
     */
    public function check()
    {
        $response = $this->sendCommand('CHECK');
        if ('NONE' == $response) {
            return null;
        }
        if ('FAIL' == substr($response, 0, 4)) {
            throw new \Exception('Check failed: '.$response);
        }
        if ('STATUS' == substr($response, 0, 6)) {
            $rExplode = explode(' ', trim($response), 4);

            return CheckResult::newStatus(
                $rExplode[1],
                $rExplode[2],
                isset($rExplode[3]) ? $rExplode[3] : null
            );
        }
        if ('MAIL' == substr($response, 0, 4)) {
            $rExplode  = explode(' ', trim($response), 5);
            $numAttach = (int) $rExplode[3];

            return CheckResult::newMail(
                $rExplode[1],
                $rExplode[2],
                $numAttach,
                $rExplode[4]
            );
        }
        throw new \Exception('Check failed with unexpected response: '.$response);
    }

    /**
     * Clear the current outgoing message.
     * All content, header, and recipient information is discarded.
     *
     * @throws \Exception on unexpected failure
     */
    public function clear()
    {
        $response = $this->sendCommand('CLEAR');
        if ('OK' != $response) {
            throw new \Exception('Clear failed: '.$response);
        }
    }

    /**
     * Close the socket connection to the server.
     */
    public function close()
    {
        fclose($this->socket);
    }

    /**
     * Alias for acknowledgeMessage().
     *
     * @throws \Exception on unexpected failure
     */
    public function done()
    {
        $this->acknowledgeMessage();
    }

    /**
     * Logout currently authenticated user but keep connection open.
     *
     * @throws \Exception on unexpected failure
     */
    public function logout()
    {
        $response = $this->sendCommand('LOGOUT');
        if ('OK' != $response) {
            throw new \Exception('Logout failed: '.$response);
        }
    }

    /**
     * Retry sending of a previous outgoing message after receiving a failure
     * status notification for the message.
     *
     * @deprecated retry is disabled on server side by default
     *
     * @throws \Exception on unexpected failure
     */
    public function retry()
    {
        $response = $this->sendCommand('RETRY');
        if ('OK' != $response) {
            throw new \Exception('Retry failed: '.$response);
        }
    }

    /**
     * Search the directory.
     *
     * @param string $searchFilter the search filter, using LDAP filter syntax
     *
     * @throws \Exception on unexpected failure
     *
     * @return string[] an array of matching JSON-encoded directory entries
     */
    public function searchDirectory($searchFilter)
    {
        $response = $this->sendCommand('LOOKUP JSON '.$searchFilter);
        if ('OK' != $response) {
            throw new \Exception('Directory search failed: '.$response);
        }

        $numResults    = (int) ($this->readLine());
        $searchResults = [];
        while ($numResults-- > 0) {
            $searchResults[] = $this->readLine();
        }

        return $searchResults;
    }

    /**
     * Send the current outgoing message.
     *
     * @throws \Exception on unexpected failure
     *
     * @return SendResult[] one for each recipient
     */
    public function send()
    {
        $response = $this->sendCommand('SEND');
        if ('FAIL' == substr($response, 0, 4)) {
            throw new \Exception('Send failed: '.$response);
        }
        $output = [];
        while (null != $response && 'OK' != $response) {
            $rExplode = explode(' ', trim($response), 3);
            switch ($rExplode[0]) {
                case 'ERROR':
                    $output[] = new SendResult($rExplode[1], false, $rExplode[2]);
                    break;
                case 'QUEUED':
                    $output[] = new SendResult($rExplode[1], true, $rExplode[2]);
                    break;
                default: //unrecognized
                    throw new \Exception('Send failed with unexpected response: '.$response);
            }
            $response = $this->sendCommand('OK');
        }

        return $output;
    }

    /**
     * Set the client certificate for authentication to the phiMail server.
     *
     * @param string $filename   path to PEM file with private key and certificate
     * @param string $passphrase the passphrase protecting the private key
     *
     * @throws \Exception if client certificate cannot be set
     */
    public static function setClientCertificate($filename, $passphrase = '')
    {
        if ( ! isset(self::$context)) {
            self::$context = stream_context_create();
        }
        if ( ! isset($filename)) {
            return;
        }
        if ( ! stream_context_set_option(self::$context, 'ssl', 'passphrase', $passphrase) ||
            ! stream_context_set_option(self::$context, 'ssl', 'local_cert', $filename)) {
            throw new \Exception('Set Client Certificate failed.');
        }
    }

    /**
     * Set Final Delivery Notification request for the current outgoing message.
     * This function can be used to override the server default.
     *
     * @param bool $value true = request a final delivery (Dispatched) MDN
     *
     * @throws \Exception on unexpected failure
     */
    public function setDeliveryNotification($value)
    {
        $response = $this->sendCommand('SET FINAL '.($value ? '1' : '0'));
        if ('OK' != $response) {
            throw new \Exception('Set delivery notification failed: '.$response);
        }
    }

    /**
     * Set the trusted phiMail server SSL certificate. After this function is
     * called, connections to a phiMail server that does not present a
     * matching certificate will be refused.
     *
     * @param string $cert     path to trusted PEM encoded certificate file
     * @param mixed  $filename
     *
     * @throws \Exception if server certificate cannot be set
     */
    public static function setServerCertificate($filename)
    {
        if ( ! isset(self::$context)) {
            self::$context = stream_context_create();
        }
        if ( ! isset($filename) || '' === $filename) {
            throw new \Exception('Server Certificate filename is invalid.');
        }
        if ( ! is_readable($filename)) {
            throw new \Exception('Server Certificate file is not readable.');
        }
        if ( ! stream_context_set_option(self::$context, 'ssl', 'verify_peer', true) ||
            ! stream_context_set_option(self::$context, 'ssl', 'cafile', $filename)) {
            throw new \Exception('Set Server Certificate failed.');
        }
    }

    /**
     * Set Subject header on current outgoing message.
     * This function can be used to override the server default.
     *
     * @param string $data the desired value, or an empty string or null
     *                     value to unset the Subject header
     *
     * @throws \Exception on unexpected failure
     */
    public function setSubject($data)
    {
        $response = $this->sendCommand('SUBJECT'.$this->extraParam($data));
        if ('OK' != $response) {
            throw new \Exception('Set subject failed: '.$response);
        }
    }

    /**
     * Retrieve a content part from the current incoming message.
     *
     * @param int $messagePart the message part 0..n-1
     *
     * @throws \Exception on time-out reading message data
     *
     * @return ShowResult the requested content
     */
    public function show($messagePart)
    {
        $response = $this->sendCommand('SHOW '.$messagePart);
        if ('OK' != $response) {
            throw new \Exception('Show '.$messagePart.' failed: '.$response);
        }

        $headers  = [];
        $filename = null;
        if (0 == $messagePart) {
            //get headers for part == 0
            while (null != ($response = $this->readLine()) && '' != $response) {
                $headers[] = $response;
            }
        } else {
            //get filename for part > 0
            $filename = $this->readLine();
        }

        $mimeType  = $this->readLine();
        $length    = (int) ($this->readLine());
        $bytesLeft = $length;
        $buf       = '';
        while ($bytesLeft > 0) {
            $buf1      = fread($this->socket, $bytesLeft);
            $bytesRead = mb_strlen($buf1, '8bit');
            if ($bytesRead > 0) {
                $bytesLeft -= $bytesRead;
                $buf .= $buf1;
            }
            $info = stream_get_meta_data($this->socket);
            if ($info['timed_out']) {
                throw new \Exception('Show timed out receiving content.');
            }
        }

        $ai = [];
        if (0 == $messagePart) { //get attachment data
            $numAttach = (int) ($this->readLine());
            for ($n = 0; $n < $numAttach; ++$n) {
                $aFilename    = $this->readLine();
                $aMimeType    = $this->readLine();
                $aDescription = $this->readLine();
                $ai[$n]       = new AttachmentInfo($aFilename, $aMimeType, $aDescription);
            }
        }

        return new ShowResult(
            $messagePart,
            $headers,
            $filename,
            $mimeType,
            $length,
            $buf,
            $ai
        );
    }

    /**
     * Private helper function to add content to the current outgoing message.
     *
     * @param string     $dataBytes
     * @param string     $dataType
     * @param string     $filename
     * @param mixed|null $encoding
     *
     * @throws \Exception
     */
    private function addData($dataBytes, $dataType, $filename = null, $encoding = null)
    {
        if (null != $encoding && ! mb_detect_encoding($dataBytes, $encoding, true)) {
            $this->sendCommand('INFO ERR invalid character encoding.');
            $response = 'FAIL invalid character encoding.';
        } else {
            $response = $this->sendCommand($dataType.' '
                .mb_strlen($dataBytes, '8bit')
                .$this->extraParam($filename));
        }
        if ('BEGIN' != $response) {
            throw new \Exception('Add '.$dataType.' failed: '.$response);
        }
        @fwrite($this->socket, $dataBytes);
        @fflush($this->socket);
        $response = $this->readLine();
        if (null == $response || 'OK' != $response) {
            throw new \Exception('Add '.$dataType.' failed: '.(null == $response ? '' : $response));
        }
    }

    private function extraParam($s)
    {
        return null != $s && strlen($s) > 0
            ? ' '.$s
            : '';
    }

    /**
     * Read one newline-terminated line from server.
     *
     * @param int $len
     *
     * @return string the line of text sent from server, with the
     *                newline character removed
     */
    private function readLine($len = 2048)
    {
        $s = @fgets($this->socket, $len);
        if (false === $s) {
            if (feof($this->socket)) {
                throw new \Exception('Socket closed unexpectedly.');
            }
            throw new \Exception('Read timed out.');
        }

        return substr($s, 0, -1);
    }

    /**
     * Send command to server.
     *
     * @param string $command the command to send to the server
     *
     * @return string the response from the server
     */
    private function sendCommand($command)
    {
        if ( ! mb_detect_encoding($command, 'UTF-8', true)) {
            $this->sendCommand('INFO ERR invalid character encoding.');

            return 'FAIL invalid character encoding.';
        }
        if (false !== strpos($command, "\n") || false !== strpos($command, "\r")) {
            $this->sendCommand('INFO ERR illegal characters in command string.');

            return 'FAIL illegal characters in command string.';
        }
        @fwrite($this->socket, $command);
        @fwrite($this->socket, "\n");
        @fflush($this->socket);

        return $this->readLine();
    }
}
