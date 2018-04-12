<?php

use App\Keyword;
use App\Models\CPM\CpmProblem;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AttachKeywordsToCpmProblemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $problems = CpmProblem::get();

        foreach ($problems as $problem){

            if ($problem->contains){
                $keywords = explode(',', $problem->contains);
                foreach ($keywords as $keyword){
                    $problem->keywords()->create([
                        'name' => trim($keyword),
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Keyword::truncate();
    }
}
