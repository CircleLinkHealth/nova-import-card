<?php

namespace App\Http\Controllers;

use App\EhrReportWriterInfo;
use App\Jobs\GenerateEligibilityBatchesForReportWriter;
use App\Notifications\EhrReportWriterNotification;
use App\Services\CCD\ProcessEligibilityService;
use App\Traits\ValidatesEligibility;
use App\User;
use Illuminate\Http\Request;
use Seld\JsonLint\JsonParser;
use Storage;

class EhrReportWriterController extends Controller
{
    use ValidatesEligibility;

    /**
     * @return $this
     */
    public function index()
    {
        $messages  = [];
        $files     = [];
        $user      = auth()->user();
        $practices = $user->practices()->get();
        if ($user->hasRole('ehr-report-writer')) {
            $files = $this->getFilesFromGoogleFolder($user->ehrReportWriterInfo);

            if (is_null($files)) {
                $messages['warnings'][] = 'No Google Drive folder found!';
                $files                  = [];
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
        $json = $request->get('json');

        if ( ! is_json($request->get('json'))) {
            $messages['errors'][] = "The text is not in a valid JSON format" . " - error: " . json_last_error_msg();

            return redirect()->back()->withErrors($messages);
        }

        $data = json_decode($request['json'], true);


        $i = 1;
        foreach ($data as $key => $value) {

            if ( ! is_numeric($key)) {
                $value = $data;
            }

            $structureValidator = $this->validateJsonStructure($value);
            foreach ($structureValidator->errors()->messages() as $array) {
                $messages['errors'][] = "Error for patient: " . $i . "-" . $array[0];
            }
            $dataValidator = $this->validateRow($value);
            foreach ($dataValidator->errors()->messages() as $array) {
                $messages['warnings'][] = "Warning for patient: " . $i . "-" . $array[0];
            }
            $i++;

            if ( ! is_numeric($key)) {
                break;
            }
        }


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

        if ( ! $practiceId) {
            $messages['warnings'][] = "Please select a Practice!";

            return redirect()->back()->withErrors($messages);
        }

        $filterLastEncounter = (boolean)$request->input('filterLastEncounter');
        $filterInsurance     = (boolean)$request->input('filterInsurance');
        $filterProblems      = (boolean)$request->input('filterProblems');

        $files = [];

        for ($i = 0; $i < 100; $i++) {
            if ($request->input($i)) {
                if (array_key_exists('path', $request->input($i))) {
                    $files[$i]['path'] = $request->input($i)['path'];
                    $files[$i]['ext']  = $request->input($i)['ext'];
                    $files[$i]['name'] = $request->input($i)['name'];
                }
            } else {
                break;
            }
        }

        foreach ($files as $file) {
            if ($file['ext'] == 'csv') {
                $batch = $service->createSingleCSVBatchFromGoogleDrive($user->ehrReportWriterInfo->google_drive_folder_path,
                    $file['name'], $practiceId, $filterLastEncounter, $filterInsurance,
                    $filterProblems);
            }
            if ($file['ext'] == 'json') {
                //add try?
                $batch = $service->createClhMedicalRecordTemplateBatch($user->ehrReportWriterInfo->google_drive_folder_path,
                    $file['name'], $practiceId, $filterLastEncounter, $filterInsurance,
                    $filterProblems);
            }
        }

        $messages['success'][] = "Thanks! CLH will review the file and get back to you. This may take a few business days.";

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
     * @param EhrReportWriterInfo $user
     *
     * @return null|static
     */
    private function getFilesFromGoogleFolder(EhrReportWriterInfo $info)
    {
        $contents = collect(Storage::drive('google')->listContents('/', false));
        $dir      = $contents->where('type', '=', 'dir')
                             ->where('filename', '=', $info->google_drive_folder)
                             ->first();

        if ( ! $dir) {
            return null;
        }
        $files = collect(Storage::disk('google')->listContents($dir['path'], false))
            ->where('type', '=', 'file');

        return $files;
    }

}
