<?php namespace App\Formatters;

use App\ActivityMeta;
use App\Contracts\ReportFormatter;
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
            $author = User::find($note->logger_id);
            if (is_object($author)) {
                $formatted_notes[$count]['author_name'] = $author->display_name;
            } else {
                $formatted_notes[$count]['author_name'] = '';
            }

            //Type
            $formatted_notes[$count]['type'] = $note->type;

            //Status
            $formatted_notes[$count]['status'] = $note->type;

            //Comments
            $metaComment = ActivityMeta::where('activity_id',$note->id)
                ->where('meta_key', 'comment')->first();

            $meta = ActivityMeta::where('activity_id',$note->id)
                ->where(function($query){
                    $query->where('meta_key', 'call_status')
                        ->orWhere('meta_key', 'email_sent_to')
                        ->orWhere('meta_key', 'hospital')
                        ->orWhere('meta_key', 'comment');
                })
                ->get();

            $formatted_notes[$count]['tags'] = '';
            $formatted_notes[$count]['comment'] = $metaComment->meta_value;
            $formatted_notes[$count]['date'] = Carbon::parse($note->created_at)->format('Y-m-d');

            foreach ($meta as $m) {
                switch ($m->meta_value) {
                    case('reached'):
                        $formatted_notes[$count]['tags'] .= '<div class="label label-info">Reached</div>';
                        break;
                    case('admitted'):
                        $formatted_notes[$count]['tags'] .= '<div class="label label-danger">ER</div>';
                        break;
                }
            }

            foreach ($meta as $m) {
                if($m->meta_key == 'email_sent_to') {
                    $formatted_notes[$count]['tags'] .= '<div class="label label-warning">Email</div>';
                }
            }

            //Topic / Offline Act Name
            //Preview
            //Date

            $count++;
        }

        return "data:" . json_encode(array_values($formatted_notes)) . "";
    }

}