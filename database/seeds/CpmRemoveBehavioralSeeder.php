<?php

use CircleLinkHealth\CarePlanModels\Entities\CpmProblem;
use Illuminate\Database\Seeder;

class CpmRemoveBehavioralSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $problems = CpmProblem::where('is_behavioral', true);

        $problems->get()->map(function ($problem) {
            $problem->problemImports()->delete();
            $this->command->warn("$problem->name has been deleted");
        });
        $problems->delete();
    }
}
