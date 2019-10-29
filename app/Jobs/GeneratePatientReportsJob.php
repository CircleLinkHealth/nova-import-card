<?php

namespace App\Jobs;

use App\PersonalizedPreventionPlan;
use App\Services\GeneratePersonalizedPreventionPlanService;
use App\Services\GenerateProviderReportService;
use App\Services\PersonalizedPreventionPlanPrepareData;
use App\Services\ProviderReportService;
use App\User;
use Barryvdh\Snappy\PdfWrapper;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Redis;

class GeneratePatientReportsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $patientId;

    /**
     * Date to specify for which survey instances to generate Provider Report for.
     *
     * @var Carbon
     */
    protected $instanceYear;

    protected $currentDate;


    /**
     * Create a new job instance.
     *
     * @param $patientId
     * @param $instanceYear
     */
    public function __construct($patientId, $instanceYear)
    {
        $this->instanceYear = Carbon::parse($instanceYear);
        $this->patientId    = $patientId;
        $this->currentDate  = Carbon::now();

    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {
        //Retrieve User with data needed for reports
        $patient = User::with([
            'surveyInstances'     => function ($instance) {
                $instance->with(['survey', 'questions.type.questionTypeAnswers'])
                         ->forYear($this->instanceYear);
            },
            'answers'             => function ($answers) {
                $answers->whereHas('surveyInstance', function ($instance) {
                    $instance->forYear($this->instanceYear);
                });
            },
            'providerReports'     => function ($report) {
                $report->whereHas('hraSurveyInstance', function ($instance) {
                    $instance->forYear($this->instanceYear);
                })
                       ->whereHas('vitalsSurveyInstance', function ($instance) {
                           $instance->forYear($this->instanceYear);
                       });
            },
            'patientAWVSummaries' => function ($summary) {
                $summary->where('year', Carbon::now()->year);
            },
        ])
                       ->findOrFail($this->patientId);

        //Generate Reports
        $providerReport = (new GenerateProviderReportService($patient))->generateData();

        if ( ! $providerReport) {
            \Log::error("Something went wrong while generating Provider Report for patient with id:{$patient->id} ");

            //todo: send notification to slack? when command stops?
            return;
        }

        $pppReport = (new GeneratePersonalizedPreventionPlanService($patient))->generateData();

        if ( ! $pppReport) {
            \Log::error("Something went wrong while generating PPP for patient with id:{$patient->id} ");

            return;
        }

        //Create PDFs for reports and upload the to S3 Media
        $providerReportMedia = $this->createAndUploadPdfProviderReport($providerReport, $patient);

        if ( ! $providerReportMedia) {
            \Log::error("Something went wrong while uploading Provider Report for patient with id:{$patient->id} ");

            return;
        }

        Redis::publish('awv-patient-report-created',
            json_encode([
                'patient_id'      => $patient->id,
                'report_type'     => 'provider report',
                'report_media_id' => $providerReportMedia->id,
            ]));

        $pppMedia = $this->createAndUploadPdfPPP($pppReport, $patient);

        if ( ! $pppMedia) {
            \Log::error("Something went wrong while uploading PPP for patient with id:{$patient->id} ");

            return;
        }

        Redis::publish('awv-patient-report-created',
            json_encode([
                'patient_id'      => $patient->id,
                'report_type'     => 'ppp',
                'report_media_id' => $pppMedia->id,
            ]));

        //Update AWVSummaries
        $summary = $patient->patientAWVSummaries->first();

        if ($summary) {
            $summary->update([
                'is_billable' => true,
                'billable_at' => Carbon::now(),
            ]);
        }
    }

    /**
     * @param $providerReport
     * @param $patient
     *
     * @return bool
     * @throws \Exception
     */
    private function createAndUploadPdfProviderReport($providerReport, $patient)
    {
        $providerReportFormattedData = (new ProviderReportService())->formatReportDataForView($providerReport);

        /** @var PdfWrapper $pdf */
        $pdf = App::make('snappy.pdf.wrapper');
        $this->setPdfOptions($pdf);

        $pdf->loadView('providerReport.report', [
            'reportData' => $providerReportFormattedData,
            'patient'    => $patient,
            'isPdf'      => true,
        ]);

        $path = storage_path("provider_report_{$patient->id}_{$this->currentDate->toDateTimeString()}.pdf");

        $saved = file_put_contents($path, $pdf->output());

        if ( ! $saved) {
            return false;
        }

        return $patient->addMedia($path)
                       ->withCustomProperties(['doc_type' => 'Provider Report'])
                       ->toMediaCollection('patient-care-documents');
    }

    private function createAndUploadPdfPPP(PersonalizedPreventionPlan $ppp, User $patient)
    {
        $personalizedHealthAdvices = (new PersonalizedPreventionPlanPrepareData())->prepareRecommendations($ppp);

        /** @var PdfWrapper $pdf */
        $pdf = App::make('snappy.pdf.wrapper');
        $this->setPdfOptions($pdf);

        $pdf->loadView('personalizedPreventionPlan', [
            'patientPppData'            => $ppp,
            'patient'                   => $patient,
            'personalizedHealthAdvices' => $personalizedHealthAdvices,
            'isPdf'                     => true,
        ]);

        $path = storage_path("ppp_report_{$patient->id}_{$this->currentDate->toDateTimeString()}.pdf");

        $saved = file_put_contents($path, $pdf->output());

        if ( ! $saved) {
            return false;
        }

        return $patient->addMedia($path)
                       ->withCustomProperties(['doc_type' => 'PPP'])
                       ->toMediaCollection('patient-care-documents');
    }

    private function setPdfOptions(PdfWrapper $pdf)
    {
        $pdf->setOption('lowquality', false);
        $pdf->setOption('disable-smart-shrinking', true);
        $pdf->setOption('margin-top', 8);
        $pdf->setOption('margin-bottom', 8);
        $pdf->setOption('margin-left', 5);
        $pdf->setOption('margin-right', 5);
        $pdf->setOption('footer-right', '[page] of [topage]');
    }
}
