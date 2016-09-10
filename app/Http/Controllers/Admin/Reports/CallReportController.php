<?php namespace App\Http\Controllers\Admin\Reports;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Call;
use App\User;
use App\PageTimer;
use Illuminate\Http\Request;
use Auth;
use DateTime;
use DatePeriod;
use DateInterval;
use Excel;

class CallReportController extends Controller {

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

		$calls = Call::with('inboundUser')
			->with('outboundUser')
			->with('note')
			->select(
				[
					'calls.id AS call_id',
					'calls.status',
					'calls.outbound_cpm_id',
					'calls.inbound_cpm_id',
					'calls.scheduled_date',
					'calls.window_start',
					'calls.window_end',
					'notes.type AS note_type',
					'notes.body AS note_body',
					'notes.performed_at AS note_datetime',
					'calls.note_id',
					'patient_info.cur_month_activity_time',
					'patient_info.last_successful_contact_time',
					'patient_info.ccm_status',
					'patient_info.birth_date',
					'patient_monthly_summaries.no_of_calls',
					'patient_monthly_summaries.no_of_successful_calls',
					'nurse.display_name AS nurse_name',
					'patient.display_name AS patient_name',
					'program.display_name AS program_name',
					'billing_provider.display_name AS billing_provider'
				])
			->where('calls.status', '=', 'scheduled')
			->leftJoin('notes', 'calls.note_id','=','notes.id')
			->leftJoin('users AS nurse', 'calls.outbound_cpm_id','=','nurse.ID')
			->leftJoin('users AS patient', 'calls.inbound_cpm_id','=','patient.ID')
			->leftJoin('patient_info', 'calls.inbound_cpm_id','=','patient_info.user_id')
			->leftJoin('patient_monthly_summaries', 'patient_monthly_summaries.patient_info_id','=','patient_info.user_id')
			->leftJoin('wp_blogs AS program', 'patient.program_id','=','program.blog_id')
			->leftJoin('patient_care_team_members', function($join)
			{
				$join->on('patient.ID', '=', 'patient_care_team_members.user_id');
				$join->where('patient_care_team_members.type', '=', "billing_provider");
			})
			->leftJoin('users AS billing_provider', 'patient_care_team_members.member_user_id','=','billing_provider.ID')
			->groupBy('call_id')
			->get();

		Excel::create('CLH-Report-' . $date, function ($excel) use ($date, $calls) {

			// Set the title
			$excel->setTitle('CLH Call Report - ' . $date);

			// Chain the setters
			$excel->setCreator('CLH System')
				->setCompany('CircleLink Health');

			// Call them separately
			$excel->setDescription('CLH Call Report - ' . $date);

			// Our first sheet
			$excel->sheet('Sheet 1', function ($sheet) use ($calls) {
				$sheet->protect('clhpa$$word', function (\PHPExcel_Worksheet_Protection $protection) {
					$protection->setSort(true);
				});
				$i = 0;
				// header
				$userColumns = array('id', 'nurse name', 'patient name', 'dob', 'status', 'scheduled_date', 'window start', 'window end', 'last call status', 'CCM Time', 'no of calls', 'successful calls', 'ccm status', 'billing provider', 'program name');
				$sheet->appendRow($userColumns);

				foreach ($calls as $call) {
					if($call->inboundUser && $call->inboundUser->patientInfo) {
						$ccmTime = substr($call->inboundUser->patientInfo->currentMonthCCMTime, 1);
					} else {
						$ccmTime = 'n/a';
					}

					if($call->inboundUser && $call->inboundUser->patientInfo) {
						if (is_null($call->inboundUser->patientInfo->no_call_attempts_since_last_success)) {
							$noAttmpts = 'n/a';
						} else if ($call->inboundUser->patientInfo->no_call_attempts_since_last_success > 0) {
							$noAttmpts = $call->inboundUser->patientInfo->no_call_attempts_since_last_success . 'x Attempts';
						} else {
							$noAttmpts = 'Success';
						}
					}

					//dd($call);
					$columns = array($call->call_id, $call->nurse_name, $call->patient_name, $call->birth_date, $call->status, $call->scheduled_date, $call->window_start, $call->window_end, $call->last_successful_contact_time, $ccmTime, $call->no_of_calls, $noAttmpts, $call->ccm_status, $call->billing_provider, $call->program_name);
					$sheet->appendRow($columns);
				}
			});

		})->export('xls');
	}

}
