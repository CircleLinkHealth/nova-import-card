<?php

namespace App\Http\Controllers;

use App\Services\CCD\ProcessEligibilityService;
use App\Services\Eligibility\Csv\CsvPatientList;
use App\Traits\ValidatesEligibility;
use Illuminate\Http\Request;
use Storage;

class EhrReportWriterController extends Controller
{
    use ValidatesEligibility;

    public function index()
    {

        $user = auth()->user();

        $practices = $user->practices()->get();

        $files = $this->getFilesFromGoogleFolder($user);

        return view('ehrReportWriter.index', compact(['files', 'practices']));
    }

    public function validateJson(Request $request)
    {

        $messages = [];
        if ( ! is_json($request->get('json'))) {
            $messages['errors'][] = "The text is not in a valid JSON format";

            return redirect()->back()->withErrors($messages);
        }

        $data = json_decode($request['json'], true);

        foreach ($data as $patient) {
            $structureValidator = $this->validateJsonStructure($patient);
            foreach ($structureValidator->errors()->messages() as $array) {
                $messages['errors'][] = "Error for: " . $patient['first_name'] . $patient['last_name'] . "-" . $array[0];
            }
            $dataValidator = $this->validateRow($patient);
            foreach ($dataValidator->errors()->messages() as $array) {
                $messages['warnings'][] = "Warning for: " . $patient['first_name'] . $patient['last_name'] . "-" . $array[0];
            }


        }
        //todo:test this
        if (empty($messages)) {
            $messages['success'][] = "JSON structure and date is valid!";
        }

        return redirect()->back()->withErrors($messages);

    }

    public function submitFile(Request $request)
    {
        //if practice is not selected return
        $practiceId = $request->input('practice_id');

        $filterLastEncounter = (boolean)$request->input('filterLastEncounter');
        $filterInsurance     = (boolean)$request->input('filterInsurance');
        $filterProblems      = (boolean)$request->input('filterProblems');

        $files = [];


        for ($i = 0; $i < 100; $i++) {
            if ($request->input($i)) {
                if (array_key_exists('path', $request->input($i))) {
                    $files[$i]['path'] = $request->input($i)['path'];
                    $files[$i]['ext']  = $request->input($i)['ext'];
                }
            } else {
                break;
            }
        }
        $messages = [];

        $service = new ProcessEligibilityService();
        foreach ($files as $file) {
            if ($file['ext'] == 'csv') {
                //add try
                $string         = Storage::disk('google')->get($file['path']);
                $patients       = $this->parseCsvStringToArray($string);
                $csvPatientList = new CsvPatientList(collect($patients));
                $isValid        = $csvPatientList->guessValidator();

                if ( ! $isValid) {
                    return [
                        'errors' => 'This csv does not match any of the supported templates. you can see supported templates here https://drive.google.com/drive/folders/1zpiBkegqjTioZGzdoPqZQAqWvXkaKEgB',
                    ];
                }

                $batch = $service->createSingleCSVBatch($patients, $practiceId, $filterLastEncounter, $filterInsurance,
                    $filterProblems, true);
                if ($batch) {
                    $messages['success'][] = "Eligibility Batch created.";
                }
                //delete files that had batches created for them?
            }
            if ($file['ext'] == 'json') {
                //todo:
            }
        }




        return redirect()->back()->withErrors($messages);
    }

    private function getFilesFromGoogleFolder($user)
    {
        //todo: change folder name to reportWriter->google_drive_folder
        $contents = collect(Storage::cloud()->listContents('/', false));
        $dir      = $contents->where('type', '=', 'dir')
                             ->where('filename', '=', 'test')
                             ->first();

        if ( ! $dir) {
            return 'No such folder!';
        }
        $files = collect(Storage::cloud()->listContents($dir['path'], false))
            ->where('type', '=', 'file');

        return $files;
    }

    private function parseCsvStringToArray($string)
    {
        $lines   = explode(PHP_EOL, $string);
        $headers = str_getcsv(array_shift($lines));
        $data    = [];
        foreach ($lines as $line) {
            $row = [];
            foreach (str_getcsv($line) as $key => $field) {
                $row[$headers[$key]] = $field;
            }
            $row    = array_filter($row);
            $data[] = $row;
        }

        return $data;
    }
}
