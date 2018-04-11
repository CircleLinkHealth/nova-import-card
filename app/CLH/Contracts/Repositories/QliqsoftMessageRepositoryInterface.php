<?php namespace App\CLH\Contracts\Repositories;

interface QliqsoftMessageRepositoryInterface
{

    public function saveResponseToDb($args);

    public function getConversationId($to);
}
