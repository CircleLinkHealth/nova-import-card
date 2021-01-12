<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class ChangePreferredContactLocationTypeInLocationsTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_info', function (Blueprint $table) {
            $table->dropForeign(['preferred_contact_location']);
            $table->string('preferred_contact_location')->change();
        });
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Need to make sure that we do not have invalid locations, in order to create a foreign key index:

        // 1.
        DB::table('patient_info')
            ->where('preferred_contact_location', '=', '')
            ->update(['preferred_contact_location' => null]);

        // 2.
        Schema::table('patient_info', function (Blueprint $table) {
            $table->unsignedInteger('preferred_contact_location')
                ->nullable(true)
                ->change();
        });

        // 3. 0 should be null
        DB::table('patient_info')
            ->where('preferred_contact_location', '=', 0)
            ->update(['preferred_contact_location' => null]);

        // 2.0 invalid ids should also be null, so:
        // 2.1 create a temporary table with all patient_info ids that have an invalid location
        DB::raw('
create temporary table my_table
select pi.id
from patient_info pi
left join locations l on pi.preferred_contact_location = l.id
where pi.preferred_contact_location is not null and l.id is null;
        ');

        // 2.2 use this table to set preferred_contact_location to null
        DB::raw('
update patient_info
set preferred_contact_location = NULL
where id in (select * from my_table);
        ');

        // 3. add the foreign key
        Schema::table('patient_info', function (Blueprint $table) {
            $table->foreign('preferred_contact_location')
                ->references('id')
                ->on('locations')
                ->onDelete('SET NULL');
        });
    }
}
