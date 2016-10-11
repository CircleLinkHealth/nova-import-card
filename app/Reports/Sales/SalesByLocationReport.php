<?php namespace App\Reports\Sales;
use App\PatientInfo;
use App\Program;
use App\User;
use Carbon\Carbon;
use phpDocumentor\Reflection\Types\Boolean;

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
    protected $location;
    protected $program;
    protected $currentMonth;
    protected $lastMonth;
    protected $reportLastMonthWithDifference;

    public function __construct(Program $forProgram, Carbon $start, Carbon $end, $withLastMonth){

        $this->startDate = $start;
        $this->endDate = $end;

        $this->location = '';
        $this->program = '';

        $this->currentMonth = [

            'withdrawn' => 0,
            'paused' => 0,
            'enrolled' => 0

        ];

        $this->lastMonth = [

            'withdrawn' => 0,
            'paused' => 0,
            'enrolled' => 0

        ];

        $this->diff = [];


        $this->program = $forProgram;

        $this->reportLastMonthWithDifference = $withLastMonth;

    }


    public function handle(){

        return $this->patientsForProgram();
//
//        $this->collectSalesData();
//
//        $this->formatSalesData();
//
//        $this->generatePDF();

//        return $this->data;

    }

    public function patientsForProgram(){

        $this->currentMonth = $this->program->enrollmentByProgram($this->startDate, $this->endDate);

        if($this->reportLastMonthWithDifference){

            $this->lastMonth = $this->program->enrollmentByProgram($this->startDate->subMonth(), $this->endDate->subMonth());

            $this->calculateDifferences();

        }

        return [$this->currentMonth, $this->lastMonth, $this->diff];

    }

    public function calculateDifferences(){

        //Withdrawn Patients

        if($this->currentMonth['withdrawn'] != 0 && $this->lastMonth['withdrawn'] != 0 ){

            $temp = $this->currentMonth['withdrawn'] - $this->lastMonth['withdrawn'];
            $this->diff['withdrawn']['diff'] = $temp > 0 ? '+' . $temp : $temp;
            $this->diff['withdrawn']['percent'] = round((($this->diff['withdrawn']['diff'] / $this->currentMonth['withdrawn']) * 100), 2) . '%';

        } else {

            $this->diff['withdrawn']['diff'] = 'N/A';
            $this->diff['withdrawn']['percent'] = 'N/A';

        }

        //Paused Patients

        if($this->currentMonth['paused'] != 0 && $this->lastMonth['paused'] != 0 ){

            $this->diff['paused']['diff'] = $this->currentMonth['paused'] - $this->lastMonth['paused'];
            $this->diff['paused']['percent'] = round((($this->diff['paused']['diff'] / $this->currentMonth['paused']) * 100), 2) . '%';

        } else {

            $this->diff['paused']['diff'] = 'N/A';
            $this->diff['paused']['percent'] = 'N/A';

        }


    }

    public function formatSalesData(){



    }

    public function generatePDF(){



    }

}