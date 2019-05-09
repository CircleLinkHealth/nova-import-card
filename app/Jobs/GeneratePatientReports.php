<?php

namespace App\Jobs;

use App\Services\GeneratePersonalizedPreventionPlanService;
use App\Services\GenerateProviderReportService;
use App\Services\GetSurveyAnswersForEvaluation;
use App\Services\PersonalizedPreventionPlanPrepareData;
use App\Services\ProviderReportService;
use App\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

class GeneratePatientReports implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $patientId;

    /**
     * Date to specify for which survey instances to generate Provider Report for.
     *
     * @var Carbon
     */
    protected $surveyInstanceStartDate;

    protected $currentDate;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($patientId, $surveyInstanceStartDate)
    {
        $this->surveyInstanceStartDate = Carbon::parse($surveyInstanceStartDate);
        $this->patientId               = $patientId;
        $this->currentDate             = Carbon::now();

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $patient = User::with([
            'surveyInstances'     => function ($instance) {
                $instance->with(['survey', 'questions.type.questionTypeAnswers'])
                         ->forDate($this->surveyInstanceStartDate);
            },
            'answers'             => function ($answers) {
                $answers->whereHas('surveyInstance', function ($instance) {
                    $instance->forDate($this->date);
                });
            },
            'providerReports'     => function ($report) {
                $report->whereHas('hraSurveyInstance', function ($instance) {
                    $instance->forDate($this->date);
                })
                       ->whereHas('vitalsSurveyInstance', function ($instance) {
                           $instance->forDate($this->date);
                       });
            },
            'patientAWVSummaries' => function ($summary) {
                $summary->where('month_year', Carbon::now()->startOfMonth());
            },
        ])
                       ->findOrFail($this->patientId);

        $providerReport = (new GenerateProviderReportService($patient))->generateData();

        $pppReport = (new GeneratePersonalizedPreventionPlanService($patient))->generateData();


        $this->uploadProviderReport($providerReport, $patient);

        $this->uploadPPP($pppReport, $patient);


        $billingProvider = $patient->billingProviderUser();

        if ($billingProvider) {
//            $billingProvider->notify();
        }

        $summary = $patient->patientAWVSummaries->first();

        if ($summary) {
            $summary->update([
                'is_billable'  => true,
                'completed_at' => Carbon::now(),
            ]);
        }
    }

    private function uploadProviderReport($providerReport, $patient)
    {

        $providerReportFormattedData = (new ProviderReportService())->formatReportDataForView($providerReport);

        $pdf = App::make('dompdf.wrapper');
        $pdf->loadView('providerReport.report', ['reportData' => $providerReportFormattedData, 'patient' => $patient]);


        $path = storage_path("provider_report_{$patient->id}_{$this->currentDate->toDateTimeString()}.pdf");

        $saved = file_put_contents($path, $pdf->output());

        $patient->addMedia($path)
                ->withCustomProperties(['doc_type' => 'Provider Report'])
                ->toMediaCollection('patient-care-documents');
    }

    private function uploadPPP($ppp, $patient)
    {

        $pppFormattedData = (new PersonalizedPreventionPlanPrepareData(new GetSurveyAnswersForEvaluation()))->prepareRecommendations($ppp);

        $recommendationTasks = [];
        foreach ($pppFormattedData['recommendation_tasks'] as $tasks) {
            $recommendationTasks[$tasks['title']] = $tasks;
        }

        $pdf = App::make('dompdf.wrapper');
        $pdf->loadView('personalizedPreventionPlan', [
            'reportData'          => $pppFormattedData,
            'age'                 => $patient->getAge(),
            'recommendationTasks' => $recommendationTasks,
        ]);


        $path = storage_path("ppp_report_{$patient->id}_{$this->currentDate->toDateTimeString()}.pdf");

        $saved = file_put_contents($path, $pdf->output());

        $patient->addMedia($path)
                ->withCustomProperties(['doc_type' => 'PPP'])
                ->toMediaCollection('patient-care-documents');
    }
}
