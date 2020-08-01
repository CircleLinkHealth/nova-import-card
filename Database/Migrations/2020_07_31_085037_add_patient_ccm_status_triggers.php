<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddPatientCcmStatusTriggers extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS patient_info_ccm_status_before_update');
        DB::unprepared('DROP TRIGGER IF EXISTS patient_info_ccm_status_after_insert');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //on insert
        DB::unprepared("
        
        CREATE TRIGGER patient_info_ccm_status_after_insert AFTER INSERT ON `patient_info`
        FOR EACH ROW
        BEGIN
            INSERT INTO patient_ccm_status_revisions(patient_info_id,patient_user_id,action,new_value)
            VALUES (NEW.id,NEW.user_id,'insert',NEW.ccm_status);
        END
        ");

        //on update
        DB::unprepared("
        
        CREATE TRIGGER patient_info_ccm_status_before_update BEFORE UPDATE ON `patient_info`
        FOR EACH ROW
        BEGIN
        IF NEW.ccm_status <> OLD.ccm_status THEN
            INSERT INTO patient_ccm_status_revisions(patient_info_id,patient_user_id,action,old_value,new_value)
            VALUES (NEW.id,NEW.user_id,'update',OLD.ccm_status,NEW.ccm_status);
            END IF;
        END
        ");
    }
}
