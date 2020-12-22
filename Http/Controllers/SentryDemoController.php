<?php


namespace CircleLinkHealth\Customer\Http\Controllers;


class SentryDemoController
{
    public function throw() {
        throw new \Exception('Test exception from '.app()->environment());
    } 
}