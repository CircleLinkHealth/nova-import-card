<?php

use App\Models\ProblemCode;
use App\ProblemCodeSystem;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MigrateCodes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $icd10 = ProblemCodeSystem::where('name', 'ICD 10')->first();

        ProblemCode::where('code_system_name', 'like', '%10%')
            ->update([
                'problem_code_system_id' => $icd10->id,
            ]);

        $icd9 = ProblemCodeSystem::where('name', 'ICD 9')->first();

        ProblemCode::where('code_system_name', 'like', '%9%')
                   ->update([
                       'problem_code_system_id' => $icd9->id,
                   ]);

        $snomed = ProblemCodeSystem::where('name', 'SNOMED')->first();

        ProblemCode::where('code_system_name', 'like', '%snomed%')
                   ->update([
                       'problem_code_system_id' => $snomed->id,
                   ]);
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
