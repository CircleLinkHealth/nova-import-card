<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\FaxLog;
use Illuminate\Http\Request;

class PhaxioWebhookController extends Controller
{
    public function onFaxSent(Request $request)
    {
        $fax       = $request->input('fax');
        $eventType = $request->input('event_type');

        $log = FaxLog::create([
            'fax_id'    => $fax['id'],
            'status'    => $eventType,
            'direction' => $fax['direction'],
            'response'  => $fax,
        ]);

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
