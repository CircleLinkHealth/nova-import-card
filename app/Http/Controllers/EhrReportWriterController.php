<?php

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
            $googleFiles = $this->getFilesFromGoogleFolder($user->ehrReportWriterInfo);

            if (is_null($googleFiles)) {
                $messages['warnings'][] = 'No Google Drive folder found!';
            } else {
                foreach ($googleFiles as $file) {
                    if (starts_with($file['name'], 'processed')) {
                        continue;
                    }
                    $files[] = $file;

                }
            }
        }


        return view('ehrReportWriter.index', compact(['files', 'practices']))->withErrors($messages);
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
        $fileName     = "validate_json_for_ehr_report_writer";
        $pathToFile   = storage_path("app/$fileName");
        $savedLocally = $localDisk->put($fileName, $json);

        if ( ! $savedLocally) {
            throw new \Exception("Failed saving $pathToFile");
        }
        $parser   = new \Seld\JsonLint\JsonParser;
        $iterator = read_file_using_generator($pathToFile);
        $i        = 1;
        foreach ($iterator as $iteration) {
            if ( ! $iteration) {
                continue;
            }
            try {
                $parser->parse($iteration, \Seld\JsonLint\JsonParser::DETECT_KEY_CONFLICTS);
            } catch (\Exception $e) {
                $messages['errors'][] = $i . " - " . $e->getMessage();
                continue;
            }
            $value              = json_decode($iteration, true);
            $structureValidator = $this->validateJsonStructure($value);
            foreach ($structureValidator->errors()->messages() as $array) {
                $messages['errors'][] = "Error for patient: " . $i . "-" . $array[0];
            }
            $dataValidator = $this->validateRow($value);
            foreach ($dataValidator->errors()->messages() as $array) {
                $messages['warnings'][] = "Warning for patient: " . $i . "-" . $array[0];
            }
            $i++;
        }
        $localDisk->delete($fileName);


        if (empty($messages)) {
            $messages['success'][] = "JSON structure and data are valid!";
        }

        return redirect()->back()->withErrors($messages);

    }

    /**
     * @param Request $request
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
            $messages['errors'][] = "You need to be an EHR Report Writer to use this feature.";

            return redirect()->back()->withErrors($messages);
        }

        if ( ! $practiceId) {
            $messages['warnings'][] = "Please select a Practice!";

            return redirect()->back()->withErrors($messages);
        }

        $filterLastEncounter = (boolean)$request->input('filterLastEncounter');
        $filterInsurance     = (boolean)$request->input('filterInsurance');
        $filterProblems      = (boolean)$request->input('filterProblems');

        $googleDriveFiles = [];

        for ($i = 0; $i < 100; $i++) {
            if ($request->input($i)) {
                if (array_key_exists('path', $request->input($i))) {
                    $googleDriveFiles[$i]['path'] = $request->input($i)['path'];
                    $googleDriveFiles[$i]['ext']  = $request->input($i)['ext'];
                    $googleDriveFiles[$i]['name'] = $request->input($i)['name'];
                }
            } else {
                break;
            }
        }
        if (empty($googleDriveFiles)) {
            $messages['warnings'][] = "Please select one or more files to be reviewed by CLH!";

            return redirect()->back()->withErrors($messages);
        }

        foreach ($googleDriveFiles as $file) {
            if ($file['ext'] == 'csv') {
                $batch = $service->createSingleCSVBatchFromGoogleDrive($user->ehrReportWriterInfo->google_drive_folder_path,
                    $file['name'], $practiceId, $filterLastEncounter, $filterInsurance,
                    $filterProblems, $file['path']);
            }
            if ($file['ext'] == 'json') {
                $batch = $service->createClhMedicalRecordTemplateBatch($user->ehrReportWriterInfo->google_drive_folder_path,
                    $file['name'], $practiceId, $filterLastEncounter, $filterInsurance,
                    $filterProblems, $file['path']);
            }
            if ( ! $batch) {
                $messages['warnings'][] = "Something went wrong with file: {$file['name']}.";
            }

        }
        if (empty($messages)) {
            $messages['success'][] = "Thanks! CLH will review the file and get back to you. This may take a few business days.";
        }

        return redirect()->back()->withErrors($messages);
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
     * @param EhrReportWriterInfo $info
     *
     * @return \Illuminate\Support\Collection
     */
    private function getFilesFromGoogleFolder(EhrReportWriterInfo $info)
    {
        try {
            return $this->googleDrive->getContents($info->google_drive_folder_path);
        } catch (\Exception $e) {
            //if folder not found throws 404
            if ($e->getCode() == 404) {
                return null;
            }
            \Log::alert($e);
        }
    }

}
