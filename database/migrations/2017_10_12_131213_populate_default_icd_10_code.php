<?php

use App\Models\CPM\CpmProblem;
use Illuminate\Database\Migrations\Migration;

class PopulateDefaultIcd10Code extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        collect([
            '1'  => 'E11.8',
            '2'  => 'I10',
            '3'  => 'I48.91',
            '4'  => 'I25.9',
            '5'  => 'F33.9',
            '6'  => 'I50.9',
            '7'  => 'E78.5',
            '8'  => 'N18.9',
            '9'  => 'F03',
            '11' => 'J45.901',
            '14' => 'F17.299',
            '15' => 'E03.9',
            '16' => 'I21.9',
            '17' => 'G30.9',
            '18' => 'D64.9',
            '19' => 'N40.1',
            '20' => 'H25.9',
            '21' => 'J44.9',
            '22' => 'H40.9',
            '23' => 'S32.9XXA',
            '24' => 'M81.0',
            '25' => 'M19.90',
            '26' => 'I63.9',
            '27' => 'C50.919',
            '28' => 'C18.9',
            '29' => 'Z85.46',
            '30' => 'C34.90',
            '31' => 'C54.1',
        ])->map(function($icd10Code, $id){
            $problem = CpmProblem::find($id);
            $problem->default_icd_10_code = $icd10Code;
            $problem->save();
        });


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
