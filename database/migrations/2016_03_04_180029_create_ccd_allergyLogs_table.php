<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCcdAllergyLogsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('ccd_allergy_logs')) {

            Schema::create('ccd_allergy_logs', function (Blueprint $table) {
                $table->increments('id');

                $table->unsignedInteger('ccda_id');
                $table->foreign('ccda_id')
                    ->references('id')
                    ->on('ccdas')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');

                $table->unsignedInteger('vendor_id');
                $table->foreign('vendor_id')
                    ->references('id')
                    ->on('ccd_vendors')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');

                $table->string('start')->nullable()->default(null);
                $table->string('end')->nullable()->default(null);
                $table->string('status')->nullable()->default(null);
                $table->string('allergen_name')->nullable()->default(null);

                $table->boolean('import');
                $table->boolean('invalid');

                $table->softDeletes();
                $table->timestamps();
            });
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('ccd_allergy_logs');
    }

}
