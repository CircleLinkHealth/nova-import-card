<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class VaporPostRefactoringRenaming extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (app()->environment('testing')) {
            return;
        }

        collect($this->classPaths())->each(function ($change) {
            echo "\nChanging {$change['old']} to {$change['new']}.\n";

            \DB::table('revisions')
               ->where('revisionable_type', $change['old'])
               ->update(
                   [
                       'revisionable_type' => $change['new'],
                   ]
               );
        });
    }

    private function classPaths():array
    {
        return [
            [
                'old' => 'CircleLinkHealth\Eligibility\Entities\Enrollee',
                'new' => 'CircleLinkHealth\SharedModels\Entities\Enrollee',
            ],
        ];
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
