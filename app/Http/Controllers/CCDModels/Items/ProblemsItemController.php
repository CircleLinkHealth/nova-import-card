<?php namespace App\Http\Controllers\CCDModels\Items;

use App\User;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\CCD\CcdProblem;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Class ProblemListItemController
 * @package App\Http\Controllers\CCDModels\Items
 */
class ProblemsItemController extends Controller {

	public function index(Request $request)
	{
		$data   = array();
		$patientId = $request->input('patient_id');
		$ccdProblems = CcdProblem::where('patient_id', '=', $patientId)->get();
		if($ccdProblems->count() > 0) {
			foreach($ccdProblems as $ccdProblem) {
				$data[] = array(
					'id' => $ccdProblem->id,
					'patient_id' => $ccdProblem->patient_id,
					'name' => $ccdProblem->name);
			}
		}
		// return a JSON response
		return response()->json($data);
	}

	public function store(Request $request)
	{
		// pass back some data, along with the original data, just to prove it was received
		$problem = $request->input('problem');
		if(!empty($problem)) {
			$ccdProblem = New CcdProblem;
			$ccdProblem->patient_id = $problem['patient_id'];
			$ccdProblem->name = $problem['name'];
			$ccdProblem->save();
			$id = $ccdProblem;
		}
		$result = array('id' => $id);
		// return a JSON response
		return response()->json($result);
	}

	public function update(Request $request)
	{
		// pass back some data, along with the original data, just to prove it was received
		$problem = $request->input('problem');
		if(!empty($problem)) {
			$ccdProblem = CcdProblem::find( $problem['id'] );
			if ( !$ccdProblem ) {
				return response( "Problem not found", 401 );
			}
			$ccdProblem->name = $problem['name'];
			$ccdProblem->save();
		}
		$string = '';
		// return a JSON response
		return response()->json($string);
	}

	public function destroy(Request $request)
	{
		$problem = $request->input('problem');
		if(!empty($problem)) {
			$ccdProblem = CcdProblem::find( $problem['id'] );
			if ( !$ccdProblem ) {
				return response( "Problem " . $problem['id'] . " not found", 401 );
			}
			$ccdProblem->delete();
		}

		return response('Successfully removed Problem');
	}

}
