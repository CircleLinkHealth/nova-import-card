<?php namespace App\Billing\NurseInvoices;

use App\Nurse;
use Carbon\Carbon;

/**
 * Created by PhpStorm.
 * User: RohanM
 * Date: 1/10/17
 * Time: 3:06 PM
 */
abstract class NurseInvoice
{

    //initializations
    protected $nurse;
    protected $startDate;
    protected $endDate;

    public function __construct(Nurse $nurse, Carbon $start, Carbon $end)
    {



    }

}