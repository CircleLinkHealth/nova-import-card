<?php

use App\Note;
use App\PatientCarePlan;
use App\PatientReports;
use Illuminate\Database\Migrations\Migration;

class ChangePdfReportsNames extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach (PatientReports::withTrashed()->get() as $r) {
            if ($r->file_type == 'note') {
                $r->file_type = Note::class;
            } elseif ($r->file_type == 'careplan') {
                $r->file_type = PatientCarePlan::class;
            }
            $r->save();

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
