<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Services\ReportsService;
use App\User;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\App;

class CCDParserDemoController extends Controller {

	public function index()
    {
        $patientId = 742;
        $reportService = new ReportsService();
        $careplan = $reportService->carePlanGenerator([$patientId]);



        $pdf = App::make('dompdf.wrapper');

        $pdf->loadView('wpUsers.patient.careplan.print', [
            'patient' => User::find($patientId),
            'treating' => $careplan[$patientId]['treating'],
            'biometrics' => $careplan[$patientId]['bio_data'],
            'symptoms' => $careplan[$patientId]['symptoms'],
            'lifestyle' => $careplan[$patientId]['lifestyle'],
            'medications_monitor' => $careplan[$patientId]['medications'],
            'taking_medications' => $careplan[$patientId]['taking_meds'],
            'allergies' => $careplan[$patientId]['allergies'],
            'social' => $careplan[$patientId]['social'],
            'appointments' => $careplan[$patientId]['appointments'],
            'other' => $careplan[$patientId]['other'],
            'isPdf' => true,
        ])->setPaper('letter', 'portrait');

        return $pdf->stream();


//        $xml = XmlCCD::find(424);
//
//        debug($xml->ccd);
//
//        $patient = new CCDParser($xml->ccd);
//
//        echo '<pre>';
//
//            echo $patient->getParsedCCD('json');
//
//        echo '</pre>';
    }

}
