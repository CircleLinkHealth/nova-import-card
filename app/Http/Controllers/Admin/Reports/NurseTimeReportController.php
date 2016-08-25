<?php namespace App\Http\Controllers\Admin\Reports;

use App\Activity;
use App\Call;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\PageTimer;
use App\User;
use Auth;
use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use DateTime;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\Datatables\Facades\Datatables;


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
		$showAllTimes = false;
		if($request->input('showAllTimes') == 'checked') {
			$showAllTimes = 'checked';
		}
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

		$userTotals = array('TOTAL:');
		foreach($period as $dt) {
			//echo $dt->format('Y-m-d') .'<br />';

			$rowUserValues = array($dt->format('Y-m-d'));
			foreach($users as $user) {
				// get total activity time
				$pageTime = PageTimer::whereBetween('start_time', [
					$dt->format('Y-m-d') . ' 00:00:01', $dt->format('Y-m-d') . ' 23:59:59'
				])
					->where('provider_id', $user->ID);
				// toggle whether to show total times
				if(!$showAllTimes) {
					$pageTime = $pageTime->where('activity_type', '!=', '');
				}
				$pageTime = $pageTime->sum('duration');

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
		return view('admin.reports.nurseTimeReport.index', [ 'reportColumns' => $reportColumns, 'reportRows' => $reportRows, 'startDate' => $startDate, 'endDate' => $endDate, 'showAllTimes' => $showAllTimes ]);
	}

	/**
	 * export
	 */

	public function exportxls(Request $request)
	{
		// get array of dates
		$startDate = new DateTime('first day of this month');
		$endDate = new DateTime(date('Y-m-d'));

		// if form submitted dates, override here
		$showAllTimes = false;
		if($request->input('showAllTimes')) {
			$showAllTimes = 'checked';
		}
		if($request->input('start_date')) {
			$startDate = new DateTime($request->input('start_date') . ' 00:00:01');
		}
		if($request->input('end_date')) {
			$endDate = new DateTime($request->input('end_date') . ' 23:59:59');
		}

		$period = new DatePeriod($startDate, new DateInterval('P1D'), $endDate);

		$users = User::with('meta')
			->with('roles')
			->whereHas('roles', function ($q) {
				$q->where(function ($query) {
					$query->orWhere('name', 'care-center');
					$query->orWhere('name', 'no-ccm-care-center');
				});
			})
			->get();

		$date = date('Y-m-d H:i:s');

		Excel::create('CLH-Report-' . $date, function ($excel) use ($date, $users, $period, $showAllTimes) {

			// Set the title
			$excel->setTitle('CLH Report T3');

			// Chain the setters
			$excel->setCreator('CLH System')
				->setCompany('CircleLink Health');

			// Call them separately
			$excel->setDescription('CLH Report T3');

			// Our first sheet
			$excel->sheet('Sheet 1', function ($sheet) use ($users, $period, $showAllTimes) {
				$sheet->protect('clhpa$$word', function (\PHPExcel_Worksheet_Protection $protection) {
					$protection->setSort(true);
				});
				$i = 0;
				// header
				$userColumns = array('date');
				foreach ($users as $user) {
					$userColumns[] = $user->display_name;
				}
				$sheet->appendRow($userColumns);

				$sheetRows = array(); // so we can reverse after

				foreach ($period as $dt) {
					$rowUserValues = array($dt->format('Y-m-d'));
					foreach ($users as $user) {
						// get total activity time
						$pageTime = PageTimer::whereBetween('start_time', [
							$dt->format('Y-m-d') . ' 00:00:01', $dt->format('Y-m-d') . ' 23:59:59'
						])
							->where('provider_id', $user->ID);
						// toggle whether to show total times
						if(!$showAllTimes) {
							$pageTime = $pageTime->where('activity_type', '!=', '');
						}
						$pageTime = $pageTime->sum('duration');

						$rowUserValues[] = number_format((float)($pageTime / 60), 2, '.', '');
					}

					$sheetRows[] = $rowUserValues;

				}

				$sheetRows = array_reverse($sheetRows);

				foreach ($sheetRows as $sheetRow) {
					$sheet->appendRow($sheetRow);
				}
			});

		})->export('xls');
	}

	public function makeDailyReport(){

		return view('admin.reports.nursedaily');

	}

	//@todo Code needs cleanup, done with urgency in mind
	public function dailyReport(){

		$nurse_ids = User::whereHas('roles', function ($q) {
			$q->where('name', '=', 'care-center');
		})->pluck('ID');

		$i = 0;
		$nurses = array();


		foreach ($nurse_ids as $nurse_id){

			$nurse = User::find($nurse_id);

			$nurses[$i]['id'] = $nurse_id;
			$nurses[$i]['name'] = $nurse->fullName;

			$last_activity_date = DB::table('lv_page_timer')->select(DB::raw('max(`end_time`) as last_activity'))->where('provider_id', $nurse_id)->get();

			if($last_activity_date[0]->last_activity == null){
				$nurses[$i]['Time Since Last Activity'] = 'N/A';
			} else {
				$nurses[$i]['Time Since Last Activity'] = Carbon::parse($last_activity_date[0]->last_activity)->diffForHumans();

			}

			$nurses[$i]['# Calls Today'] =
				Call::where('outbound_cpm_id', $nurse_id)
					->where(function ($q){
						$q->where('updated_at', '>=' , Carbon::now()->startOfDay())
							->where('updated_at', '<=' , Carbon::now()->endOfDay());
					})
					->where(function ($q){
						$q->where('status', 'reached')
							->orWhere('status', '');
					})
					->count();

			$nurses[$i]['# Successful Calls Today'] =
				Call::where('outbound_cpm_id', $nurse_id)
					->where(function ($q){
						$q->where('updated_at', '>=' , Carbon::now()->startOfDay())
						  ->where('updated_at', '<=' , Carbon::now()->endOfDay());
					})
					->where('status', 'reached')
					->count();

//        $nurses[$nurse->fullName]['# Scheduled Calls Today'] =
//            \App\Call::where('outbound_cpm_id', $nurse_id)
//                ->where(function ($q){
//                    $q->where('updated_at', '>=' , Carbon::now()->startOfDay())
//                        ->where('updated_at', '<=' , Carbon::now()->endOfDay());
//                })
//                ->where('status', 'scheduled')
//                ->count();

			$activity_time = Activity::where(function ($q) use ($nurse_id){
					$q->where('provider_id', $nurse_id)
						->orWhere('logger_id', $nurse_id);
					})
				->where(function ($q){
					$q->where('created_at', '>=' , Carbon::now()->startOfDay())
						->where('created_at', '<=' , Carbon::now()->endOfDay());
				})
				->sum('duration');

			$H1 = floor($activity_time / 3600);
			$m1 = ($activity_time / 60) % 60;
			$s1 = $activity_time % 60;
			$activity_time_formatted = sprintf("%02d:%02d:%02d",$H1, $m1, $s1);

			$system_time = PageTimer::where('provider_id', $nurse_id)
				->where(function ($q){
					$q->where('updated_at', '>=' , Carbon::now()->startOfDay())
					->where('updated_at', '<=' , Carbon::now()->endOfDay());
				})
//				->whereNotNull('activity_type')
				->sum('duration');

			$H2 = floor($system_time / 3600);
			$m2 = ($system_time / 60) % 60;
			$s2 = $system_time % 60;
			$system_time_formatted = sprintf("%02d:%02d:%02d",$H2, $m2, $s2);

			$nurses[$i]['CCM Mins Today'] = $activity_time_formatted;
			$nurses[$i]['Total Mins Today'] = $system_time_formatted;

			$carbon_now = Carbon::now();

			$nurses[$i]['lessThan20MinsAgo'] = false;

			if($last_activity_date == null){

				$nurses[$i]['last_activity'] = 'N/A';

			} else {

				$carbon_last_act = Carbon::parse($last_activity_date[0]->last_activity);
				$nurses[$i]['last_activity'] = $carbon_last_act->toDateTimeString();

				$diff = $carbon_now->diffInSeconds($carbon_last_act);

				if ($diff <= 1200 && $nurses[$i]['Time Since Last Activity'] != 'N/A') {
					$nurses[$i]['lessThan20MinsAgo'] = true;
				}
			}
			
			if($nurses[$i]['Time Since Last Activity'] == 'N/A'){
				unset($nurses[$i]);
			}

			$i++;

		}

		$nurses = collect($nurses);
		$nurses->sortBy('last_activity');

		debug($nurses);

		return Datatables::collection($nurses)->make(true);

	}

}
