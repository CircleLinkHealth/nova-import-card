<?php

/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 4/19/16
 * Time: 4:34 PM
 */
class CarePlanTemplateRelationshipsTest extends TestCase
{
    public function testRelateProblem()
    {
        $cpmProblem = \App\Entities\CPM\CpmProblem::create([
            'name' => 'test',
            'icd10from' => 'test',
            'icd10to' => 'test',
            'icd9from' => 'test',
            'icd9to' => 'test',
            'contains' => 'test',
            'care_item_name' => 'diabetes',
        ]);

        $carePlanTemplate = \App\CarePlanTemplate::create([
            'display_name' => 'test',
            'program_id' => '7',
        ]);

        $carePlanTemplate->cpmProblems()->attach($cpmProblem->id);

        $carePlanTemplate->cpmProblems()->attach($cpmProblem->id);
        $check = $cpmProblem->carePlanTemplates()->get();
        
        $this->assertEquals($carePlanTemplate->id, $check[0]->id);
    }
}