<?php

use App\Models\CPM\CpmProblem;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDiabetesType2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $diabetes1 = CpmProblem::create([
            'name' => 'Diabetes Type 1',
            'default_icd_10_code' => 'E10.8',
        ]);

        $diabetes2 = CpmProblem::create([
            'name' => 'Diabetes Type 2',
            'default_icd_10_code' => 'E11.8',
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
