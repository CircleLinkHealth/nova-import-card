<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAllergyImportsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('allergy_imports', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('ccda_id')->unsigned()->index();
            $table->integer('vendor_id')->unsigned()->index('allergy_imports_vendor_id_foreign');
            $table->integer('ccd_allergy_log_id')->unsigned()->index('allergy_imports_ccd_allergy_log_id_foreign');
            $table->string('allergen_name')->nullable();
            $table->integer('substitute_id')->unsigned()->nullable()->index('allergy_imports_substitute_id_foreign');
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
        Schema::drop('allergy_imports');
    }

}
