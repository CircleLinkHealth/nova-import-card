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
        'Katie' => 2159,
        'Lydia' => 1755,
        'Sue' => 1877,
        'Monique' => 2332,
    ];

    public function uploadCSV(Request $request)
    {
        $calls = array();

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

                        $call = (new SchedulerService())->getScheduledCallForPatient($temp);

                        Call::updateOrCreate([

                                'service' => 'phone',
                                'status' => 'scheduled',

                                'inbound_phone_number' => $temp->phone ? $temp->phone : '',
                                'outbound_phone_number' => '',

                                'inbound_cpm_id' => $temp->ID,
                                'outbound_cpm_id' => $this->nurses[$patient['Nurse']],

                                'call_time' => 0,

                                'is_cpm_outbound' => true

                            ], [

                                'scheduled_date' => Carbon::parse($patient['Next call date'])->toDateString(),

                                'window_start' => empty($patient['Call time From:'])
                                    ? Carbon::parse($patient['Call time From:'])->format('H:i')
                                    : '09:00',

                                'window_end' => empty($patient['Call time to:'])
                                    ? Carbon::parse($patient['Call time to:'])->format('H:i')
                                    : '17:00'

                            ]);

                        $calls[] = $call;

                        }

                    };

            }

            return $calls;

        }
    
}
