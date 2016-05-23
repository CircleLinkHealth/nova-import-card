<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class NoteService
{

    public function getNotesForPatients($patients){

        return DB::table('lv_activities')
            ->select(DB::raw('*,provider_id, type'))
            ->whereIn('patient_id', $patients)
            ->where(function ($q) {
                $q->where('logged_from', 'note');
            })
            ->orderBy('performed_at', 'desc')
            ->get();

    }

}