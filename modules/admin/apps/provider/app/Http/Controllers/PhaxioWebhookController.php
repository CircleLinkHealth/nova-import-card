<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\FaxLog;
use App\Http\Requests\PhaxioWebhookRequest;
use CircleLinkHealth\Core\Entities\DatabaseNotification;
use CircleLinkHealth\Core\Jobs\PhaxioNotificationStatusUpdateJob;

class PhaxioWebhookController extends Controller
{
    public function onFaxSent(PhaxioWebhookRequest $request)
    {
        $fax       = json_decode($request->input('fax'), true);
        $eventType = $request->input('event_type');

        $log = FaxLog::create(
            [
                'fax_id'     => $fax['id'],
                'event_type' => $eventType,
                'status'     => $fax['status'],
                'direction'  => $fax['direction'],
                'response'   => $fax,
            ]
        );

        if (array_key_exists('tags', $fax)
            && is_array($fax['tags'])
            && array_key_exists('notification_id', $fax['tags'])
            && $notification = DatabaseNotification::find($fax['tags']['notification_id'])) {
            PhaxioNotificationStatusUpdateJob::dispatch($notification, $log);
        }

        return $this->ok();
    }

    /*
     *
     *
     * @param string $token The callback token that signed the signature. Obtainable from https://www.phaxio.com/apiSettings/callbacks
     * @param string $url The full URL that was called by Phaxio, including the query string
     * @param mixed $postParameters An associative array of the POST parameters
     * @param string $signature The X-Phaxio-Signature HTTP header value
     * @return boolean
     */
//    public function isValidCallbackRequest($token, $url = null, $postParameters = null, $uploadedFiles = null, $signature = null){
//        if (!$url) {
//            $url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
//        }
//
//        if (!$postParameters){
//            $postParameters = $_REQUEST;
//        }
//
//        if (!$uploadedFiles){
//            $uploadedFiles = $_FILES;
//        }
//
//        if (!$signature){
//            $signature = $_SERVER['HTTP_X_PHAXIO_SIGNATURE'];
//        }
//
//        // sort the array by keys
//        ksort($postParameters);
//
//        // append the data array to the url string, with no delimiters
//        foreach ($postParameters as $key => $value) {
//            $url .= $key . $value;
//        }
//
//        foreach ($uploadedFiles as $key => $value) {
//            $url .= $key . sha1_file($value['tmp_name']);
//        }
//
//        $hmac = hash_hmac("sha1", $url, $token);
//        return $signature == $hmac;
//    }
}
