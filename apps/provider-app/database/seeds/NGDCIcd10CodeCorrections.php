<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\SharedModels\Entities\CpmProblem;
use CircleLinkHealth\SharedModels\Entities\Problem;
use Illuminate\Database\Seeder;

class NGDCIcd10CodeCorrections extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $corrections = [
            ['109257', 'Diabetes', 'Hypertension', 'E11.22', 'I12.9'],
            ['171546', 'Hypertension', 'High Cholesterol', 'I11.9', 'I43'],
            ['155764', 'Diabetes', 'Hypertension', 'E11.22', 'I13.10'],
            ['121550', 'Hypertension', 'Osteoporosis', 'I10', 'I65.29'],
            ['178067', 'Hypertension', 'Afib', 'I11.0', 'I50.32'],
            ['179612', 'Afib', 'Glaucoma', 'I48.0', 'E78.5'],
            ['65963', 'Hypertension', 'High Cholesterol', 'I11.9', 'I25.10'],
            ['29061', 'Hypertension', 'Afib', 'I13.10', 'I48.91'],
            ['103309', 'Diabetes', 'Hypertension', 'F03.90', 'I11.9'],
            ['186182', 'Hypertension', 'High Cholesterol', 'I10', 'E78.00'],
            ['182303', 'Hypertension', 'High Cholesterol', 'I10', 'E78.00'],
            ['18059', 'Diabetes', 'Hypertension', 'I11.9', 'I34.0'],
            ['162546', 'Diabetes', 'Hypertension', 'E11.22', 'I13.10'],
            ['88436', 'Hypertension', 'Afib', 'I13.10', 'I48.91'],
            ['32791', 'Diabetes', 'Hypertension', 'E11.22', 'I13.10'],
            ['168317', 'Hypertension', 'High Cholesterol', 'I10', 'E78.2'],
            ['159819', 'Diabetes', 'Hypertension', 'E11.22', 'I12.9'],
            ['166984', 'Diabetes', 'Hypertension', 'I13.10', 'N18.3'],
            ['162273', 'Diabetes', 'Hypertension', 'E11.22', 'E11.59'],
            ['101446', 'Hypertension', 'Afib', 'I11.9', 'I48.91'],
            ['107206', 'Diabetes', 'Hypertension', 'E11.22', 'I12.9'],
            ['168843', 'Diabetes', 'Depression', 'E78.00', 'F32.9'],
            ['160988', 'Hypertension', 'Depression', 'I13.10', 'I34.0'],
            ['143050', 'Diabetes', 'Hypertension', 'E11.9', 'I11.9'],
            ['121892', 'Hypertension', 'Dementia', 'I10', 'F04'],
            ['157165', 'Diabetes', 'Hypertension', 'I11.9', 'I25.10'],
            ['175313', 'Diabetes', 'Hypertension', 'E11.9', 'I10'],
            ['139549', 'Hypertension', 'CHF', 'I13.10', 'I50.1'],
            ['143646', 'Diabetes', 'CAD/IHD', 'I12.5', 'J44.9'],
            ['13815', 'Diabetes', 'Afib', 'I48.91', 'I25.10'],
            ['162294', 'Diabetes', 'Hypertension', 'E11.22', 'I12.9'],
            ['119288', 'CAD/IHD', 'High Cholesterol', 'I25.2', 'E78.00'],
            ['205850', 'Diabetes', 'Hypertension', 'E11.42', 'I10'],
            ['154612', 'Diabetes', 'Hypertension', 'E11.42', 'I10'],
            ['104153', 'Hypertension', 'High Cholesterol', 'I10', 'E78.00'],
            ['213602', 'Hypertension', 'CHF', 'I11.0', 'I50.1'],
            ['212588', 'Hypertension', 'Afib', 'I11.9', 'I48.91'],
            ['44552', 'Hypertension', 'Afib', 'I12.9', 'N18.3'],
            ['109368', 'Hypertension', 'Depression', 'I10', 'F33.9'],
            ['106363', 'Hypertension', 'CHF', 'I11.9', 'I50.1'],
            ['151346', 'Diabetes', 'Hypertension', 'E11.9', 'I12.9'],
            ['178604', 'Hypertension', 'CAD/IHD', 'I13.10', 'I25.2'],
            ['71725', 'Hypertension', 'High Cholesterol', 'I11.9', 'I25.10'],
            ['188141', 'Afib', 'CHF', 'I48.91', 'I50.1'],
            ['133874', 'Diabetes', 'Hypertension', 'E11.40', 'I10'],
            ['214053', 'Hypertension', 'Dementia', 'I10', 'G30.9'],
            ['22101', 'Diabetes', 'Hypertension', 'E11.22', 'I13.10'],
            ['106981', 'Hypertension', 'Depression', 'I11.0', 'F41.8'],
            ['185748', 'Hypertension', 'Afib', 'I10', 'I48.91'],
            ['109069', 'CAD/IHD', 'Depression', 'I25.2', 'F32.9'],
            ['134539', 'Diabetes', 'Hypertension', 'E11.649', 'I13.10'],
            ['214619', 'CAD/IHD', 'Depression', 'I25.10', 'F32.0'],
            ['211878', 'Afib', 'CAD/IHD', 'I48.91', 'I25.10'],
            ['64191', 'Hypertension', 'Depression', 'I12.9', 'N18.9'],
            ['197913', 'Hypertension', 'CAD/IHD', 'I11.9', 'I25.10'],
            ['185682', 'Diabetes', 'Hypertension', 'E11.9', 'I10'],
            ['157770', 'Diabetes', 'Hypertension', 'E11.40', 'I11.9'],
            ['120687', 'Afib', 'Depression', 'I48.91', 'F32.9'],
            ['187170', 'Diabetes', 'Hypertension', 'I10', 'E78.00'],
            ['200236', 'Hypertension', 'Afib', 'I12.9', 'N18.3'],
            ['185541', 'Diabetes', 'Hypertension', 'E11.9', 'I10'],
            ['116654', 'Diabetes', 'Hypertension', 'E11.22', 'I12.9'],
            ['185210', 'Diabetes', 'Hypertension', 'E11.649', 'I12.9'],
            ['55231', 'Diabetes', 'Hypertension', 'E11.65', 'I10'],
            ['206260', 'Diabetes', 'Hypertension', 'E11.9', 'I10'],
            ['190179', 'Hypertension', 'Afib', 'I12.9', 'N18.9'],
            ['74753', 'Diabetes', 'Hypertension', 'I11.9', 'I44.0'],
            ['48246', 'Hypertension', 'High Cholesterol', 'I13.10', 'I38'],
            ['134698', 'Diabetes', 'Hypertension', 'E11.9', 'I10'],
            ['206126', 'Hypertension', 'Afib', 'I11.9', 'I65.29'],
            ['68311', 'Diabetes', 'Hypertension', 'E11.22', 'I13.10'],
            ['110318', 'Diabetes', 'Hypertension', 'I10', 'E03.9'],
            ['131635', 'Diabetes', 'Hypertension', 'E11.65', 'I11.9'],
            ['214879', 'Diabetes', 'Hypertension', 'E11.22', 'I13.0'],
            ['48435', 'Hypertension', 'CAD/IHD', 'I11.9', 'I25.10'],
            ['105075', 'Hypertension', 'Anemia', 'I10', 'D649'],
            ['213599', 'High Cholesterol', 'BPH', 'E78.00', 'I65.29'],
            ['17177', 'Diabetes', 'Hypertension', 'E11.40', 'I10'],
            ['188932', 'Diabetes', 'Hypertension', 'E11.9', 'I10'],
            ['185540', 'Hypertension', 'Afib', 'I11.9', 'I25.10'],
            ['183152', 'Hypertension', 'High Cholesterol', 'I10', 'E78.5'],
            ['210770', 'Diabetes', 'Hypertension', 'E11.9', 'I10'],
            ['170381', 'Hypertension', 'Afib', 'I10', 'I48.91'],
            ['25785', 'Hypertension', 'Afib', 'I12.9', 'N18.3'],
            ['188795', 'Hypertension', 'High Cholesterol', 'I10', 'E78.00'],
            ['116320', 'Diabetes', 'Hypertension', 'I11.9', 'E11.9'],
            ['213313', 'Depression', 'Anemia', 'F32.9', 'D64.9'],
            ['121528', 'Hypertension', 'Depression', 'I10', 'F32.9'],
        ];

        $cpmProblems = CpmProblem::get()->keyBy('name');

        foreach ($corrections as $c) {
            //problem 1
            if ('Diabetes' == $c[1]) {
                Problem::where('name', 'like', '%diabetes%')
                    ->whereHas('patient.patientInfo', function ($q) use ($c) {
                        $q->where('mrn_number', '=', $c[0]);
                    })->update([
                        'icd_10_code' => $c[3],
                    ]);
            } else {
                Problem::where('cpm_problem_id', '=', $cpmProblems[$c[1]]->id)
                    ->whereHas('patient.patientInfo', function ($q) use ($c) {
                        $q->where('mrn_number', '=', $c[0]);
                    })->update([
                        'icd_10_code' => $c[3],
                    ]);
            }

            //problem 2
            Problem::where('cpm_problem_id', '=', $cpmProblems[$c[2]]->id)
                ->whereHas('patient.patientInfo', function ($q) use ($c) {
                    $q->where('mrn_number', '=', $c[0]);
                })->update([
                    'icd_10_code' => $c[4],
                ]);
        }
    }
}
