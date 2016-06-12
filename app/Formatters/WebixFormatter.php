<?php namespace App\Formatters;

use App\ActivityMeta;
use App\Call;
use App\Contracts\ReportFormatter;
use App\MailLog;
use App\Program;
use App\User;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class WebixFormatter implements ReportFormatter
{

    //Transform Reports Data for Webix

    public function formatDataForNotesListingReport($notes, $request)
    {
        $count = 0;

        $formatted_notes = array();

        foreach ($notes as $note) {
            $patient = User::find($note->patient_id);

            if (!$patient) {
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

            $mails = MailLog::where('note_id', $note->id)
                ->get();
            debug($mails);

            if (count($mails) > 0) {
                $formatted_notes[$count]['tags'] = '<div class="label label-warning"><span class="glyphicon glyphicon-envelope" aria-hidden="true"></span></div> ';
            }

            $call = Call::where('note_id', $note->id)
                ->first();

            if (is_object($call)) {
                if ($call->status == 'reached') {
                    $formatted_notes[$count]['tags'] .= '<div class="label label-info"><span class="glyphicon glyphicon-earphone" aria-hidden="true"></span></div> ';
                }
            }

            if ($note->isTCM == true) {
                $formatted_notes[$count]['tags'] .= '<div class="label label-danger"><span class="glyphicon glyphicon-flag" aria-hidden="true"></span></div> ';
            }

            $count++;
        }

//        //This would contain all data to be sent to the view
//        $notes = array();
//
//        //Get current page form url e.g. &page=6
//        $currentPage = LengthAwarePaginator::resolveCurrentPage();
//
//        //Create a new Laravel collection from the array data
//        $collection = new Collection($formatted_notes);
//
//        //Define how many items we want to be visible in each page
//        $per_page = 15;
//
//        //Slice the collection to get the items to display in current page
//        $currentPageResults = $collection->slice(($currentPage-1) * $per_page, $per_page)->all();
//
//        //Create our paginator and add it to the data array
//        $results = new LengthAwarePaginator($currentPageResults, count($collection), $per_page);
//
//        //Set base url for pagination links to follow e.g custom/url?page=6
//        $results->setPath($request->url());

        return $formatted_notes;

    }

    public function formatDataForNotesAndOfflineActivitiesReport($report_data)
    {

        if ($report_data->isEmpty()) {
            return '';
        }

        $formatted_data = array();
        $count = 0;

        foreach ($report_data as $data) {

            $formatted_data[$count]['id'] = $data->id;


            if (is_int($data->author_id)) // only notes have authors
            {
                $formatted_data[$count]['logger_name'] = User::find($data->author_id)->fullName;
                $formatted_data[$count]['comment'] = $data->body;
                $formatted_data[$count]['logged_from'] = 'note';

            } else // handles activities
            {
                $formatted_data[$count]['logger_name'] = User::find($data->logger_id)->fullName;
                $formatted_data[$count]['comment'] = $data->getCommentForActivity();
                $formatted_data[$count]['logged_from'] = 'manual_input';
            }

            $formatted_data[$count]['type_name'] = $data->type;
            $formatted_data[$count]['performed_at'] = $data->performed_at;


            $count++;

        }

        if (!empty($formatted_data)) {

            return "data:" . json_encode($formatted_data) . "";

        } else {

            return '';

        }
    }

}