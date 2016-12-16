<?php
/**
 * Created by PhpStorm.
 * User: RohanM
 * Date: 9/17/16
 * Time: 1:58 PM
 */

namespace App\Contracts;

interface CallHandler
{

    //exec function
    public function handle();

    //calculate how much time to wait before next call
    public function getPatientOffset($ccmTime, $week);


}