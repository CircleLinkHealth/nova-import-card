<?php

namespace App\Jobs;

use App\Services\GenerateProviderReportService;
use App\Services\GeneratePersonalizedPreventionPlanService;
use App\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GeneratePatientReports implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $patientId;

    /**
     * Date to specify for which survey instances to generate Provider Report for.
     *
     * @var Carbon
     */
    protected $date;



    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($patientId, $date)
    {
        $this->date = Carbon::parse($date);
        $this->patientId = $patientId;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $patient = User::with([
            'surveyInstances' => function ($instance) {
                $instance->with(['survey', 'questions.type.questionTypeAnswers'])
                         ->forDate($this->date);
            },
            'answers'         => function ($answers) {
                $answers->whereHas('surveyInstance', function ($instance) {
                    $instance->forDate($this->date);
                });
            },
            'providerReports' => function ($report) {
                $report->whereHas('hraSurveyInstance', function ($instance) {
                    $instance->forDate($this->date);
                })
                       ->whereHas('vitalsSurveyInstance', function ($instance) {
                           $instance->forDate($this->date);
                       });
            },
            'patientAWVSummaries' => function ($summary) {
                $summary->where('month_year', Carbon::now()->startOfMonth());
            }
        ])
                             ->findOrFail($this->patientId);

        $providerReport = (new GenerateProviderReportService($patient, $this->date))->generateData();

        $pppReport = (new GeneratePersonalizedPreventionPlanService($patient))->generateData();


        //create Pdfs
        //upload docs to S3
//        $patient->addMedia($providerReport)
//                ->withCustomProperties(['doc_type' => 'Provider Report'])
//                ->toMediaCollection('patient-care-documents');
//
//        $patient->addMedia($pppReport)
//                ->withCustomProperties(['doc_type' => 'PPP'])
//                ->toMediaCollection('patient-care-documents');

        //slack/notify something/someone
        $billingProvider = $patient->billingProviderUser();
        if ($billingProvider){
            $billingProvider->notify();
        }

        $summary = $patient->patientAWVSummaries->first();

        if ($summary){
            $summary->update([
                'is_billable' => true,
                'completed_at' => Carbon::now()
            ]);
        }
    }
}
