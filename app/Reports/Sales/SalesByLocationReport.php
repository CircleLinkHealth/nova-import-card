<?php namespace App\Reports\Sales;
use App\User;
use Carbon\Carbon;

/**
 * Created by PhpStorm.
 * User: RohanM
 * Date: 10/10/16
 * Time: 1:09 PM
 */
class SalesByLocationReport
{

    protected $startDate;
    protected $endDate;
    protected $data;
    protected $reportee;
    protected $location;
    protected $program;

    public function __construct(User $requestor, Carbon $start, Carbon $end){

        $this->reportee = $requestor;
        $this->startDate = $start;
        $this->endDate = $end;

        $this->location = '';
        $this->program = '';

        $this->data = [];

        $this->program = $this->reportee->viewableProgramIds();
        $this->location = $this->reportee->locations();

    }


    public function handle(){

        $this->getReporteeLocationsAndPrograms();

        $this->collectSalesData();

        $this->formatSalesData();

        $this->generatePDF();

        return $this->data;

    }

    public function getReporteeLocationsAndPrograms(){





        return [$this->program, $this->location];

    }

    public function collectSalesData(){



    }

    public function formatSalesData(){



    }

    public function generatePDF(){



    }

}