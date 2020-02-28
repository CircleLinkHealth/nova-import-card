<?php


namespace App\Traits;


trait ValidatesDates
{
    public function isValidDate(string $date = null)
    {
        return \Validator::make(['date' => $date], ['date' => 'required|date'])->passes();
    }
}