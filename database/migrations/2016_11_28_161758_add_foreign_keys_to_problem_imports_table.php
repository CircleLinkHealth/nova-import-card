<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToProblemImportsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('problem_imports', function (Blueprint $table) {
            $table->foreign('ccd_problem_log_id')->references('id')->on('ccd_problem_logs')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('ccda_id')->references('id')->on('ccdas')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('cpm_problem_id')->references('id')->on('cpm_problems')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('substitute_id')->references('id')->on('problem_imports')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('vendor_id')->references('id')->on('ccd_vendors')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('problem_imports', function (Blueprint $table) {
            $table->dropForeign('problem_imports_ccd_problem_log_id_foreign');
            $table->dropForeign('problem_imports_ccda_id_foreign');
            $table->dropForeign('problem_imports_cpm_problem_id_foreign');
            $table->dropForeign('problem_imports_substitute_id_foreign');
            $table->dropForeign('problem_imports_vendor_id_foreign');
        });
    }
}
