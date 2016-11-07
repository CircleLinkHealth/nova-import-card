<?php namespace App\Http\Controllers\Admin\Reports;

use App\Http\Controllers\Controller;
use App\Practice;
use App\User;
use Excel;
use Illuminate\Http\Request;

class PatientConditionsReportController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index(Request $request)
	{
		//
	}

	/**
	 * export xls
	 */

	public function exportxls(Request $request)
	{
		$date = date('Y-m-d H:i:s');

		$patients = User::with('roles')
			->with('patientInfo')
			->whereHas('roles', function ($q) {
				$q->where(function ($query) {
					$query->orWhere('name', 'participant');
				});
			})
			->get();

		Excel::create('CLH-Patient-Conditions-Report-' . $date, function ($excel) use ($date, $patients) {

			// Set the title
			$excel->setTitle('CLH Patient Conditions Report - ' . $date);

			// Chain the setters
			$excel->setCreator('CLH System')
				->setCompany('CircleLink Health');

			// Call them separately
			$excel->setDescription('CLH Patient Conditions Report - ' . $date);

			// Our first sheet
			$excel->sheet('Sheet 1', function ($sheet) use ($patients) {
				$sheet->protect('clhpa$$word', function (\PHPExcel_Worksheet_Protection $protection) {
					$protection->setSort(true);
				});
				$i = 0;
				// header
				$userColumns = array('Patient Name', 'Status', 'Program', 'Total Conditions', 'Conditions');
				$sheet->appendRow($userColumns);

				foreach ($patients as $patient) {
					// program
					$programName = '';
                    $program = Practice::find($patient->program_id);
					if ($program) $programName = $program->display_name;

					// problems
					$patientProblems = $patient->cpmProblems()->get();
					$conditionsText = '';
					$total = $patientProblems->count();
					if($patientProblems->count() > 0) {
						foreach($patientProblems as $patientProblem) {
							$conditionsText .= $patientProblem->name.',';
						}
						$conditionsText = rtrim($conditionsText, ",");
					}
					$columns = array($patient->display_name, $patient->patientInfo->ccm_status, $programName, $total, $conditionsText);
					$sheet->appendRow($columns);
				}
			});

		})->export('xls');
	}

}
