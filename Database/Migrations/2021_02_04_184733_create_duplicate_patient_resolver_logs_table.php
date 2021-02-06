<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDuplicatePatientResolverLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('duplicate_patient_resolver_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('enrollee_id')->virtualAs('JSON_UNQUOTE(debug_logs->"$.debug_logs.enrollee.enrollee_id")')->index();
            $table->unsignedInteger('user_id_kept')->index();
            $table->unsignedInteger('user_id_deleted')->virtualAs('JSON_UNQUOTE(debug_logs->"$.debug_logs.patient.user_id")')->index();
            $table->json('debug_logs');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('duplicate_patient_resolver_logs');
    }
}
