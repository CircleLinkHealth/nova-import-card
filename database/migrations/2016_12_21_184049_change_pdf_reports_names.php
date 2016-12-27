<?php

use App\CarePlan;
use App\Note;
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
                $r->file_type = CarePlan::class;
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
