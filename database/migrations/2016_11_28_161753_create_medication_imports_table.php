<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMedicationImportsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('medication_imports', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('ccda_id')->unsigned()->index();
            $table->integer('vendor_id')->unsigned()->index('medication_imports_vendor_id_foreign');
            $table->integer('ccd_medication_log_id')->unsigned()->index('medication_imports_ccd_medication_log_id_foreign');
            $table->string('name')->nullable();
            $table->string('sig')->nullable();
            $table->string('code')->nullable();
            $table->string('code_system')->nullable();
            $table->string('code_system_name')->nullable();
            $table->integer('substitute_id')->unsigned()->nullable()->index('medication_imports_substitute_id_foreign');
            $table->softDeletes();
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('medication_imports');
    }

}
