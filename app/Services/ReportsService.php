<?php namespace App\Services;

use App\Observation;
use App\WpUser;
use Illuminate\Support\Facades\DB;

Class ReportsService
{
    public function careplan()
    {


        $obs_keys = array('Blood_Sugar','Blood_Pressure','Weight','Cigarettes');

        $progress = array();
        $userHeader = array();
        $treating = array();
        $medications = array();
        $trackingChanges = array();

        //USER HEADER:
//       {
//      "Date": "2015-08-27",
//      "Patient_Name": "Chico Marx",
//      "Patient_Phone": "203-385-2176",
//      "Provider_Name": "Kerry Palakanis",
//      "Provider_Phone": "404-555-1212",
//      "Clinic_Name": "Crisfield Clinic"
//       }

        foreach ($obs_keys as $obs_key) {

            $trackingChanges[] = DB::table('observations')
                ->where('user_id',308)
                ->where('obs_key',$obs_key)
                ->select('user_id', 'obs_date','obs_key')->get();
        }

        //return json_encode($trackingChanges);
    }

}