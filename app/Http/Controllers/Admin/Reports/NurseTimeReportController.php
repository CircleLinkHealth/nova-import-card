<?php namespace App\Http\Controllers\Admin\Reports;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Program;
use App\User;
use App\PageTimer;
use Illuminate\Http\Request;
use Auth;
use DateTime;
use DatePeriod;
use DateInterval;

class NurseTimeReportController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index(Request $request)
	{
		//dd('yo');
		if(!Auth::user()->can('report-nurse-time-view')) {
			//abort(403);
		}

		// get all users with paused ccm_status
		$users = User::with('meta')
			->with('roles')
			->whereHas('roles', function($q) {
				$q->where(function ($query) {
					$query->orWhere('name', 'care-center');
					$query->orWhere('name', 'no-ccm-care-center');
				});
			})
			->get();

		// get date
		$date = date('Y-m-d H:i:s');

		$i = 0;

		// header
		$reportColumns = array('date');
		foreach($users as $user) {
			$reportColumns[] = $user->display_name;
		}

		// get array of dates
		$startDate = new DateTime('first day of this month');
		$endDate = new DateTime(date('Y-m-d'));

		// if form submitted dates, override here
		if($request->input('start_date')) {
			$startDate = new DateTime($request->input('start_date') . ' 00:00:01');
		}
		if($request->input('end_date')) {
			$endDate = new DateTime($request->input('end_date') . ' 23:59:59');
		}

		// to exclude the end date (so you just get dates between start and end date):
		// $b->modify('-1 day');

		$period = new DatePeriod($startDate, new DateInterval('P1D'), $endDate);

		$sheetRows = array(); // so we can reverse after

		$userTotals = array('');
		foreach($period as $dt) {
			//echo $dt->format('Y-m-d') .'<br />';

			$rowUserValues = array($dt->format('Y-m-d'));
			foreach($users as $user) {
				// get total activity time
				$pageTime = PageTimer::whereBetween( 'start_time', [
					$dt->format('Y-m-d') . ' 00:00:01', $dt->format('Y-m-d') . ' 23:59:59'
				] )
					->where( 'provider_id', $user->ID )
					->where( 'activity_type', '!=', '' )
					->sum('duration');

				$total = number_format((float)($pageTime / 60), 2, '.', '');
				$rowUserValues[] = $total;
				if(!isset($userTotals[$user->ID])) {
					$userTotals[$user->ID] = 0;
				}
				$userTotals[$user->ID] = $userTotals[$user->ID] + $total;
			}

			$sheetRows[] = $rowUserValues;

		}

		$sheetRows = array_reverse($sheetRows);

		// $reportRows
		$reportRows = array();
		foreach($sheetRows as $sheetRow) {
			$reportRows[] = $sheetRow;
		}
		$reportRows[] = $userTotals;

		// display view
		return view('admin.reports.nurseTimeReport.index', [ 'reportColumns' => $reportColumns, 'reportRows' => $reportRows, 'startDate' => $startDate, 'endDate' => $endDate ]);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function export()
	{
		if(!Auth::user()->can('report-nurse-time-manage')) {
			abort(403);
		}
		//
	}

}
