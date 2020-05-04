<?php

namespace App\Jobs;

use App\CPM\PatientReportCreatedEvent;
use App\Notifications\SendReport;
use App\PersonalizedPreventionPlan;
use App\Services\GeneratePersonalizedPreventionPlanService;
use App\Services\GenerateProviderReportService;
use App\Services\PersonalizedPreventionPlanPrepareData;
use App\Services\ProviderReportService;
use App\User;
use Barryvdh\Snappy\PdfWrapper;
use Carbon\Carbon;
use GrofGraf\LaravelPDFMerger\PDFMerger;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class GeneratePatientReportsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 360;

    protected $patientId;

    /**
     * Year to specify for which survey instances to generate Provider Report for.
     *
     * @var int
     */
    protected $instanceYear;

    protected $currentDate;

    /**
     * Choose whether to save in Media and therefore cloud, or keep as a local file (for debugging).
     * @var bool
     */
    protected $debug;

    /**
     * Create a new job instance.
     *
     * @param $patientId
     * @param $instanceYear
     * @param bool $debug
     */
    public function __construct($patientId, int $instanceYear, $debug = false)
    {
        $this->instanceYear = $instanceYear;
        $this->patientId = $patientId;
        $this->currentDate = Carbon::now();
        $this->debug = $debug;
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array
     */
    public function tags()
    {
        return ['Generate Patient Report', $this->patientId];
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
            'regularDoctor',
            'billingProvider',
            'primaryPractice',
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

        //instantiate Redis Event class to emit report created events to CPM
        $redisEvent = new PatientReportCreatedEvent($patient);

        //Generate Reports
        $providerReport = (new GenerateProviderReportService($patient))->generateData();
        if (! $providerReport) {
            throw new \Exception("Something went wrong while generating Provider Report for patient with id:{$patient->id}");
        }

        $pppReport = (new GeneratePersonalizedPreventionPlanService($patient))->generateData();
        if (! $pppReport) {
            throw new \Exception("Something went wrong while generating Provider Report for patient with id:{$patient->id}");
        }

        $providerReportMedia = $this->createAndUploadPdfProviderReport($providerReport, $patient, $this->debug);
        if (! $providerReportMedia) {
            throw new \Exception("Something went wrong while uploading Provider Report for patient with id:{$patient->id}");
        }

        if (! $this->debug) {
            $redisEvent->publishReportCreated($providerReportMedia);
        }

        $pppMedia = $this->createAndUploadPdfPPP($pppReport, $patient, $this->debug);
        if (! $pppMedia) {
            throw new \Exception("Something went wrong while uploading PPP for patient with id:{$patient->id}");
        }

        if (! $this->debug) {
            $redisEvent->publishReportCreated($pppMedia);
        }

        if ($this->debug) {
            return;
        }

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
     * @param bool $saveLocally
     *
     * @return bool
     * @throws \Exception
     */
    private function createAndUploadPdfProviderReport($providerReport, $patient, $saveLocally = false)
    {
        $pathToCoverPage = $this->getCoverPagePdf($patient, $providerReport->updated_at, 'Provider Report');
        if (! $pathToCoverPage) {
            throw new \Exception('Could not get cover page for Provider Report');
        }

        $providerReportFormattedData = (new ProviderReportService())->formatReportDataForView($providerReport);

        /** @var PdfWrapper $pdf */
        $pdf = App::make('snappy.pdf.wrapper');
        $this->setPdfOptions($pdf);

        $headerHtml = View::make('reports.pppHeader', [
            'patientName' => $patient->display_name,
            'patientDob'  => $patient->patientInfo->dob(),
            'reportName'  => 'Provider Report',
        ])->render();
        $pdf->setOption('header-html', $headerHtml);

        $pdf->loadView('reports.provider', [
            'reportData' => $providerReportFormattedData,
            'patient'    => $patient,
            'isPdf'      => true,
        ]);

        $pathToData = storage_path("provider_report_{$patient->id}_{$this->currentDate->toIso8601ZuluString()}_data.pdf");
        $savedData = file_put_contents($pathToData, $pdf->output());
        if (! $savedData) {
            throw new \Exception("Could not get store file $pathToData");
        }

        $path = storage_path("provider_report_{$patient->id}_{$this->currentDate->toIso8601ZuluString()}.pdf");
        $saved = $this->mergePdfs($path, $pathToCoverPage, $pathToData);
        if (! $saved) {
            throw new \Exception("Could not merge pdfs [$path]");
        }

        if ($saveLocally) {
            return $saved;
        }

        return $patient->addMedia($path)
                       ->withCustomProperties(['doc_type' => 'Provider Report', 'year' => $this->instanceYear])
                       ->toMediaCollection('patient-care-documents');
    }

    private function getCoverPagePdf(
        User $patient,
        Carbon $generatedAt,
        string $reportTitle,
        string $reportTitle2 = null
    ): string {
        /** @var PdfWrapper $cover */
        $cover = App::make('snappy.pdf.wrapper');
        $this->setPdfOptions($cover, false);

        $doctorsName = null;
        if (! empty($patient->regularDoctorUser())) {
            $doctorsName = $patient->regularDoctorUser()->getFullName();
        } elseif (! empty($patient->billingProviderUser())) {
            $doctorsName = $patient->billingProviderUser()->getFullName();
        }

        $cover->loadView('reports.cover', [
            'isPdf'        => true,
            'patient'      => $patient,
            'title1'       => $reportTitle,
            'title2'       => $reportTitle2,
            'generatedAt'  => $generatedAt->format('m/d/Y'),
            'practiceName' => $patient->primaryProgramName(),
            'providerName' => $doctorsName,
        ]);

        $title = Str::snake($reportTitle);
        $coverPath = storage_path("{$title}_report_{$patient->id}_{$this->currentDate->toIso8601ZuluString()}_temp_cover.pdf");
        $coverSaved = file_put_contents($coverPath, $cover->output());

        return $coverSaved
            ? $coverPath
            : null;
    }

    private function setPdfOptions(PdfWrapper $pdf, bool $addPageNumbers = true)
    {
        $pdf->setOption('lowquality', false);
        $pdf->setOption('disable-smart-shrinking', true);
        $pdf->setOption('margin-top', 20);
        $pdf->setOption('margin-bottom', 20);
        $pdf->setOption('margin-left', 20);
        $pdf->setOption('margin-right', 20);
        if ($addPageNumbers) {
            $pdf->setOption('footer-right', '[page] of [topage]');
        }
    }

    private function mergePdfs(string $targetPath, string $pdf1, string $pdf2): string
    {
        try {
            Log::info("Merging [$pdf1, $pdf2] to $targetPath");

            /** @var \GrofGraf\LaravelPDFMerger\PDFMerger $pdfMerger */
            $pdfMerger = new PDFMerger(app('files'));
            $pdfMerger->addPathToPDF($pdf1);
            $pdfMerger->addPathToPDF($pdf2);
            $pdfMerger->merge();
            $saved = $pdfMerger->save($targetPath);

            //delete temp files
            //unlink($pdf1);
            //unlink($pdf2);

            return $saved;
        } catch (\Exception $e) {
            throw $e;
//            \Log::error($e->getMessage());

//            return false;
        }
    }

    private function createAndUploadPdfPPP(PersonalizedPreventionPlan $ppp, User $patient, $saveLocally = false)
    {
        $pathToCoverPage = $this->getCoverPagePdf($patient, $ppp->updated_at, 'Personalized', 'Prevention Plan');
        if (! $pathToCoverPage) {
            return false;
        }

        $personalizedHealthAdvices = (new PersonalizedPreventionPlanPrepareData())->prepareRecommendations($ppp);
        $suggestedCheckListData = PersonalizedPreventionPlanPrepareData::getOrderedSuggestedChecklist($personalizedHealthAdvices);

        /** @var PdfWrapper $pdf */
        $pdf = App::make('snappy.pdf.wrapper');
        $this->setPdfOptions($pdf);

        $headerHtml = View::make('reports.pppHeader', [
            'patientName' => $patient->display_name,
            'patientDob'  => $patient->patientInfo->dob(),
            'reportName'  => 'PPP',
        ])->render();
        $pdf->setOption('header-html', $headerHtml);

        $pdf->loadView('reports.ppp', [
            'patientPppData'            => $ppp,
            'patient'                   => $patient,
            'personalizedHealthAdvices' => $personalizedHealthAdvices,
            'suggestedCheckListData'    => $suggestedCheckListData,
            'isPdf'                     => true,
        ]);

        $dataPath = storage_path("ppp_report_{$patient->id}_{$this->currentDate->toIso8601ZuluString()}_data.pdf");
        $dataSaved = file_put_contents($dataPath, $pdf->output());
        if (! $dataSaved) {
            return false;
        }

        $path = storage_path("ppp_report_{$patient->id}_{$this->currentDate->toIso8601ZuluString()}.pdf");
        $saved = $this->mergePdfs($path, $pathToCoverPage, $dataPath);
        if (! $saved) {
            return false;
        }

        if ($saveLocally) {
            return $saved;
        }

        return $patient->addMedia($path)
                       ->withCustomProperties(['doc_type' => 'PPP', 'year' => $this->instanceYear])
                       ->toMediaCollection('patient-care-documents');
    }
}
