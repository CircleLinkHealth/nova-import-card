<?php


namespace App\Contracts;


interface SurveyLoginInterface
{
    public function getLoginData($request, $userId);
}
