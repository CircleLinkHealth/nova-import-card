<?php namespace App\Formatters;

use App\ActivityMeta;
use App\Call;
use App\Contracts\ReportFormatter;
use App\MailLog;
use App\Program;
use App\User;
use Carbon\Carbon;

class WebixFormatter implements ReportFormatter
{

    //Transform Reports Data for Webix

    public function formatDataForNotesListingReport($notes)
    {
        $count = 0;

        foreach($notes as $note){
            $patient = User::find($note->patient_id);

            if(!$patient){
                continue;
            }

            $formatted_notes[$count]['id'] = $note->id;

            //Display Name
            $formatted_notes[$count]['patient_name'] = $patient->display_name ? $patient->display_name : '';
            //ID
            $formatted_notes[$count]['patient_id'] = $note->patient_id;

            //Program Name
            $program = Program::find($patient->program_id);
            if ($program) $formatted_notes[$count]['program_name'] = $program->display_name;

            //Provider Name
            $provider = User::find(intval($patient->billingProviderID));
            if (is_object($provider)) {
                $formatted_notes[$count]['provider_name'] = $provider->fullName;
            } else {
                $formatted_notes[$count]['provider_name'] = '';
            }

            //Author
            $author = User::find($note->author_id);
            if (is_object($author)) {
                $formatted_notes[$count]['author_name'] = $author->display_name;
            } else {
                $formatted_notes[$count]['author_name'] = '';
            }

            //Type
            $formatted_notes[$count]['type'] = $note->type;

            //Body
            $formatted_notes[$count]['comment'] = $note->body;

            $formatted_notes[$count]['date'] = Carbon::parse($note->created_at)->format('Y-m-d');

            //TAGS
            $formatted_notes[$count]['tags'] = '';

            $mails = MailLog::where('note_id',$note->id)
                ->get();

            if(count($mails) > 0){
                $formatted_notes[$count]['tags'] = '<div class="label label-warning"><span class="glyphicon glyphicon-envelope" aria-hidden="true"></span></div> ';
            }

            $call = Call::where('note_id',$note->id)
                ->first();

            if(is_object($call)){
                if($call->status == 'reached'){
                    $formatted_notes[$count]['tags'] .= '<div class="label label-info"><span class="glyphicon glyphicon-earphone" aria-hidden="true"></span></div> ';
                }
            }

                if($note->isTCM == true){
                    $formatted_notes[$count]['tags'] .= '<div class="label label-danger"><span class="glyphicon glyphicon-flag" aria-hidden="true"></span></div> ';
            }

            $count++;
        }

        return "data:" . json_encode(array_values($formatted_notes)) . "";
    }

    public function formatDataForNotesAndOfflineActivitesReport($report_data)
    {
        $acts = json_decode(json_encode($acts), true);

        foreach ($acts as $key => $value) {
            $acts[$key]['patient'] = User::find($patientId);
        }

        foreach ($acts as $key => $value) {
            $act_id = $acts[$key]['id'];
            $acts_ = Activity::find($act_id);
            $comment = $acts_->getActivityCommentFromMeta($act_id);
            $acts[$key]['comment'] = $comment;
        }

        $activities_data_with_users = array();
        $activities_data_with_users[$patientId] = $acts;

        $reportData[$patientId] = array();
        foreach ($activities_data_with_users as $patientAct) {
            $reportData[] = collect($patientAct)->groupBy('performed_at_year_month');
        }

        for ($i = 0; $i < count($patientAct) ; $i++) {
            $logger_user = User::find($patientAct[$i]['logger_id']);
            if ($logger_user) {
                $patientAct[$i]['logger_name'] = $logger_user->getFullNameAttribute();
            } else {
                $patientAct[$i]['logger_name'] = 'N/A';
            }
        }

        $data = true;
        return "data:" . json_encode($patientAct) . "";
    }
}