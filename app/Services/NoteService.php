<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class NoteService
{

    public function getNotesForPatients($patients,$start,$end){

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
            ->get();

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