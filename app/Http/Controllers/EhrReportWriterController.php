<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\EhrReportWriterInfo;
use App\Notifications\EhrReportWriterNotification;
use App\Services\CCD\ProcessEligibilityService;
use App\Services\GoogleDrive;
use App\Traits\ValidatesEligibility;
use App\User;
use Illuminate\Http\Request;
use Storage;

class EhrReportWriterController extends Controller
{
    use ValidatesEligibility;

    private $googleDrive;

    public function __construct(GoogleDrive $googleDrive)
    {
        $this->googleDrive = $googleDrive;
    }

    public function downloadCsvTemplate($name)
    {
        $filename = $name;

        try {
            $contents = $this->googleDrive->getContents('1zpiBkegqjTioZGzdoPqZQAqWvXkaKEgB');
        } catch (\Exception $e) {
            \Log::alert($e);
            $messages['warnings'][] = 'Folder Eligibility Templates not found!';

            return redirect()->back()->withErrors($messages);
        }

        if ($contents->isEmpty()) {
            $messages['warnings'][] = 'Folder Eligibility Templates not found!';

            return redirect()->back()->withErrors($messages);
        }

        $file = $contents
            ->where('type', '=', 'file')
            ->where('filename', '=', $filename)
            ->first();
        if (is_null($file)) {
            $messages['warnings'][] = 'File not found!';

            return redirect()->back()->withErrors($messages);
        }

        $service  = Storage::drive('google')->getAdapter()->getService();
        $mimeType = 'text/csv';
        $export   = $service->files->export($file['basename'], $mimeType);

        return response($export->getBody(), 200, $export->getHeaders());
    }

    /**
     * @return $this
     */
    public function index()
    {
        $messages  = [];
        $files     = [];
        $user      = auth()->user();
        $practices = $user->practices()->get();
        if ($user->hasRole('ehr-report-writer') && $user->ehrReportWriterInfo) {
            $googleFiles = $this->getUnprocessedFilesFromGoogleFolder($user->ehrReportWriterInfo);
            if (is_null($googleFiles)) {
                $messages['warnings'][] = 'No Google Drive folder found!';
            } else {
                $files = $googleFiles;
            }
        }

        return view('ehrReportWriter.index', compact(['files', 'practices']))->withErrors($messages);
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function notifyReportWriter(Request $request)
    {
        $reportWriterUser = User::find($request->get('initiator_id'));

        $text = $request->get('text');

        $reportWriterUser->notify(new EhrReportWriterNotification($text, $request->get('practice_name')));

        return redirect()->back()->with([
            'message' => 'Ehr Report Writer successfully notified.',
            'type'    => 'success',
        ]);
    }

    /**
     * @param Request                   $request
     * @param ProcessEligibilityService $service
     *
     * @return $this
     */
    public function submitFile(Request $request, ProcessEligibilityService $service)
    {
        $messages   = [];
        $user       = auth()->user();
        $practiceId = $request->input('practice_id');

        if ( ! $user->ehrReportWriterInfo) {
            $messages['errors'][] = 'You need to be an EHR Report Writer to use this feature.';

            return redirect()->back()->withErrors($messages);
        }

        if ( ! $practiceId) {
            $messages['warnings'][] = 'Please select a Practice!';

            return redirect()->back()->withErrors($messages);
        }

        $filterLastEncounter = (bool) $request->input('filterLastEncounter');
        $filterInsurance     = (bool) $request->input('filterInsurance');
        $filterProblems      = (bool) $request->input('filterProblems');

        $files = collect($request->input('googleDriveFiles', []))
            ->filter(
                function ($file) {
                    return array_key_exists('path', $file);
                }
            )->values();

        if ($files->isEmpty()) {
            $messages['warnings'][] = 'Please select one or more files to be reviewed by CLH!';

            return redirect()->back()->withErrors($messages);
        }

        foreach ($files as $file) {
            if (strcasecmp('csv', $file['ext']) == 0) {
                $batch = $service->createSingleCSVBatchFromGoogleDrive(
                    $user->ehrReportWriterInfo->google_drive_folder_path,
                    $file['name'],
                    $practiceId,
                    $filterLastEncounter,
                    $filterInsurance,
                    $filterProblems,
                    $file['path']
                );
            }
            if (strcasecmp('json', $file['ext']) == 0) {
                $batch = $service->createClhMedicalRecordTemplateBatch(
                    $user->ehrReportWriterInfo->google_drive_folder_path,
                    $file['name'],
                    $practiceId,
                    $filterLastEncounter,
                    $filterInsurance,
                    $filterProblems,
                    false,
                    $file['path']
                );
            }
            if ( ! isset($batch)) {
                $messages['warnings'][] = "Something went wrong with file: {$file['name']}.";
            }
        }
        if (empty($messages)) {
            $messages['success'][] = 'Thanks! CLH will review the file and get back to you. This may take a few business days.';
        }

        return redirect()->back()->withErrors($messages);
    }

    /**
     * @param Request $request
     *
     * @return $this
     */
    public function validateJson(Request $request)
    {
        $messages = [];
        $json     = $request->get('json');

        $localDisk    = Storage::disk('local');
        $fileName     = 'validate_json_for_ehr_report_writer';
        $pathToFile   = storage_path("app/${fileName}");
        $savedLocally = $localDisk->put($fileName, $json);

        if ( ! $savedLocally) {
            throw new \Exception("Failed saving ${pathToFile}");
        }
        $parser   = new \Seld\JsonLint\JsonParser();
        $iterator = read_file_using_generator($pathToFile);
        $i        = 1;
        foreach ($iterator as $iteration) {
            if ( ! $iteration) {
                continue;
            }
            try {
                $parser->parse($iteration, \Seld\JsonLint\JsonParser::DETECT_KEY_CONFLICTS);
            } catch (\Exception $e) {
                $messages['errors'][] = $i.' - '.$e->getMessage();
                continue;
            }
            $value              = json_decode($iteration, true);
            $structureValidator = $this->validateJsonStructure($value);
            foreach ($structureValidator->errors()->messages() as $array) {
                $messages['errors'][] = 'Error for patient: '.$i.'-'.$array[0];
            }
            $dataValidator = $this->validateRow($value);
            foreach ($dataValidator->errors()->messages() as $array) {
                $messages['warnings'][] = 'Warning for patient: '.$i.'-'.$array[0];
            }
            ++$i;
        }
        $localDisk->delete($fileName);

        if (empty($messages)) {
            $messages['success'][] = 'JSON structure and data are valid!';
        }

        return redirect()->back()->withErrors($messages);
    }

    /**
     * @param EhrReportWriterInfo $info
     *
     * @return \Illuminate\Support\Collection
     */
    private function getUnprocessedFilesFromGoogleFolder(EhrReportWriterInfo $info)
    {
        try {
            $files    = [];
            $contents = $this->googleDrive->getContents($info->google_drive_folder_path);

            if ($contents->isEmpty()) {
                return null;
            }
            foreach ($contents as $file) {
                if (starts_with($file['name'], 'processed')) {
                    continue;
                }
                $files[] = $file;
            }

            return collect($files);
        } catch (\Exception $e) {
            //if folder not found throws 404
            if (404 == $e->getCode()) {
                return null;
            }
            \Log::alert($e);
        }
    }
}
