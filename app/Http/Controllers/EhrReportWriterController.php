<?php

namespace App\Http\Controllers;

use App\Traits\ValidatesEligibility;
use Illuminate\Http\Request;

class EhrReportWriterController extends Controller
{
    use ValidatesEligibility;

    public function index(){
        return view('ehrReportWriter.index');
    }

    public function validateJson(Request $request){

        $messages = [];
        if (! is_json($request->get('json'))){
            $messages['errors'][] = "The text is not in a valid JSON format";
            return redirect()->back()->withErrors($messages);
        }

        $data = json_decode($request['json'], true);

        foreach ($data as $patient){
            $structureValidator = $this->validateJsonStructure($patient);
            foreach ($structureValidator->errors()->messages() as $array){
                $messages['errors'][] = "Error for: ". $patient['first_name']. $patient['last_name'] . "-" . $array[0];
            }
            $dataValidator = $this->validateRow($patient);
            foreach ($dataValidator->errors()->messages() as $array){
                $messages['warnings'][] = "Warning for: " . $patient['first_name']. $patient['last_name'] . "-" . $array[0];
            }


        }

        return redirect()->back()->withErrors($messages);

    }

    public function submitFile(Request $request){

    }
}
