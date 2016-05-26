<?php namespace App\Formatters;

use App\ActivityMeta;
use App\Contracts\ReportFormatter;
use App\Program;
use App\User;

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

            //Display Name
            $formatted_notes[$count]['patient_name'] = $patient->display_name ? $patient->display_name : '';
            //Program Name
            $program = Program::find($patient->program_id);
            if ($program) $formatted_notes[$count]['program_name'] = $program->display_name;
            //Provider Name
            $provider = User::find(intval($patient->billingProviderID));
            if ($provider) {
                $formatted_notes[$count]['provider_name'] = $provider->fullName;
            } else {
                $formatted_notes[$count]['provider_name'] = '';
            }
            //Author
            $author_name = User::find(intval($note->logger_id));
            if (is_object($author_name)) {
                $formatted_notes[$count]['author_name'] = '';
            } else {
                $formatted_notes[$count]['author_name'] = '';
            }
            //Status

            $meta = ActivityMeta::where('activity_id',$note->id)
                ->where(function($query){
                    $query->where('meta_key', 'call_status')
                        ->orWhere('meta_key', 'email_sent_to')
                        ->orWhere('meta_key', 'hospital');
                })
                ->get();

            $formatted_notes[$count]['tags'] = '';

            foreach ($meta as $m) {
                switch ($m->meta_value) {
                    case('reached'):
                        $formatted_notes[$count]['tags'] .= '<div class="label label-info">reached</div>';
                        break;
                    case('hospital'):
                        $formatted_notes[$count]['tags'] .= '<div class="label label-danger">ER</div>';
                        break;
                    case('email_sent_to'):
                        $formatted_notes[$count]['tags'] .= '<div class="label label-info">Email</div>';
                        break;
                }
            }

            //Topic / Offline Act Name
            //Preview
            //Date

            $count++;
        }

        //dd($formatted_notes);

        return "data:" . json_encode(array_values($formatted_notes)) . "";
    }

}