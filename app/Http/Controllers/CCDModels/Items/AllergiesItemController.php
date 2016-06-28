<?php namespace App\Http\Controllers\CCDModels\Items;

use App\User;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\CCD\CcdAllergy;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Class AllergyListItemController
 * @package App\Http\Controllers\CCDModels\Items
 */
class AllergiesItemController extends Controller {

	public function index(Request $request)
	{
		$data   = array();
		$patientId = $request->input('patient_id');
		$ccdAllergies = CcdAllergy::where('patient_id', '=', $patientId)->orderBy('allergen_name')->get();
		if($ccdAllergies->count() > 0) {
			foreach($ccdAllergies as $ccdAllergy) {
				$data[] = array(
					'id' => $ccdAllergy->id,
					'patient_id' => $ccdAllergy->patient_id,
					'name' => $ccdAllergy->allergen_name);
			}
		}
		// return a JSON response
		return response()->json($data);
	}

	public function store(Request $request)
	{
		$result = 'error';
		// pass back some data, along with the original data, just to prove it was received
		$allergy = $request->input('allergy');
		if(!empty($allergy)) {
			$ccdAllergy = New CcdAllergy;
			$ccdAllergy->patient_id = $allergy['patient_id'];
			$ccdAllergy->allergen_name = $allergy['name'];
			$ccdAllergy->ccda_id = null;
			$ccdAllergy->save();
			$id = $ccdAllergy;
			$result = array('id' => $id);
		}
		// return a JSON response
		return response()->json($result);
	}

	public function update(Request $request)
	{
		// pass back some data, along with the original data, just to prove it was received
		$allergy = $request->input('allergy');
		if(!empty($allergy)) {
			$ccdAllergy = CcdAllergy::find( $allergy['id'] );
			if ( !$ccdAllergy ) {
				return response( "Allergy not found", 401 );
			}
			$ccdAllergy->allergen_name = $allergy['name'];
			$ccdAllergy->save();
		}
		$string = '';
		// return a JSON response
		return response()->json($string);
	}

	public function destroy(Request $request)
	{
		$allergy = $request->input('allergy');
		if(!empty($allergy)) {
			$ccdAllergy = CcdAllergy::find( $allergy['id'] );
			if ( !$ccdAllergy ) {
				return response( "Allergy " . $allergy['id'] . " not found", 401 );
			}
			$ccdAllergy->delete();
		}

		return response('Successfully removed Allergy');
	}

}
