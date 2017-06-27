<?php namespace App\Reports;
use App\Note;
use App\Patient;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;

/**
 * Created by PhpStorm.
 * User: RohanM
 * Date: 1/6/17
 * Time: 10:10 AM
 */
class PatientDailyAuditReport
{

    protected $patient;
    protected $day;

    protected $data = [];

    public function __construct(Patient $patient, Carbon $forMonth)
    {

        $this->patient = $patient;
        $this->forMonth = $forMonth;

    }

    public function renderData() : array {

        $this->data['name'] = $this->patient->user->fullName;
        $this->data['month'] = $this->forMonth->format('F, Y');
        $this->data['provider'] = $this->patient->user->billingProviderName;
        $this->data['totalCCM'] = $this->patient->getCurrentMonthCCMTimeAttribute();

        $activities = DB::table('lv_activities')
            ->select(DB::raw('DATE(performed_at) as day, type, duration'))
            ->whereBetween('created_at', [
                $this->forMonth->startOfMonth()->toDateTimeString(),
                $this->forMonth->endOfMonth()->toDateTimeString()
            ])
            ->where('patient_id', $this->patient->user_id)
            ->orderBy('created_at', 'desc')
            ->get();

        $activities = $activities->groupBy('day');
        $this->data['daily'] = [];

        foreach ( $activities as $date => $value){

            $value = collect($value);

            $this->data['daily'][$date]['activities'] = $value->implode('type', ', ');
            $dailyDuration = $value->sum('duration');
            $this->data['daily'][$date]['ccm'] = secondsToMMSS($dailyDuration);

            $notes = Note
                ::wherePatientId($this->patient->user_id)
                ->whereBetween('created_at', [
                    Carbon::parse($date)->startOfDay()->toDateTimeString(),
                    Carbon::parse($date)->endOfDay()->toDateTimeString(),
                ])
                ->get();

            $this->data['daily'][$date]['notes'] = [];

            foreach ($notes as $note){

                $time = Carbon::parse($note->created_at)->format("g:i:s A");
                $performer = User::find($note->author_id)->fullName;
                $this->data['daily'][$date]['notes'][$note->id]['performer'] = $performer;
                $this->data['daily'][$date]['notes'][$note->id]['time'] = $time;
                $this->data['daily'][$date]['notes'][$note->id]['body'] = $note->body;

            }

        }

        return $this->data;

    }

    public function renderPDF() {

        $name = $this->patient->user->last_name . '-' . Carbon::now()->timestamp;

        $this->renderData();

        $pdf = PDF::loadView('wpUsers.patient.audit', ['data' => $this->data]);

        $pdf->save(storage_path("download/$name.pdf"), true);

        return "/$name.pdf";
    }

}