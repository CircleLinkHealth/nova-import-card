<?php

namespace App\Http\Controllers\Patient;

use App\Practice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class EnrollmentConsentController extends Controller
{

    public function create($practice_name){


        $practice = Practice::whereName($practice_name)->get();

        if($practice->count() < 1){
            return view('errors.enrollmentConsentUrlError');

        }

        return view('enrollment-consent.create', ['practice' => $practice]);

    }

    public function store(Request $request){


        $input = $request->input();

        $enrolled = Carbon::parse($input['enrolled_time'])->toDateTimeString();
        $confirmed = Carbon::parse($input['confirmed_time'])->toDateTimeString();

        dd([$enrolled, $confirmed]);


    }

}
