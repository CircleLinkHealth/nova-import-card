<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Http\Controllers;


use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class UploadsController extends Controller
{
    public function postGeneralCommentsCsv(Request $request)
    {
        if ( ! $request->hasFile('uploadedCsv')) {
            dd('File was not uploaded. Please try again.');
        }

        $file = $request->file('uploadedCsv');
        $csv  = parseCsvToArray($file);

        $failed = [];

        foreach ($csv as $row) {
            $firstName = $row['Patient First Name'];
            $lastName  = $row['Patient Last Name'];

            $patient = User::where('first_name', $firstName)
                ->where('last_name', $lastName)
                ->whereHas('patientInfo', function ($q) use ($row) {
                    $q->where(
                        'birth_date',
                        Carbon::parse($row['DOB'])->toDateString()
                    );
                })
                ->first();

            if ( ! $patient) {
                $failed[] = "${firstName} ${lastName}";
                continue;
            }

            $info = $patient->patientInfo;

            if (array_key_exists('General Comment', $row)) {
                $generalComment = $row['General Comment'];
            }

            if ( ! empty($generalComment)) {
                $info->general_comment = $generalComment;
                $info->save();
            }
        }

        if ( ! empty($failed)) {
            echo 'We could not find these patients. Is the name spelled correctly?';

            dd($failed);
        }

        return 'Everything imported well.';
    }
}
