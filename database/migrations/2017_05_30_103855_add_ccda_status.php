<?php

use App\Models\MedicalRecords\Ccda;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCcdaStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ccdas', function (Blueprint $table) {
            $table->enum('status', [
                'determine_enrollement_eligibility',
                'eligible',
                'ineligible',
                'patient_consented',
                'patient_declined',
                'import',
                'qa',
                'careplan_created',
            ])->after('json')
            ->nullable()
            ->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ccdas', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
}
