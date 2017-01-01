<?php

use App\CarePlan;
use App\PatientReports;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameCarePlan extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach (PatientReports::withTrashed()->get() as $r) {
            if ($r->file_type == 'App\PatientCarePlan') {
                $r->file_type = CarePlan::class;
                $r->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_reports', function (Blueprint $table) {
            //
        });
    }
}
