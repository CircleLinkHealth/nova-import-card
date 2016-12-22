<?php namespace App\Reports\Sales\Location;

use App\PatientInfo;
use App\Practice;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


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
    protected $enrollmentCount = [];

    public function __construct(
        Practice $forProgram,
        Carbon $start,
        Carbon $end,
        $withLastMonth = true
    ) {


        $this->startDate = $start;
        $this->startDateString = Carbon::parse($start)->toDateString();
        $this->endDate = $end;

        $this->location = '';
        $this->program = '';

        $this->diff = [];

        $this->program = $forProgram;

        $this->reportLastMonthWithDifference = true;//$withLastMonth;

    }


    public function handle(){

        $this->patientsForProgram();

//        $this->getStatsByProvider();

        $this->getEnrollmentNumbers();

        $this->formatSalesData();

        return $this->generatePdf();

    }

    public function introParagraph(){

    }

    public function patientsForProgram(){
        
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

//        //Enrolled Patients
//        if($this->currentMonth['total enrolled'] != 0 && $this->lastMonth['total enrolled'] != 0 ){
//
//            $this->diff['total enrolled']['diff'] = $this->currentMonth['total enrolled'] - $this->lastMonth['total enrolled'];
//            $this->diff['total enrolled']['percent'] = round((($this->diff['total enrolled']['diff'] / $this->currentMonth['total enrolled']) * 100), 2) . '%';
//
//        } else {
//
//            $this->diff['total enrolled']['diff'] = 'N/A';
//            $this->diff['total enrolled']['percent'] = 'N/A';
//
//        }

        //Enrolled Patients
        if($this->currentMonth['added'] != 0 && $this->lastMonth['added'] != 0 ){

            $this->diff['added']['diff'] = $this->currentMonth['added'] - $this->lastMonth['added'];
            $this->diff['added']['percent'] = round((($this->diff['added']['diff'] / $this->currentMonth['added']) * 100), 2) . '%';

        } else {

            $this->diff['added']['diff'] = 'N/A';
            $this->diff['added']['percent'] = 'N/A';

        }


    }

    public function getEnrollmentNumbers()
    {
        $this->enrollmentCount = PatientInfo::whereHas('user', function ($q) {

            $q->where('program_id', $this->program->id);

        })
            ->whereNotNull('ccm_status')
            ->select(DB::raw('count(ccm_status) as total, ccm_status'))
            ->groupBy('ccm_status')
            ->get()
            ->toArray();

        return $this->enrollmentCount;
    }

    public function formatSalesData(){

        $this->data = [
            'current' => $this->currentMonth,
            'last' => $this->lastMonth,
            'diff' => $this->diff,
            'program_name' => $this->program->display_name,
            'withMOM' => $this->reportLastMonthWithDifference,
            't0start' => Carbon::parse($this->startDateString)->format('F Y'),
            'count' => $this->enrollmentCount,
            't1start' => Carbon::parse($this->startDateString)->subMonth()->startOfMonth()->format('F Y'),
//            't1end' => Carbon::parse($this->startDateString)->subMonth()->endOfMonth()->toFormattedDateString()

        ];


    }

    public function generatePdf(){

        $pdf = PDF::loadView('sales.by-location.make', ['data' => $this->data]);

        $name = trim($this->program->name).'-'.Carbon::now()->toDateString();

        $pdf->save( storage_path("download/$name.pdf"), true );

        return $name.'.pdf';

    }


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
//            $m->to($nurse->user->email, $nurse->user->fullName)
//                ->subject('New Invoice from CircleLink Health');
//        });
//
////        MailLog::create([
////            'sender_email' => $sender->email,
////            'receiver_email' => $receiver->email,
////            'body' => $body,
////            'subject' => $email_subject,
////            'type' => 'note',
////            'sender_cpm_id' => $sender->id,
////            'receiver_cpm_id' => $receiver->id,
////            'created_at' => $note->created_at,
////            'note_id' => $note->id
////        ]);
//
//    }

}