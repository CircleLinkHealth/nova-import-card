<?php

/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 4/28/16
 * Time: 3:25 PM
 */
class CpmInstructionSeeder extends \Illuminate\Database\Seeder
{
    public function run()
    {
        $modelsCollection[] = \App\Models\CPM\CpmProblem::all();

        foreach ($modelsCollection as $collection)
        {
            foreach ($collection as $model)
            {
                $instruction = \App\Models\CPM\CpmInstruction::create([
                    'name' => 'Test Instruction'
                ]);
                
                $model->instructions()->attach($instruction);
            }
        }
    }
}