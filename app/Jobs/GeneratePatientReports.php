<?php

namespace App\Jobs;

use App\Notifications\SendReport;
use App\Services\GeneratePersonalizedPreventionPlanService;
use App\Services\GenerateProviderReportService;
use App\Services\PersonalizedPreventionPlanPrepareData;
use App\Services\ProviderReportService;
use App\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Notifications\Channels\MailChannel;
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
        //Retrieve User with data needed for reports
        $patient = User::with([
            'surveyInstances'     => function ($instance) {
                $instance->with(['survey', 'questions.type.questionTypeAnswers'])
                         ->forDate($this->surveyInstanceStartDate);
            },
            'answers'             => function ($answers) {
                $answers->whereHas('surveyInstance', function ($instance) {
                    $instance->forDate($this->surveyInstanceStartDate);
                });
            },
            'providerReports'     => function ($report) {
                $report->whereHas('hraSurveyInstance', function ($instance) {
                    $instance->forDate($this->surveyInstanceStartDate);
                })
                       ->whereHas('vitalsSurveyInstance', function ($instance) {
                           $instance->forDate($this->surveyInstanceStartDate);
                       });
            },
            'patientAWVSummaries' => function ($summary) {
                $summary->where('month_year', Carbon::now()->startOfMonth());
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

        if ( ! $providerReport) {
            \Log::error("Something went wrong while generating PPP for patient with id:{$patient->id} ");

            return;
        }

        //Create PDFs for reports and upload the to S3 Media
        $providerReportUploaded = $this->createAndUploadPdfProviderReport($providerReport, $patient);

        if ( ! $providerReportUploaded) {
            \Log::error("Something went wrong while uploading Provider Report for patient with id:{$patient->id} ");

            return;
        }

        $pppUploaded = $this->createAndUploadPdfPPP($pppReport, $patient);

        if ( ! $pppUploaded) {
            \Log::error("Something went wrong while uploading PPP for patient with id:{$patient->id} ");

            return;
        }


        //Notify Billing Provider
        $billingProvider = $patient->billingProviderUser();

        //TODO: Practice breaks on instantiation because we need to move App\Traits\HasChargeableServices from CPM to Customer
        try {
            $settings = $patient->practiceSettings();

            $channels = [];

            if ($settings) {
                if ($settings->dm_awv_reports) {
                    //todo: when Notifications Module is finished
//                $channels[] = DirectMailChannel::class;
                }
                if ($settings->efax_awv_reports) {
                    //todo: when Notifications Module is finished
//                $channels[] = EfaxChannel::class;
                }
                if ($settings->email_awv_reports) {
                    $channels[] = MailChannel::class;
                }
            }
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            $channels[] = MailChannel::class;
        }


        if ($billingProvider) {
            $billingProvider->notify(new SendReport($patient, $providerReport, 'Provider Report', $channels));
            $billingProvider->notify(new SendReport($patient, $pppReport, 'PPP', $channels));
        }


        //Update AWVSummaries
        //We are updating month_year so that the patient is billable for the month that the surveys have been completed
        $summary = $patient->patientAWVSummaries->first();

        if ($summary) {
            $summary->update([
                'is_billable'  => true,
                'completed_at' => Carbon::now(),
                'month_year'   => Carbon::now()->startOfMonth()->toDateString(),
            ]);
        }
    }

    private function createAndUploadPdfProviderReport($providerReport, $patient)
    {

        $providerReportFormattedData = (new ProviderReportService())->formatReportDataForView($providerReport);

        $pdf = App::make('dompdf.wrapper');
        $pdf->loadView('providerReport.report', ['reportData' => $providerReportFormattedData, 'patient' => $patient]);


        $path = storage_path("provider_report_{$patient->id}_{$this->currentDate->toDateTimeString()}.pdf");

        $saved = file_put_contents($path, $pdf->output());

        if ( ! $saved) {
            return false;
        }

        return $patient->addMedia($path)
                       ->withCustomProperties(['doc_type' => 'Provider Report'])
                       ->toMediaCollection('patient-care-documents');
    }

    private function createAndUploadPdfPPP($ppp, $patient)
    {
        $personalizedHealthAdvices = (new PersonalizedPreventionPlanPrepareData())->prepareRecommendations($ppp);

        $pdf = App::make('dompdf.wrapper');
        $pdf->loadView('personalizedPreventionPlan', [
            'patientPppData'            => $ppp,
            'patient'                   => $patient,
            'personalizedHealthAdvices' => $personalizedHealthAdvices,
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
}
