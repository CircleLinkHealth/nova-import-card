<?php


namespace CircleLinkHealth\SharedModels\Exceptions;


class CallBackMissingCallerIdException extends \Exception
{
    public function __construct()
    {
        parent::__construct('Inbound Callback is missing [Clr Id]. It cannot be processed.');
    }
}