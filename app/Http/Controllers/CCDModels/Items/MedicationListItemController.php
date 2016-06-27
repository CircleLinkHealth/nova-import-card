<?php namespace App\Http\Controllers\CCDModels\Items;

use App\User;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\CCD\CcdMedication;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Class MedicationListItemController
 * @package App\Http\Controllers\CCDModels\Items
 */
class MedicationListItemController extends Controller {

	public function index(Request $request)
	{
		$data   = array();
		$patientId = $request->input('patient_id');
		$ccdMedications = CcdMedication::where('patient_id', '=', $patientId)->get();
		if($ccdMedications->count() > 0) {
			foreach($ccdMedications as $ccdMedication) {
				$data[] = array(
					'id' => $ccdMedication->id,
					'patient_id' => $ccdMedication->patient_id,
					'name' => $ccdMedication->name,
					'sig' => $ccdMedication->sig);
			}
		}
		// return a JSON response
		return response()->json($data);
	}

	public function store(Request $request)
	{
		// pass back some data, along with the original data, just to prove it was received
		$medication = $request->input('medication');
		if(!empty($medication)) {
			$ccdMedication = New CcdMedication;
			$ccdMedication->patient_id = $medication['patient_id'];
			$ccdMedication->name = $medication['name'];
			$ccdMedication->sig = $medication['sig'];
			$ccdMedication->save();
			$id = $ccdMedication;
		}
		$result = array('id' => $id);
		// return a JSON response
		return response()->json($result);
	}

	public function update(Request $request)
	{
		// pass back some data, along with the original data, just to prove it was received
		$medication = $request->input('medication');
		if(!empty($medication)) {
			$ccdMedication = CcdMedication::find( $medication['id'] );
			if ( !$ccdMedication ) {
				return response( "Medication not found", 401 );
			}
			$ccdMedication->name = $medication['name'];
			$ccdMedication->sig = $medication['sig'];
			$ccdMedication->save();
		}
		$string = '';
		// return a JSON response
		return response()->json($string);
	}

	public function destroy(Request $request)
	{
		$medication = $request->input('medication');
		if(!empty($medication)) {
			$ccdMedication = CcdMedication::find( $medication['id'] );
			if ( !$ccdMedication ) {
				return response( "Medication " . $medication['id'] . " not found", 401 );
			}
			$ccdMedication->delete();
		}

		return response('Successfully removed Medication');
	}

}
