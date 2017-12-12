<?php

use App\Patient;
use App\PatientMonthlySummary;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPatientUserId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach (PatientMonthlySummary::all() as $summ) {
            $summ->patient_id = Patient::withTrashed()->where('id', $summ->patient_info_id)->first()->user_id;
            $summ->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
