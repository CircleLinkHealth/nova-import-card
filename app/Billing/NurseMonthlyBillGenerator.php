<?php

namespace App\Billing;

use App\NurseInfo;
use App\PageTimer;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class NurseMonthlyBillGenerator
{

    //initializations
    protected $nurse;
    protected $nurseName;
    protected $startDate;
    protected $endDate;

    //manual time adds
    protected $hasAddedTime = false;
    protected $addDuration;
    protected $addNotes;

    //Billing Results
    protected $formattedItemizedActivities;
    protected $payable;
    protected $systemTime;

    public function __construct(NurseInfo $newNurse,
                                Carbon $billingDateStart,
                                Carbon $billingDateEnd,
                                $manualTimeAdd = 0,
                                $notes = ''){

        $this->nurse = $newNurse;
        $this->nurseName = $newNurse->user->last_name;
        $this->startDate = $billingDateStart;
        $this->endDate = $billingDateEnd;
        $this->addDuration = $manualTimeAdd;
        $this->addNotes = $notes;

        if($this->addDuration != 0){ $this->hasAddedTime = true; }

    }

    public function handle(){

        $this->getSystemTimeForNurse();

        $this->getItemizedActivities();

        $this->formatItemizedActivities();

        return $this->generatePdf();

    }

    public function email(){

        $this->getSystemTimeForNurse();

        $this->getItemizedActivities();

        $this->formatItemizedActivities();

        $this->generatePdf();

        $this->mail();

    }

    public function getSystemTimeForNurse(){


        $this->systemTime = PageTimer::where('provider_id', $this->nurse->user_id)
            ->where(function ($q){
                $q->where('updated_at', '>=' , $this->startDate->toDateString())
                    ->where('updated_at', '<=' , $this->endDate->toDateString());
            })
            ->sum('duration');

        //handle any extra time
        if($this->hasAddedTime){ $this->systemTime += $this->addDuration; }

        $this->payable = round($this->systemTime / 3600, 1) * $this->nurse->hourly_rate;

        return '$'.$this->payable;

    }

    public function getItemizedActivities(){

        $data = [];

        $activities = PageTimer::where('provider_id', $this->nurse->user_id)
            ->where(function ($q){
                $q->where('updated_at', '>=' , $this->startDate->toDateString())
                    ->where('updated_at', '<=' , $this->endDate->toDateString());
            })
            ->get();


        $activities = $activities->groupBy(function($q) {
            return Carbon::parse($q->created_at)->format('d'); // grouping by days
        });

        foreach ($activities as $activity){

            $data[Carbon::parse($activity[0]['created_at'])->toDateString()] = $activity->sum('duration');

        };

        return $data;

    }

    public function formatItemizedActivities(){

        $activities = $this->getItemizedActivities();

        $data = [];

        $dayCounterCarbon = Carbon::parse($this->startDate->toDateString());
        $dayCounterDate = $dayCounterCarbon->toDateString();

        while($this->endDate->toDateString() >= $dayCounterDate){

            if(isset($activities[$dayCounterDate])){

                $data[$dayCounterDate] = [

                    'Date'    => $dayCounterDate,
                    'Minutes' => round($activities[$dayCounterDate] / 60, 2),
                    'Hours'   => round($activities[$dayCounterDate] / 3600, 1)

                ];

            } else {

                $data[$dayCounterDate] = [

                    'Date'    => $dayCounterDate,
                    'Minutes' => 0,
                    'Hours'   => 0

                ];

            }

            $dayCounterCarbon->addDay();
            $dayCounterDate = $dayCounterCarbon->toDateString();

        }

        //handle any extra time
        if($this->hasAddedTime){

            $data['Others'] = [

                'Date'    => $this->addNotes,
                'Minutes' => $this->addDuration,
                'Hours'   => round($this->addDuration / 60, 1)

            ];

        }

        $this->formattedItemizedActivities = $data;

        return [
            //days data
            'data' => $data,
            'hasAddedTime' => $this->hasAddedTime,
            'manual_time' => round($this->addDuration / 60, 1) . 'Hours',
            'manual_time_notes' => $this->addNotes,
            'manual_time_amount' => round($this->addDuration / 60, 1) * $this->nurse->hourly_rate,

            //headers
            'nurse_billable_time' => round($this->systemTime / 3600, 1),
            'total_billable_amount' => '$'.$this->payable,
            'total_billable_rate' => '$'.$this->nurse->hourly_rate,
            'nurse_name' => $this->nurse->user->fullName,

            //range
            'date_start' => $this->startDate->format('jS M, Y'),
            'date_end' => $this->endDate->format('jS M, Y')

        ];

    }

    public function generatePdf(){

        $data = $this->formatItemizedActivities();

        $pdf = PDF::loadView('billing.nurse.invoice', $data);

        $name = trim(($this->nurseName).'-'.trim(Carbon::now()->toDateString()));

        $pdf->save( base_path( "storage/pdfs/invoices/$name.pdf" ), true );

        return asset("/storage/pdfs/invoices/$name.pdf");

    }

    public function mail(){

        $nurse = $this->nurse;

        Mail::send('billing.nurse.invoice', ['data' => $this->formatItemizedActivities()], function ($m) use ($nurse) {

            $m->from('billing@circlelinkhealth.com', 'CircleLink Health');

            $m->to('rohstar@gmail.com', $nurse->user->last_name)
                ->subject('New Invoice from CircleLink Health');

            $m->attach($this->generatePdf());

//            $m->to($nurse->user->user_email, $nurse->user->fullName)
//                ->subject('New Invoice from CircleLink Health');
        });

//        MailLog::create([
//            'sender_email' => $sender->user_email,
//            'receiver_email' => $receiver->user_email,
//            'body' => $body,
//            'subject' => $email_subject,
//            'type' => 'note',
//            'sender_cpm_id' => $sender->ID,
//            'receiver_cpm_id' => $receiver->ID,
//            'created_at' => $note->created_at,
//            'note_id' => $note->id
//        ]);

    }

}