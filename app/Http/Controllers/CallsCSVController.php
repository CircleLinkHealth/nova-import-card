<?php

namespace App\Http\Controllers;

use App\Call;
use App\Services\Calls\SchedulerService;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Http\Requests;

class CallsCSVController extends Controller
{
    protected $nurses = [
        'Patricia' => 1920,
        'Kathryn' => 2159,
        'Lydia' => 1755,
        'Sue' => 1877,
        'Monique' => 2332,
    ];

    public function uploadCSV(Request $request)
    {
        if ($request->hasFile('uploadedCsv')) {
            $csv = parseCsvToArray($request->file('uploadedCsv'));

            $calls = array();
            
            foreach ($csv as $patient){

                $temp = User::where('first_name', $patient['Patient First Name'])
                                  ->where('last_name', $patient['Patient Last Name'])
                                  ->whereHas('patientInfo',function ($q) use ($patient){
                                      $q->where('birth_date',
                                          Carbon::parse($patient['DOB'])->toDateString()
                                          );
                                  })
                                  ->first();

                if(is_object($temp))

                    {

                        $call = (new SchedulerService())->getScheduledCallForPatient($temp->ID);

                        Call::updateOrCreate([

                                'service' => 'phone',
                                'status' => 'scheduled',

                                'inbound_phone_number' => $patient->phone ? $patient->phone : '',
                                'outbound_phone_number' => '',

                                'inbound_cpm_id' => $patient->ID,
                                'outbound_cpm_id' => isset($nurse_id) ? $nurse_id : '',

                                'call_time' => 0,

                                'is_cpm_outbound' => true

                            ], [
                                'scheduled_date' => Carbon::parse($patient['Next call date'])->toDateString(),
                                'window_start' => Carbon::parse($patient['Call time From:'])->format('H:i'),
                                'window_end' => Carbon::parse($patient['Call time From:'])->format('H:i'),
                            ]);

                        $calls[] = $call;

                        }

                    };

            }

            return $calls;
        }
    
}
