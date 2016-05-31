<?php

namespace App\Services;

use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class NoteService
{


    public function getNotesForPatient(User $patient){

        return DB::table('lv_activities')
            ->select(DB::raw('*,provider_id, type'))
            ->where('patient_id', $patient->ID)
            ->where(function ($q) {
                $q->where('logged_from', 'note')
                    ->Orwhere('logged_from', 'manual_input');
            })
            ->orderBy('performed_at', 'desc')
            ->get();

    }

    public function getNotesWithRangeForPatients($patients,$start,$end){

        return DB::table('lv_activities')
            ->select(DB::raw('*,provider_id, type'))
            ->whereIn('patient_id', $patients)
            ->where(function ($q) {
                $q->where('logged_from', 'note');
            }) 
            ->whereBetween('performed_at', [
                $start, $end
            ])
            ->orderBy('performed_at', 'desc')
            ->take(1000)->get();

    }

    public function getMonthsArray(){
        return array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
    }

    public function getYearsArray(){

        $years = array();
        for ($i = 0; $i < 3; $i++) {
            $years[] = Carbon::now()->subYear($i)->year;
        } array_reverse($years);

        return $years;

    }

}