<?php namespace App\Reports\Sales;

use App\PatientInfo;
use App\Program;
use Carbon\Carbon;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;


/**
 * Created by PhpStorm.
 * User: RohanM
 * Date: 10/10/16
 * Time: 1:09 PM
 */
class SalesByLocationReport
{

    protected $startDate;
    protected $startDateString;
    protected $endDate;
    protected $data;
    protected $providers;
    protected $location;
    protected $program;
    protected $currentMonth;
    protected $lastMonth;
    protected $reportLastMonthWithDifference;

    public function __construct(Program $forProgram, Carbon $start, Carbon $end, $withLastMonth){

//        dd($forProgram->getProviders($forProgram->blog_id));

        $this->startDate = $start;
        $this->startDateString = Carbon::parse($start)->toDateString();
        $this->endDate = $end;

//        $this->providers = $this->program->getProviders($this->program->blog_id);

        $this->location = '';
        $this->program = '';

        $this->diff = [];

        $this->program = $forProgram;

        $this->reportLastMonthWithDifference = $withLastMonth;

    }


    public function handle(){

        $this->patientsForProgram();

//        $this->getStatsByProvider();

        $this->formatSalesData();

        return $this->generatePdf();

    }

    public function patientsForProgram(){

        $startDate = $this->startDate->toDateString();

        $this->currentMonth = $this->program->enrollmentByProgram(Carbon::parse($this->startDateString),
                                                                  Carbon::parse($this->endDate));

        $this->currentMonth['month'] = Carbon::parse($this->startDateString)->format('F Y');

        if($this->reportLastMonthWithDifference){

            $this->lastMonth = $this->program->enrollmentByProgram(
                Carbon::parse($this->startDateString)->subMonth()->startOfMonth(),
                Carbon::parse($this->startDateString)->subMonth()->endOfMonth()
                                                    );

            $this->lastMonth['month'] = Carbon::parse($this->startDateString)->subMonth()->endOfMonth()->format('F Y');

            $this->calculateMonthOverMonthChanges();

        }
    }

    public function getStatsByProvider(){

        foreach ($this->providers as $provider){

            $patients = PatientInfo::whereHas('user', function ($q){

                $q->where('program_id', $this->blog_id);

            })
                ->whereNotNull('ccm_status')
                ->where('ccm_status')
                ->get();


        }

    }

    public function calculateMonthOverMonthChanges(){

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

        //Enrolled Patients
        if($this->currentMonth['enrolled'] != 0 && $this->lastMonth['paused'] != 0 ){

            $this->diff['enrolled']['diff'] = $this->currentMonth['enrolled'] - $this->lastMonth['enrolled'];
            $this->diff['enrolled']['percent'] = round((($this->diff['enrolled']['diff'] / $this->currentMonth['enrolled']) * 100), 2) . '%';

        } else {

            $this->diff['enrolled']['diff'] = 'N/A';
            $this->diff['enrolled']['percent'] = 'N/A';

        }


    }

    public function formatSalesData(){

        $this->data = [
            'current' => $this->currentMonth,
            'last' => $this->lastMonth,
            'data' => $this->diff,
            'program_name' => $this->program->display_name,
            't0start' => $this->startDateString,
            't0end' => $this->endDate->toDateString(),
            't1start' => Carbon::parse($this->startDateString)->subMonth()->startOfMonth()->toDateString(),
            't1end' => Carbon::parse($this->startDateString)->subMonth()->endOfMonth()->toDateString()

        ];

    }

    public function generatePdf(){

        $pdf = PDF::loadView('sales.make', ['data' => $this->data]);

        $name = trim($this->program->name).'-'.Carbon::now()->toDateString();

        $pdf->save( storage_path("download/$name.pdf"), true );

        return $name.'.pdf';

    }

//
//    public function mail(){
//
//        $nurse = $this->nurse;
//
//        $fileName = $this->generatePdf();
//
//        Mail::send('billing.nurse.invoice', $this->formattedItemizedActivities, function ($m) use ($nurse, $fileName) {
//
//            $m->from('billing@circlelinkhealth.com', 'CircleLink Health');
//
//            $m->attach(storage_path("download/$fileName"));
//
//            $m->to($nurse->user->user_email, $nurse->user->fullName)
//                ->subject('New Invoice from CircleLink Health');
//        });
//
////        MailLog::create([
////            'sender_email' => $sender->user_email,
////            'receiver_email' => $receiver->user_email,
////            'body' => $body,
////            'subject' => $email_subject,
////            'type' => 'note',
////            'sender_cpm_id' => $sender->ID,
////            'receiver_cpm_id' => $receiver->ID,
////            'created_at' => $note->created_at,
////            'note_id' => $note->id
////        ]);
//
//    }

}