<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\Http\Requests\DownloadMediaWithSignedRequest;
use App\Http\Requests\DownloadPracticeAuditReports;
use App\Http\Requests\DownloadZippedMediaWithSignedRequest;
use App\Jobs\CreateAuditReportForPatientForMonth;
use Carbon\Carbon;
use CircleLinkHealth\Core\GoogleDrive;
use CircleLinkHealth\Customer\Entities\Media;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaStream;

class DownloadController extends Controller
{
    private $googleDrive;

    public function __construct(GoogleDrive $googleDrive)
    {
        $this->googleDrive = $googleDrive;
    }

    public function downloadAuditReportsForMonth(DownloadPracticeAuditReports $request)
    {
        $practiceId = $request->input('practice_id');
        $monthInput = $request->input('month');

        $date = Carbon::createFromFormat('Y-m', $monthInput)->startOfMonth();

        if ( ! $this->auditReportsQuery($date, $practiceId)->exists()) {
            return response()->redirectToRoute('make.monthly.audit.reports', ['practice_id' => $practiceId, 'month' => $date->format('Y-m')]);
        }

        $mediaExport = $this->auditReportsQuery($date, $practiceId)->get();

        if ($mediaExport->isNotEmpty()) {
            return MediaStream::create("Audit Reports for {$date->format('F, Y')}.zip")->addMedia($mediaExport);
        }

        abort(400, 'No reports found.');
    }

    public function downloadCsvFromGoogleDrive($filename, $dir = '/', $recursive = true)
    {
        $contents = $this->googleDrive->getContents($dir, $recursive);

        $file = $contents
            ->where('type', '=', 'file')
            ->where('filename', '=', pathinfo($filename, PATHINFO_FILENAME))
            ->first();

        if (is_null($file)) {
            $messages['warnings'][] = 'File not found!';

            return response()->json($messages, 404);
        }

        $service  = Storage::drive('google')->getAdapter()->getService();
        $mimeType = 'text/csv';
        $export   = $service->files->export($file['basename'], $mimeType);

        return response($export->getBody(), 200, $export->getHeaders());
    }

    public function downloadMediaFromSignedUrl(DownloadMediaWithSignedRequest $request)
    {
        return $this->downloadMedia(Media::findOrFail($request->route('media_id')));
    }

    public function downloadUserMediaCollectionAsZip($collectionName)
    {
        $collection = Media::where('collection_name', $collectionName)
            ->where('model_id', auth()->user()->id)
            ->whereIn('model_type', [\App\User::class, 'CircleLinkHealth\Customer\Entities\User'])
            ->get();

        if ($collection->isEmpty()) {
            return 'We could not find the files you are looking for. Please contact Circle Link support.';
        }

        return MediaStream::create('patient-consent-letters.zip')->addMedia($collection);
    }

    public function downloadZippedMedia(DownloadZippedMediaWithSignedRequest $request)
    {
        $ids = explode(',', $request->route('media_ids'));

        $mediaExport = Media::whereIn('id', $ids)->get();

        if ($mediaExport->isNotEmpty()) {
            $now = now()->toDateTimeString();

            return MediaStream::create("Practice Billing Documents downloaded at $now.zip")->addMedia($mediaExport);
        }
    }

    /**
     * Returns file requested to download.
     *
     * @param $filePath
     *
     * @return string|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function file($filePath)
    {
        if (empty($filePath)) {
            abort(400, 'File to download must be provided.');
        }

        $path = storage_path($filePath);

        //try looking in the download folder
        if ( ! file_exists($path)) {
            $path = storage_path("download/${filePath}");
        }

        if ( ! file_exists($path)) {
            $path = storage_path("eligibility-templates/${filePath}");
        }

        if ( ! file_exists($path)) {
            $downloadMedia = $this->mediaFileExists($filePath);

            if ($downloadMedia) {
                return $downloadMedia;
            }

            $path = storage_path($filePath);
        }

        if ( ! file_exists($path)) {
            $path = $filePath;
        }

        if ( ! file_exists($path)) {
            $path = base64_decode($filePath);
        }

        if ( ! file_exists($path)) {
            return abort(400, "Could not locate file with name: ${filePath}");
        }

        $fileName = str_replace('/', '', strrchr($filePath, '/'));

        return response()->download(
            $path,
            $fileName,
            [
                'Content-Length: '.filesize($path),
            ]
        );
    }

    public function makeAuditReportsForMonth(DownloadPracticeAuditReports $request)
    {
        $practiceId = $request->input('practice_id');
        $monthInput = $request->input('month');

        $date = Carbon::createFromFormat('Y-m', $monthInput)->startOfMonth();

        User::ofType('participant')->ofPractice($practiceId)
            ->with('patientInfo')
            ->with('patientSummaries')
            ->with('primaryPractice')
            ->whereHas('patientSummaries', function ($query) use ($date) {
                $query->where('total_time', '>', 0)
                    ->where('month_year', $date->toDateString());
            })
            ->chunkById(200, function ($patients) use ($date) {
                $delay = 5;

                foreach ($patients as $patient) {
                    CreateAuditReportForPatientForMonth::dispatch($patient, $date)
                        ->onQueue('low')->delay(now()->addSeconds($delay));
                    ++$delay;
                }
            });

        return 'CPM will create reports for patients for '.$date->format('F, Y').' Visit '.link_to_route('make.monthly.audit.reports', 'this page', ['practice_id' => $practiceId, 'month' => $date->format('Y-m')]).' in 10-20 minutes to download the reports.';
    }

    public function mediaFileExists($filePath)
    {
        $filePath = base64_decode($filePath);

        if (is_json($filePath)) {
            $decoded = json_decode($filePath, true);

            if ( ! empty($decoded['media_id'])) {
                $media = Media::findOrFail($decoded['media_id']);

                if ( ! $this->canDownload($media)) {
                    abort(403);
                }

                return $this->downloadMedia($media);
            }
        }

        return null;
    }

    public function postDownloadfile(Request $request)
    {
        return $this->file($request->input('filePath'));
    }

    private function auditReportsQuery(Carbon $date, $practiceId)
    {
        return Media::whereIn(
            'model_type',
            [
                \App\User::class,
                \CircleLinkHealth\Customer\Entities\User::class,
            ]
        )->whereIn(
            'model_id',
            function ($query) use ($practiceId) {
                $query->select('id')
                    ->from((new User())->getTable())
                    ->where('program_id', $practiceId);
            }
        )->where('collection_name', 'audit_report_'.$date->format('F, Y'))
            ->groupBy('model_id');
    }

    private function canDownload(Media $media)
    {
        if (Practice::class != $media->model_type) {
            return true;
        }

        $practiceId = $media->model_id;

        return auth()->user()->practice((int) $practiceId) || auth()->user()->isAdmin();
    }
}
