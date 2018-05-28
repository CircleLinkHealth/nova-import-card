<?php

use App\CLH\CCD\Importer\SnomedToCpmIcdMap;
use App\Enrollee;
use App\Models\CPM\CpmProblem;
use Illuminate\Database\Seeder;

class UpdateBHIProblems extends Seeder
{
    private $validBHIProblemIds = [];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->updateProblems();
    }

    public function updateProblems()
    {
        $defaultCarePlan = getDefaultCarePlanTemplate();

        foreach ($this->problems() as $name) {
            //Does a CPMProblem exist?
            $bhiProblem = CpmProblem::firstOrNew([
                'name' => $name,
            ]);

            $bhiProblem->is_behavioral = true;
            $bhiProblem->save();

            $this->validBHIProblemIds[] = $bhiProblem->id;

            if ( ! in_array($bhiProblem->id, $defaultCarePlan->cpmProblems->pluck('id')->all())) {
                $defaultCarePlan->cpmProblems()->attach($bhiProblem, [
                    'has_instruction' => true,
                    'page'            => 1,
                ]);
            }

            $this->existingBHIProblems()
                 ->where('simple_name', $bhiProblem->name)
                 ->map(function ($p) use ($bhiProblem) {
                     foreach (['cpm_problem_1', 'cpm_problem_2'] as $q) {
                         Enrollee::where($q, $p['id'])
                                 ->update([
                                     $q => $bhiProblem->id,
                                 ]);
                     }

                     $tables = [
                         'cpm_problems_users',
                         'ccd_problems',
                         'ccd_problem_logs',
                         'problem_imports',
                         'snomed_to_cpm_icd_maps',
                     ];

                     foreach ($tables as $table) {
                         DB::table($table)
                           ->whereCpmProblemId($p['id'])
                           ->update([
                               'cpm_problem_id' => $bhiProblem->id,
                           ]);
                     }

                     if ($p['default_icd_10_code']) {
                         SnomedToCpmIcdMap::updateOrCreate([
                             'icd_10_code' => $p['default_icd_10_code'],
                         ], [
                             'icd_10_name'    => $p['name'],
                             'cpm_problem_id' => $bhiProblem->id,
                         ]);
                     }
                 });

            $this->command->info("$name has been added");
        }


        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        CpmProblem::where('id', '>', 33)
                  ->whereNotIn('id', $this->validBHIProblemIds)
                  ->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $tables = [
            'cpm_problems_users',
            'snomed_to_cpm_icd_maps',
        ];

        foreach ($tables as $table) {
            DB::table($table)
              ->where('cpm_problem_id', '>', 33)
              ->whereNotIn('cpm_problem_id', $this->validBHIProblemIds)
              ->delete();
        }

        $tables = [
            'ccd_problems',
            'ccd_problem_logs',
            'problem_imports',
        ];

        foreach ($tables as $table) {
            DB::table($table)
              ->where('cpm_problem_id', '>', 33)
              ->whereNotIn('cpm_problem_id', $this->validBHIProblemIds)
              ->update([
                  'cpm_problem_id' => null,
              ]);
        }
    }


    /**
     * The array of problems to be added
     *
     * @return array
     */
    public function problems(): array
    {
        return [
            'Substance Abuse (ex-Alcoholism)',
            'Anxiety and Stress',
            'Depression',
            'Alcoholism',
            'Dementia',
            'Bipolar',
            'Psychosis & Schizophrenia',
            'Post-traumatic stress',
        ];
    }

    public function existingBHIProblems()
    {
        return collect([
            0   =>
                [
                    'id'                  => 34,
                    'name'                => 'Drug Use Disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => null,
                ],
            1   =>
                [
                    'id'                  => 35,
                    'name'                => 'Acrophobia',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F40.241',
                ],
            2   =>
                [
                    'id'                  => 36,
                    'name'                => 'Panic disorder ºepisodic paroxysmal anxiety» without agoraphobia',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F41.0',
                ],
            3   =>
                [
                    'id'                  => 37,
                    'name'                => 'Acute stress reaction',
                    'simple_name'         => 'Anxiety and Stress',
                    'default_icd_10_code' => 'F43.0',
                ],
            4   =>
                [
                    'id'                  => 38,
                    'name'                => 'Adjustment disorder with anxiety',
                    'simple_name'         => 'Anxiety and Stress',
                    'default_icd_10_code' => 'F43.22',
                ],
            5   =>
                [
                    'id'                  => 39,
                    'name'                => 'Adjustment disorder with depressed mood',
                    'simple_name'         => 'Depression',
                    'default_icd_10_code' => 'F43.21',
                ],
            6   =>
                [
                    'id'                  => 40,
                    'name'                => 'Adjustment disorder with disturbance of conduct',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F43.24',
                ],
            7   =>
                [
                    'id'                  => 41,
                    'name'                => 'Adjustment disorder with mixed anxiety and depressed mood',
                    'simple_name'         => 'Depression',
                    'default_icd_10_code' => 'F43.23',
                ],
            8   =>
                [
                    'id'                  => 42,
                    'name'                => 'Adjustment disorder with mixed disturbance of emotions and conduct',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F43.25',
                ],
            9   =>
                [
                    'id'                  => 43,
                    'name'                => 'Adjustment disorder with other symptoms',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F43.29',
                ],
            10  =>
                [
                    'id'                  => 44,
                    'name'                => 'Adjustment disorder, unspecified',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F43.20',
                ],
            11  =>
                [
                    'id'                  => 45,
                    'name'                => 'Avoidant/restrictive food intake disorder',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F50.82',
                ],
            12  =>
                [
                    'id'                  => 46,
                    'name'                => 'Adjustment insomnia',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F51.02',
                ],
            13  =>
                [
                    'id'                  => 47,
                    'name'                => 'Adult onset fluency disorder',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F98.5',
                ],
            14  =>
                [
                    'id'                  => 48,
                    'name'                => 'Agoraphobia with panic disorder',
                    'simple_name'         => 'Anxiety and Stress',
                    'default_icd_10_code' => 'F40.01',
                ],
            15  =>
                [
                    'id'                  => 49,
                    'name'                => 'Agoraphobia without panic disorder',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F40.02',
                ],
            16  =>
                [
                    'id'                  => 50,
                    'name'                => 'Agoraphobia, unspecified',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F40.00',
                ],
            17  =>
                [
                    'id'                  => 51,
                    'name'                => 'Alcohol abuse with alcohol-induced anxiety disorder',
                    'simple_name'         => 'Alcoholism',
                    'default_icd_10_code' => 'F10.180',
                ],
            18  =>
                [
                    'id'                  => 52,
                    'name'                => 'Alcohol abuse with alcohol-induced mood disorder',
                    'simple_name'         => 'Alcoholism',
                    'default_icd_10_code' => 'F10.14',
                ],
            19  =>
                [
                    'id'                  => 53,
                    'name'                => 'Alcohol abuse with alcohol-induced psychotic disorder with delusions',
                    'simple_name'         => 'Alcoholism',
                    'default_icd_10_code' => 'F10.150',
                ],
            20  =>
                [
                    'id'                  => 54,
                    'name'                => 'Alcohol abuse with alcohol-induced psychotic disorder with hallucinations',
                    'simple_name'         => 'Alcoholism',
                    'default_icd_10_code' => 'F10.151',
                ],
            21  =>
                [
                    'id'                  => 55,
                    'name'                => 'Alcohol abuse with alcohol-induced psychotic disorder, unspecified',
                    'simple_name'         => 'Alcoholism',
                    'default_icd_10_code' => 'F10.159',
                ],
            22  =>
                [
                    'id'                  => 56,
                    'name'                => 'Alcohol abuse with alcohol-induced sexual dysfunction',
                    'simple_name'         => 'Alcoholism',
                    'default_icd_10_code' => 'F10.181',
                ],
            23  =>
                [
                    'id'                  => 57,
                    'name'                => 'Alcohol abuse with alcohol-induced sleep disorder',
                    'simple_name'         => 'Alcoholism',
                    'default_icd_10_code' => 'F10.182',
                ],
            24  =>
                [
                    'id'                  => 58,
                    'name'                => 'Alcohol abuse with intoxication delirium',
                    'simple_name'         => 'Alcoholism',
                    'default_icd_10_code' => 'F10.121',
                ],
            25  =>
                [
                    'id'                  => 59,
                    'name'                => 'Alcohol abuse with other alcohol-induced disorder',
                    'simple_name'         => 'Alcoholism',
                    'default_icd_10_code' => 'F10.188',
                ],
            26  =>
                [
                    'id'                  => 60,
                    'name'                => 'Alcohol abuse with unspecified alcohol-induced disorder',
                    'simple_name'         => 'Alcoholism',
                    'default_icd_10_code' => 'F10.19',
                ],
            27  =>
                [
                    'id'                  => 61,
                    'name'                => 'Alcohol dependence with alcohol-induced anxiety disorder',
                    'simple_name'         => 'Alcoholism',
                    'default_icd_10_code' => 'F10.280',
                ],
            28  =>
                [
                    'id'                  => 62,
                    'name'                => 'Alcohol dependence with alcohol-induced mood disorder',
                    'simple_name'         => 'Alcoholism',
                    'default_icd_10_code' => 'F10.24',
                ],
            29  =>
                [
                    'id'                  => 63,
                    'name'                => 'Alcohol dependence with alcohol-induced persisting amnestic disorder',
                    'simple_name'         => 'Alcoholism',
                    'default_icd_10_code' => 'F10.26',
                ],
            30  =>
                [
                    'id'                  => 64,
                    'name'                => 'Alcohol dependence with alcohol-induced persisting dementia',
                    'simple_name'         => 'Alcoholism',
                    'default_icd_10_code' => 'F10.27',
                ],
            31  =>
                [
                    'id'                  => 65,
                    'name'                => 'Alcohol dependence with alcohol-induced psychotic disorder with delusions',
                    'simple_name'         => 'Alcoholism',
                    'default_icd_10_code' => 'F10.250',
                ],
            32  =>
                [
                    'id'                  => 66,
                    'name'                => 'Alcohol dependence with alcohol-induced psychotic disorder with hallucinations',
                    'simple_name'         => 'Alcoholism',
                    'default_icd_10_code' => 'F10.251',
                ],
            33  =>
                [
                    'id'                  => 67,
                    'name'                => 'Alcohol dependence with alcohol-induced psychotic disorder, unspecified',
                    'simple_name'         => 'Alcoholism',
                    'default_icd_10_code' => 'F10.259',
                ],
            34  =>
                [
                    'id'                  => 68,
                    'name'                => 'Alcohol dependence with alcohol-induced sexual dysfunction',
                    'simple_name'         => 'Alcoholism',
                    'default_icd_10_code' => 'F10.281',
                ],
            35  =>
                [
                    'id'                  => 69,
                    'name'                => 'Alcohol dependence with alcohol-induced sleep disorder',
                    'simple_name'         => 'Alcoholism',
                    'default_icd_10_code' => 'F10.282',
                ],
            36  =>
                [
                    'id'                  => 70,
                    'name'                => 'Alcohol dependence with intoxication delirium',
                    'simple_name'         => 'Alcoholism',
                    'default_icd_10_code' => 'F10.221',
                ],
            37  =>
                [
                    'id'                  => 71,
                    'name'                => 'Alcohol dependence with other alcohol-induced disorder',
                    'simple_name'         => 'Alcoholism',
                    'default_icd_10_code' => 'F10.288',
                ],
            38  =>
                [
                    'id'                  => 72,
                    'name'                => 'Alcohol dependence with unspecified alcohol-induced disorder',
                    'simple_name'         => 'Alcoholism',
                    'default_icd_10_code' => 'F10.29',
                ],
            39  =>
                [
                    'id'                  => 73,
                    'name'                => 'Alcohol dependence with withdrawal delirium',
                    'simple_name'         => 'Alcoholism',
                    'default_icd_10_code' => 'F10.231',
                ],
            40  =>
                [
                    'id'                  => 74,
                    'name'                => 'Alcohol dependence with withdrawal with perceptual disturbance',
                    'simple_name'         => 'Alcoholism',
                    'default_icd_10_code' => 'F10.232',
                ],
            41  =>
                [
                    'id'                  => 75,
                    'name'                => 'Alcohol dependence with withdrawal, unspecified',
                    'simple_name'         => 'Alcoholism',
                    'default_icd_10_code' => 'F10.239',
                ],
            42  =>
                [
                    'id'                  => 76,
                    'name'                => 'Alcohol use, unspecified with alcohol-induced anxiety disorder',
                    'simple_name'         => 'Alcoholism',
                    'default_icd_10_code' => 'F10.980',
                ],
            43  =>
                [
                    'id'                  => 77,
                    'name'                => 'Alcohol use, unspecified with alcohol-induced mood disorder',
                    'simple_name'         => 'Alcoholism',
                    'default_icd_10_code' => 'F10.94',
                ],
            44  =>
                [
                    'id'                  => 78,
                    'name'                => 'Alcohol use, unspecified with alcohol-induced persisting amnestic disorder',
                    'simple_name'         => 'Alcoholism',
                    'default_icd_10_code' => 'F10.96',
                ],
            45  =>
                [
                    'id'                  => 79,
                    'name'                => 'Alcohol use, unspecified with alcohol-induced persisting dementia',
                    'simple_name'         => 'Alcoholism',
                    'default_icd_10_code' => 'F10.97',
                ],
            46  =>
                [
                    'id'                  => 80,
                    'name'                => 'Alcohol use, unspecified with alcohol-induced psychotic disorder with delusions',
                    'simple_name'         => 'Alcoholism',
                    'default_icd_10_code' => 'F10.950',
                ],
            47  =>
                [
                    'id'                  => 81,
                    'name'                => 'Alcohol use, unspecified with alcohol-induced psychotic disorder with hallucinations',
                    'simple_name'         => 'Alcoholism',
                    'default_icd_10_code' => 'F10.951',
                ],
            48  =>
                [
                    'id'                  => 82,
                    'name'                => 'Alcohol use, unspecified with alcohol-induced psychotic disorder, unspecified',
                    'simple_name'         => 'Alcoholism',
                    'default_icd_10_code' => 'F10.959',
                ],
            49  =>
                [
                    'id'                  => 83,
                    'name'                => 'Alcohol use, unspecified with alcohol-induced sexual dysfunction',
                    'simple_name'         => 'Alcoholism',
                    'default_icd_10_code' => 'F10.981',
                ],
            50  =>
                [
                    'id'                  => 84,
                    'name'                => 'Alcohol use, unspecified with alcohol-induced sleep disorder',
                    'simple_name'         => 'Alcoholism',
                    'default_icd_10_code' => 'F10.982',
                ],
            51  =>
                [
                    'id'                  => 85,
                    'name'                => 'Alcohol use, unspecified with intoxication delirium',
                    'simple_name'         => 'Alcoholism',
                    'default_icd_10_code' => 'F10.921',
                ],
            52  =>
                [
                    'id'                  => 86,
                    'name'                => 'Alcohol use, unspecified with other alcohol-induced disorder',
                    'simple_name'         => 'Alcoholism',
                    'default_icd_10_code' => 'F10.988',
                ],
            53  =>
                [
                    'id'                  => 87,
                    'name'                => 'Amnestic disorder due to known physiological condition',
                    'simple_name'         => 'Dementia',
                    'default_icd_10_code' => 'F04',
                ],
            54  =>
                [
                    'id'                  => 88,
                    'name'                => 'Androphobia',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F40.290',
                ],
            55  =>
                [
                    'id'                  => 89,
                    'name'                => 'Anorexia nervosa, binge eating/purging type',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F50.02',
                ],
            56  =>
                [
                    'id'                  => 90,
                    'name'                => 'Anorexia nervosa, restricting type',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F50.01',
                ],
            57  =>
                [
                    'id'                  => 91,
                    'name'                => 'Anorexia nervosa, unspecified',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F50.00',
                ],
            58  =>
                [
                    'id'                  => 92,
                    'name'                => 'Antisocial personality disorder',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F60.2',
                ],
            59  =>
                [
                    'id'                  => 93,
                    'name'                => 'Anxiety disorder due to known physiological condition',
                    'simple_name'         => 'Anxiety and Stress',
                    'default_icd_10_code' => 'F06.4',
                ],
            60  =>
                [
                    'id'                  => 94,
                    'name'                => 'Anxiety disorder, unspecified',
                    'simple_name'         => 'Anxiety and Stress',
                    'default_icd_10_code' => 'F41.9',
                ],
            61  =>
                [
                    'id'                  => 95,
                    'name'                => 'Arachnophobia',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F40.210',
                ],
            62  =>
                [
                    'id'                  => 96,
                    'name'                => 'Asperger\'s syndrome',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F84.5',
                ],
            63  =>
                [
                    'id'                  => 97,
                    'name'                => 'Attention-deficit hyperactivity disorder, combined type',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F90.2',
                ],
            64  =>
                [
                    'id'                  => 98,
                    'name'                => 'Attention-deficit hyperactivity disorder, other type',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F90.8',
                ],
            65  =>
                [
                    'id'                  => 99,
                    'name'                => 'Attention-deficit hyperactivity disorder, predominantly hyperactive type',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F90.1',
                ],
            66  =>
                [
                    'id'                  => 100,
                    'name'                => 'Attention-deficit hyperactivity disorder, predominantly inattentive type',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F90.0',
                ],
            67  =>
                [
                    'id'                  => 101,
                    'name'                => 'Attention-deficit hyperactivity disorder, unspecified type',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F90.9',
                ],
            68  =>
                [
                    'id'                  => 102,
                    'name'                => 'Autistic disorder',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F84.0',
                ],
            69  =>
                [
                    'id'                  => 103,
                    'name'                => 'Avoidant personality disorder',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F60.6',
                ],
            70  =>
                [
                    'id'                  => 104,
                    'name'                => 'Binge eating disorder',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F50.81',
                ],
            71  =>
                [
                    'id'                  => 105,
                    'name'                => 'Bipolar II disorder',
                    'simple_name'         => 'Bipolar',
                    'default_icd_10_code' => 'F31.81',
                ],
            72  =>
                [
                    'id'                  => 106,
                    'name'                => 'Bipolar disorder, current episode depressed, mild',
                    'simple_name'         => 'Bipolar',
                    'default_icd_10_code' => 'F31.31',
                ],
            73  =>
                [
                    'id'                  => 107,
                    'name'                => 'Bipolar disorder, current episode depressed, mild or moderate severity, unspecified',
                    'simple_name'         => 'Bipolar',
                    'default_icd_10_code' => 'F31.30',
                ],
            74  =>
                [
                    'id'                  => 108,
                    'name'                => 'Bipolar disorder, current episode depressed, moderate',
                    'simple_name'         => 'Bipolar',
                    'default_icd_10_code' => 'F31.32',
                ],
            75  =>
                [
                    'id'                  => 109,
                    'name'                => 'Bipolar disorder, current episode depressed, severe, with psychotic features',
                    'simple_name'         => 'Bipolar',
                    'default_icd_10_code' => 'F31.5',
                ],
            76  =>
                [
                    'id'                  => 110,
                    'name'                => 'Bipolar disorder, current episode depressed, severe, without psychotic features',
                    'simple_name'         => 'Bipolar',
                    'default_icd_10_code' => 'F31.4',
                ],
            77  =>
                [
                    'id'                  => 111,
                    'name'                => 'Bipolar disorder, current episode hypomanic',
                    'simple_name'         => 'Bipolar',
                    'default_icd_10_code' => 'F31.0',
                ],
            78  =>
                [
                    'id'                  => 112,
                    'name'                => 'Bipolar disorder, current episode manic severe with psychotic features',
                    'simple_name'         => 'Bipolar',
                    'default_icd_10_code' => 'F31.2',
                ],
            79  =>
                [
                    'id'                  => 113,
                    'name'                => 'Bipolar disorder, current episode manic without psychotic features, mild',
                    'simple_name'         => 'Bipolar',
                    'default_icd_10_code' => 'F31.11',
                ],
            80  =>
                [
                    'id'                  => 114,
                    'name'                => 'Bipolar disorder, current episode manic without psychotic features, moderate',
                    'simple_name'         => 'Bipolar',
                    'default_icd_10_code' => 'F31.12',
                ],
            81  =>
                [
                    'id'                  => 115,
                    'name'                => 'Bipolar disorder, current episode manic without psychotic features, severe',
                    'simple_name'         => 'Bipolar',
                    'default_icd_10_code' => 'F31.13',
                ],
            82  =>
                [
                    'id'                  => 116,
                    'name'                => 'Bipolar disorder, current episode manic without psychotic features, unspecified',
                    'simple_name'         => 'Bipolar',
                    'default_icd_10_code' => 'F31.10',
                ],
            83  =>
                [
                    'id'                  => 117,
                    'name'                => 'Bipolar disorder, current episode mixed, mild',
                    'simple_name'         => 'Bipolar',
                    'default_icd_10_code' => 'F31.61',
                ],
            84  =>
                [
                    'id'                  => 118,
                    'name'                => 'Bipolar disorder, current episode mixed, moderate',
                    'simple_name'         => 'Bipolar',
                    'default_icd_10_code' => 'F31.62',
                ],
            85  =>
                [
                    'id'                  => 119,
                    'name'                => 'Bipolar disorder, current episode mixed, severe, with psychotic features',
                    'simple_name'         => 'Bipolar',
                    'default_icd_10_code' => 'F31.64',
                ],
            86  =>
                [
                    'id'                  => 120,
                    'name'                => 'Bipolar disorder, current episode mixed, severe, without psychotic features',
                    'simple_name'         => 'Bipolar',
                    'default_icd_10_code' => 'F31.63',
                ],
            87  =>
                [
                    'id'                  => 121,
                    'name'                => 'Bipolar disorder, current episode mixed, unspecified',
                    'simple_name'         => 'Bipolar',
                    'default_icd_10_code' => 'F31.60',
                ],
            88  =>
                [
                    'id'                  => 122,
                    'name'                => 'Bipolar disorder, currently in remission, most recent episode unspecified',
                    'simple_name'         => 'Bipolar',
                    'default_icd_10_code' => 'F31.70',
                ],
            89  =>
                [
                    'id'                  => 123,
                    'name'                => 'Bipolar disorder, in full remission, most recent episode depressed',
                    'simple_name'         => 'Bipolar',
                    'default_icd_10_code' => 'F31.76',
                ],
            90  =>
                [
                    'id'                  => 124,
                    'name'                => 'Bipolar disorder, in full remission, most recent episode hypomanic',
                    'simple_name'         => 'Bipolar',
                    'default_icd_10_code' => 'F31.72',
                ],
            91  =>
                [
                    'id'                  => 125,
                    'name'                => 'Bipolar disorder, in full remission, most recent episode manic',
                    'simple_name'         => 'Bipolar',
                    'default_icd_10_code' => 'F31.74',
                ],
            92  =>
                [
                    'id'                  => 126,
                    'name'                => 'Bipolar disorder, in full remission, most recent episode mixed',
                    'simple_name'         => 'Bipolar',
                    'default_icd_10_code' => 'F31.78',
                ],
            93  =>
                [
                    'id'                  => 127,
                    'name'                => 'Bipolar disorder, in partial remission, most recent episode depressed',
                    'simple_name'         => 'Bipolar',
                    'default_icd_10_code' => 'F31.75',
                ],
            94  =>
                [
                    'id'                  => 128,
                    'name'                => 'Bipolar disorder, in partial remission, most recent episode hypomanic',
                    'simple_name'         => 'Bipolar',
                    'default_icd_10_code' => 'F31.71',
                ],
            95  =>
                [
                    'id'                  => 129,
                    'name'                => 'Bipolar disorder, in partial remission, most recent episode manic',
                    'simple_name'         => 'Bipolar',
                    'default_icd_10_code' => 'F31.73',
                ],
            96  =>
                [
                    'id'                  => 130,
                    'name'                => 'Bipolar disorder, in partial remission, most recent episode mixed',
                    'simple_name'         => 'Bipolar',
                    'default_icd_10_code' => 'F31.77',
                ],
            97  =>
                [
                    'id'                  => 131,
                    'name'                => 'Bipolar disorder, unspecified',
                    'simple_name'         => 'Bipolar',
                    'default_icd_10_code' => 'F31.9',
                ],
            98  =>
                [
                    'id'                  => 132,
                    'name'                => 'Body dysmorphic disorder',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F45.22',
                ],
            99  =>
                [
                    'id'                  => 133,
                    'name'                => 'Borderline personality disorder',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F60.3',
                ],
            100 =>
                [
                    'id'                  => 134,
                    'name'                => 'Brief psychotic disorder',
                    'simple_name'         => 'Psychosis & Schizophrenia',
                    'default_icd_10_code' => 'F23',
                ],
            101 =>
                [
                    'id'                  => 135,
                    'name'                => 'Bulimia nervosa',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F50.2',
                ],
            102 =>
                [
                    'id'                  => 136,
                    'name'                => 'Cannabis abuse with cannabis-induced anxiety disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F12.180',
                ],
            103 =>
                [
                    'id'                  => 137,
                    'name'                => 'Cannabis abuse with intoxication delirium',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F12.121',
                ],
            104 =>
                [
                    'id'                  => 138,
                    'name'                => 'Cannabis abuse with intoxication with perceptual disturbance',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F12.122',
                ],
            105 =>
                [
                    'id'                  => 139,
                    'name'                => 'Cannabis abuse with other cannabis-induced disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F12.188',
                ],
            106 =>
                [
                    'id'                  => 140,
                    'name'                => 'Cannabis abuse with psychotic disorder with delusions',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F12.150',
                ],
            107 =>
                [
                    'id'                  => 141,
                    'name'                => 'Cannabis abuse with psychotic disorder with hallucinations',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F12.151',
                ],
            108 =>
                [
                    'id'                  => 142,
                    'name'                => 'Cannabis abuse with psychotic disorder, unspecified',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F12.159',
                ],
            109 =>
                [
                    'id'                  => 143,
                    'name'                => 'Cannabis abuse with unspecified cannabis-induced disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F12.19',
                ],
            110 =>
                [
                    'id'                  => 144,
                    'name'                => 'Cannabis dependence with cannabis-induced anxiety disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F12.280',
                ],
            111 =>
                [
                    'id'                  => 145,
                    'name'                => 'Cannabis dependence with intoxication delirium',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F12.221',
                ],
            112 =>
                [
                    'id'                  => 146,
                    'name'                => 'Cannabis dependence with intoxication with perceptual disturbance',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F12.222',
                ],
            113 =>
                [
                    'id'                  => 147,
                    'name'                => 'Cannabis dependence with other cannabis-induced disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F12.288',
                ],
            114 =>
                [
                    'id'                  => 148,
                    'name'                => 'Cannabis dependence with psychotic disorder with delusions',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F12.250',
                ],
            115 =>
                [
                    'id'                  => 149,
                    'name'                => 'Cannabis dependence with psychotic disorder with hallucinations',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F12.251',
                ],
            116 =>
                [
                    'id'                  => 150,
                    'name'                => 'Cannabis dependence with psychotic disorder, unspecified',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F12.259',
                ],
            117 =>
                [
                    'id'                  => 151,
                    'name'                => 'Cannabis dependence with unspecified cannabis-induced disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F12.29',
                ],
            118 =>
                [
                    'id'                  => 152,
                    'name'                => 'Cannabis use, unspecified with anxiety disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F12.980',
                ],
            119 =>
                [
                    'id'                  => 153,
                    'name'                => 'Cannabis use, unspecified with intoxication delirium',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F12.921',
                ],
            120 =>
                [
                    'id'                  => 154,
                    'name'                => 'Cannabis use, unspecified with intoxication with perceptual disturbance',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F12.922',
                ],
            121 =>
                [
                    'id'                  => 155,
                    'name'                => 'Cannabis use, unspecified with other cannabis-induced disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F12.988',
                ],
            122 =>
                [
                    'id'                  => 156,
                    'name'                => 'Cannabis use, unspecified with psychotic disorder with delusions',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F12.950',
                ],
            123 =>
                [
                    'id'                  => 157,
                    'name'                => 'Cannabis use, unspecified with psychotic disorder with hallucinations',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F12.951',
                ],
            124 =>
                [
                    'id'                  => 158,
                    'name'                => 'Cannabis use, unspecified with psychotic disorder, unspecified',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F12.959',
                ],
            125 =>
                [
                    'id'                  => 159,
                    'name'                => 'Catatonic disorder due to known physiological condition',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F06.1',
                ],
            126 =>
                [
                    'id'                  => 160,
                    'name'                => 'Catatonic schizophrenia',
                    'simple_name'         => 'Psychosis & Schizophrenia',
                    'default_icd_10_code' => 'F20.2',
                ],
            127 =>
                [
                    'id'                  => 161,
                    'name'                => 'Childhood emotional disorder, unspecified',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F93.9',
                ],
            128 =>
                [
                    'id'                  => 162,
                    'name'                => 'Childhood onset fluency disorder',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F80.81',
                ],
            129 =>
                [
                    'id'                  => 163,
                    'name'                => 'Chronic motor or vocal tic disorder',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F95.1',
                ],
            130 =>
                [
                    'id'                  => 164,
                    'name'                => 'Claustrophobia',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F40.240',
                ],
            131 =>
                [
                    'id'                  => 165,
                    'name'                => 'Cocaine abuse with cocaine-induced anxiety disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F14.180',
                ],
            132 =>
                [
                    'id'                  => 166,
                    'name'                => 'Cocaine abuse with cocaine-induced mood disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F14.14',
                ],
            133 =>
                [
                    'id'                  => 167,
                    'name'                => 'Cocaine abuse with cocaine-induced psychotic disorder with delusions',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F14.150',
                ],
            134 =>
                [
                    'id'                  => 168,
                    'name'                => 'Cocaine abuse with cocaine-induced psychotic disorder with hallucinations',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F14.151',
                ],
            135 =>
                [
                    'id'                  => 169,
                    'name'                => 'Cocaine abuse with cocaine-induced psychotic disorder, unspecified',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F14.159',
                ],
            136 =>
                [
                    'id'                  => 170,
                    'name'                => 'Cocaine abuse with cocaine-induced sexual dysfunction',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F14.181',
                ],
            137 =>
                [
                    'id'                  => 171,
                    'name'                => 'Cocaine abuse with cocaine-induced sleep disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F14.182',
                ],
            138 =>
                [
                    'id'                  => 172,
                    'name'                => 'Cocaine abuse with intoxication with delirium',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F14.121',
                ],
            139 =>
                [
                    'id'                  => 173,
                    'name'                => 'Cocaine abuse with intoxication with perceptual disturbance',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F14.122',
                ],
            140 =>
                [
                    'id'                  => 174,
                    'name'                => 'Cocaine abuse with other cocaine-induced disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F14.188',
                ],
            141 =>
                [
                    'id'                  => 175,
                    'name'                => 'Cocaine abuse with unspecified cocaine-induced disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F14.19',
                ],
            142 =>
                [
                    'id'                  => 176,
                    'name'                => 'Cocaine dependence with cocaine-induced anxiety disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F14.280',
                ],
            143 =>
                [
                    'id'                  => 177,
                    'name'                => 'Cocaine dependence with cocaine-induced mood disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F14.24',
                ],
            144 =>
                [
                    'id'                  => 178,
                    'name'                => 'Cocaine dependence with cocaine-induced psychotic disorder with delusions',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F14.250',
                ],
            145 =>
                [
                    'id'                  => 179,
                    'name'                => 'Cocaine dependence with cocaine-induced psychotic disorder with hallucinations',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F14.251',
                ],
            146 =>
                [
                    'id'                  => 180,
                    'name'                => 'Cocaine dependence with cocaine-induced psychotic disorder, unspecified',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F14.259',
                ],
            147 =>
                [
                    'id'                  => 181,
                    'name'                => 'Cocaine dependence with cocaine-induced sexual dysfunction',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F14.281',
                ],
            148 =>
                [
                    'id'                  => 182,
                    'name'                => 'Cocaine dependence with cocaine-induced sleep disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F14.282',
                ],
            149 =>
                [
                    'id'                  => 183,
                    'name'                => 'Cocaine dependence with intoxication delirium',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F14.221',
                ],
            150 =>
                [
                    'id'                  => 184,
                    'name'                => 'Cocaine dependence with intoxication with perceptual disturbance',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F14.222',
                ],
            151 =>
                [
                    'id'                  => 185,
                    'name'                => 'Cocaine dependence with other cocaine-induced disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F14.288',
                ],
            152 =>
                [
                    'id'                  => 186,
                    'name'                => 'Cocaine dependence with unspecified cocaine-induced disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F14.29',
                ],
            153 =>
                [
                    'id'                  => 187,
                    'name'                => 'Cocaine use, unspecified with cocaine-induced anxiety disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F14.980',
                ],
            154 =>
                [
                    'id'                  => 188,
                    'name'                => 'Cocaine use, unspecified with cocaine-induced mood disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F14.94',
                ],
            155 =>
                [
                    'id'                  => 189,
                    'name'                => 'Cocaine use, unspecified with cocaine-induced psychotic disorder with delusions',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F14.950',
                ],
            156 =>
                [
                    'id'                  => 190,
                    'name'                => 'Cocaine use, unspecified with cocaine-induced psychotic disorder with hallucinations',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F14.951',
                ],
            157 =>
                [
                    'id'                  => 191,
                    'name'                => 'Cocaine use, unspecified with cocaine-induced psychotic disorder, unspecified',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F14.959',
                ],
            158 =>
                [
                    'id'                  => 192,
                    'name'                => 'Cocaine use, unspecified with cocaine-induced sexual dysfunction',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F14.981',
                ],
            159 =>
                [
                    'id'                  => 193,
                    'name'                => 'Cocaine use, unspecified with cocaine-induced sleep disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F14.982',
                ],
            160 =>
                [
                    'id'                  => 194,
                    'name'                => 'Cocaine use, unspecified with intoxication delirium',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F14.921',
                ],
            161 =>
                [
                    'id'                  => 195,
                    'name'                => 'Cocaine use, unspecified with intoxication with perceptual disturbance',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F14.922',
                ],
            162 =>
                [
                    'id'                  => 196,
                    'name'                => 'Cocaine use, unspecified with other cocaine-induced disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F14.988',
                ],
            163 =>
                [
                    'id'                  => 197,
                    'name'                => 'Conduct disorder confined to family context',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F91.0',
                ],
            164 =>
                [
                    'id'                  => 198,
                    'name'                => 'Conduct disorder, adolescent-onset type',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F91.2',
                ],
            165 =>
                [
                    'id'                  => 199,
                    'name'                => 'Conduct disorder, childhood-onset type',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F91.1',
                ],
            166 =>
                [
                    'id'                  => 200,
                    'name'                => 'Conduct disorder, unspecified',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F91.9',
                ],
            167 =>
                [
                    'id'                  => 201,
                    'name'                => 'Conversion disorder with mixed symptom presentation',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F44.7',
                ],
            168 =>
                [
                    'id'                  => 202,
                    'name'                => 'Conversion disorder with motor symptom or deficit',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F44.4',
                ],
            169 =>
                [
                    'id'                  => 203,
                    'name'                => 'Conversion disorder with seizures or convulsions',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F44.5',
                ],
            170 =>
                [
                    'id'                  => 204,
                    'name'                => 'Conversion disorder with sensory symptom or deficit',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F44.6',
                ],
            171 =>
                [
                    'id'                  => 205,
                    'name'                => 'Cyclothymic disorder',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F34.0',
                ],
            172 =>
                [
                    'id'                  => 206,
                    'name'                => 'Delirium due to known physiological condition',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F05',
                ],
            173 =>
                [
                    'id'                  => 207,
                    'name'                => 'Delusional disorders',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F22',
                ],
            174 =>
                [
                    'id'                  => 208,
                    'name'                => 'Dementia in other diseases classified elsewhere with behavioral disturbance',
                    'simple_name'         => 'Dementia',
                    'default_icd_10_code' => 'F02.81',
                ],
            175 =>
                [
                    'id'                  => 209,
                    'name'                => 'Dementia in other diseases classified elsewhere without behavioral disturbance',
                    'simple_name'         => 'Dementia',
                    'default_icd_10_code' => 'F02.80',
                ],
            176 =>
                [
                    'id'                  => 210,
                    'name'                => 'Dependent personality disorder',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F60.7',
                ],
            177 =>
                [
                    'id'                  => 211,
                    'name'                => 'Depersonalization-derealization syndrome',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F48.1',
                ],
            178 =>
                [
                    'id'                  => 212,
                    'name'                => 'Disinhibited attachment disorder of childhood',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F94.2',
                ],
            179 =>
                [
                    'id'                  => 213,
                    'name'                => 'Disorganized schizophrenia',
                    'simple_name'         => 'Psychosis & Schizophrenia',
                    'default_icd_10_code' => 'F20.1',
                ],
            180 =>
                [
                    'id'                  => 214,
                    'name'                => 'Dispruptive mood dysregulation disorder',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F34.81',
                ],
            181 =>
                [
                    'id'                  => 215,
                    'name'                => 'Dissociative amnesia',
                    'simple_name'         => 'Dementia',
                    'default_icd_10_code' => 'F44.0',
                ],
            182 =>
                [
                    'id'                  => 216,
                    'name'                => 'Dissociative and conversion disorder, unspecified',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F44.9',
                ],
            183 =>
                [
                    'id'                  => 217,
                    'name'                => 'Dissociative fugue',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F44.1',
                ],
            184 =>
                [
                    'id'                  => 218,
                    'name'                => 'Dissociative identity disorder',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F44.81',
                ],
            185 =>
                [
                    'id'                  => 219,
                    'name'                => 'Dissociative stupor',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F44.2',
                ],
            186 =>
                [
                    'id'                  => 220,
                    'name'                => 'Dyspareunia not due to a substance or known physiological condition',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F52.6',
                ],
            187 =>
                [
                    'id'                  => 221,
                    'name'                => 'Dysthymic disorder',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F34.1',
                ],
            188 =>
                [
                    'id'                  => 222,
                    'name'                => 'Eating disorder, unspecified',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F50.9',
                ],
            189 =>
                [
                    'id'                  => 223,
                    'name'                => 'Encopresis not due to a substance or known physiological condition',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F98.1',
                ],
            190 =>
                [
                    'id'                  => 224,
                    'name'                => 'Enuresis not due to a substance or known physiological condition',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F98.0',
                ],
            191 =>
                [
                    'id'                  => 225,
                    'name'                => 'Excoriation (skin-picking) disorder',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F42.4',
                ],
            192 =>
                [
                    'id'                  => 226,
                    'name'                => 'Exhibitionism',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F65.2',
                ],
            193 =>
                [
                    'id'                  => 227,
                    'name'                => 'Factitious disorder with combined psychological and physical signs and symptoms',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F68.13',
                ],
            194 =>
                [
                    'id'                  => 228,
                    'name'                => 'Factitious disorder with predominantly physical signs and symptoms',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F68.12',
                ],
            195 =>
                [
                    'id'                  => 229,
                    'name'                => 'Factitious disorder with predominantly psychological signs and symptoms',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F68.11',
                ],
            196 =>
                [
                    'id'                  => 230,
                    'name'                => 'Factitious disorder, unspecified',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F68.10',
                ],
            197 =>
                [
                    'id'                  => 231,
                    'name'                => 'Fear of blood',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F40.230',
                ],
            198 =>
                [
                    'id'                  => 232,
                    'name'                => 'Fear of bridges',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F40.242',
                ],
            199 =>
                [
                    'id'                  => 233,
                    'name'                => 'Fear of flying',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F40.243',
                ],
            200 =>
                [
                    'id'                  => 234,
                    'name'                => 'Fear of injections and transfusions',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F40.231',
                ],
            201 =>
                [
                    'id'                  => 235,
                    'name'                => 'Fear of injury',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F40.233',
                ],
            202 =>
                [
                    'id'                  => 236,
                    'name'                => 'Fear of other medical care',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F40.232',
                ],
            203 =>
                [
                    'id'                  => 237,
                    'name'                => 'Fear of thunderstorms',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F40.220',
                ],
            204 =>
                [
                    'id'                  => 238,
                    'name'                => 'Female orgasmic disorder',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F52.31',
                ],
            205 =>
                [
                    'id'                  => 239,
                    'name'                => 'Female sexual arousal disorder',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F52.22',
                ],
            206 =>
                [
                    'id'                  => 240,
                    'name'                => 'Fetishism',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F65.0',
                ],
            207 =>
                [
                    'id'                  => 241,
                    'name'                => 'Frotteurism',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F65.81',
                ],
            208 =>
                [
                    'id'                  => 242,
                    'name'                => 'Gender identity disorder in adolescence and adulthood',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F64.1',
                ],
            209 =>
                [
                    'id'                  => 243,
                    'name'                => 'Gender identity disorder of childhood',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F64.2',
                ],
            210 =>
                [
                    'id'                  => 244,
                    'name'                => 'Gender identity disorder, unspecified',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F64.9',
                ],
            211 =>
                [
                    'id'                  => 245,
                    'name'                => 'Generalized anxiety disorder',
                    'simple_name'         => 'Anxiety and Stress',
                    'default_icd_10_code' => 'F41.1',
                ],
            212 =>
                [
                    'id'                  => 246,
                    'name'                => 'Gynephobia',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F40.291',
                ],
            213 =>
                [
                    'id'                  => 247,
                    'name'                => 'Hallucinogen abuse with hallucinogen persisting perception disorder (flashbacks)',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F16.183',
                ],
            214 =>
                [
                    'id'                  => 248,
                    'name'                => 'Hallucinogen abuse with hallucinogen-induced anxiety disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F16.180',
                ],
            215 =>
                [
                    'id'                  => 249,
                    'name'                => 'Hallucinogen abuse with hallucinogen-induced mood disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F16.14',
                ],
            216 =>
                [
                    'id'                  => 250,
                    'name'                => 'Hallucinogen abuse with hallucinogen-induced psychotic disorder with delusions',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F16.150',
                ],
            217 =>
                [
                    'id'                  => 251,
                    'name'                => 'Hallucinogen abuse with hallucinogen-induced psychotic disorder with hallucinations',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F16.151',
                ],
            218 =>
                [
                    'id'                  => 252,
                    'name'                => 'Hallucinogen abuse with hallucinogen-induced psychotic disorder, unspecified',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F16.159',
                ],
            219 =>
                [
                    'id'                  => 253,
                    'name'                => 'Hallucinogen abuse with intoxication with delirium',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F16.121',
                ],
            220 =>
                [
                    'id'                  => 254,
                    'name'                => 'Hallucinogen abuse with intoxication with perceptual disturbance',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F16.122',
                ],
            221 =>
                [
                    'id'                  => 255,
                    'name'                => 'Hallucinogen abuse with other hallucinogen-induced disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F16.188',
                ],
            222 =>
                [
                    'id'                  => 256,
                    'name'                => 'Hallucinogen abuse with unspecified hallucinogen-induced disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F16.19',
                ],
            223 =>
                [
                    'id'                  => 257,
                    'name'                => 'Hallucinogen dependence with hallucinogen persisting perception disorder (flashbacks)',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F16.283',
                ],
            224 =>
                [
                    'id'                  => 258,
                    'name'                => 'Hallucinogen dependence with hallucinogen-induced anxiety disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F16.280',
                ],
            225 =>
                [
                    'id'                  => 259,
                    'name'                => 'Hallucinogen dependence with hallucinogen-induced mood disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F16.24',
                ],
            226 =>
                [
                    'id'                  => 260,
                    'name'                => 'Hallucinogen dependence with hallucinogen-induced psychotic disorder with delusions',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F16.250',
                ],
            227 =>
                [
                    'id'                  => 261,
                    'name'                => 'Hallucinogen dependence with hallucinogen-induced psychotic disorder with hallucinations',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F16.251',
                ],
            228 =>
                [
                    'id'                  => 262,
                    'name'                => 'Hallucinogen dependence with hallucinogen-induced psychotic disorder, unspecified',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F16.259',
                ],
            229 =>
                [
                    'id'                  => 263,
                    'name'                => 'Hallucinogen dependence with intoxication with delirium',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F16.221',
                ],
            230 =>
                [
                    'id'                  => 264,
                    'name'                => 'Hallucinogen dependence with other hallucinogen-induced disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F16.288',
                ],
            231 =>
                [
                    'id'                  => 265,
                    'name'                => 'Hallucinogen dependence with unspecified hallucinogen-induced disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F16.29',
                ],
            232 =>
                [
                    'id'                  => 266,
                    'name'                => 'Hallucinogen use, unspecified with hallucinogen persisting perception disorder (flashbacks)',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F16.983',
                ],
            233 =>
                [
                    'id'                  => 267,
                    'name'                => 'Hallucinogen use, unspecified with hallucinogen-induced anxiety disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F16.980',
                ],
            234 =>
                [
                    'id'                  => 268,
                    'name'                => 'Hallucinogen use, unspecified with hallucinogen-induced mood disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F16.94',
                ],
            235 =>
                [
                    'id'                  => 269,
                    'name'                => 'Hallucinogen use, unspecified with hallucinogen-induced psychotic disorder with delusions',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F16.950',
                ],
            236 =>
                [
                    'id'                  => 270,
                    'name'                => 'Hallucinogen use, unspecified with hallucinogen-induced psychotic disorder with hallucinations',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F16.951',
                ],
            237 =>
                [
                    'id'                  => 271,
                    'name'                => 'Hallucinogen use, unspecified with hallucinogen-induced psychotic disorder, unspecified',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F16.959',
                ],
            238 =>
                [
                    'id'                  => 272,
                    'name'                => 'Hallucinogen use, unspecified with intoxication with delirium',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F16.921',
                ],
            239 =>
                [
                    'id'                  => 273,
                    'name'                => 'Hallucinogen use, unspecified with other hallucinogen-induced disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F16.988',
                ],
            240 =>
                [
                    'id'                  => 274,
                    'name'                => 'Histrionic personality disorder',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F60.4',
                ],
            241 =>
                [
                    'id'                  => 275,
                    'name'                => 'Hoarding disorder',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F42.3',
                ],
            242 =>
                [
                    'id'                  => 276,
                    'name'                => 'Hypoactive sexual desire disorder',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F52.0',
                ],
            243 =>
                [
                    'id'                  => 277,
                    'name'                => 'Hypochondriacal disorder, unspecified',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F45.20',
                ],
            244 =>
                [
                    'id'                  => 278,
                    'name'                => 'Hypochondriasis',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F45.21',
                ],
            245 =>
                [
                    'id'                  => 279,
                    'name'                => 'Impulse disorder, unspecified',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F63.9',
                ],
            246 =>
                [
                    'id'                  => 280,
                    'name'                => 'Inhalant abuse with inhalant-induced anxiety disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F18.180',
                ],
            247 =>
                [
                    'id'                  => 281,
                    'name'                => 'Inhalant abuse with inhalant-induced dementia',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F18.17',
                ],
            248 =>
                [
                    'id'                  => 282,
                    'name'                => 'Inhalant abuse with inhalant-induced mood disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F18.14',
                ],
            249 =>
                [
                    'id'                  => 283,
                    'name'                => 'Inhalant abuse with inhalant-induced psychotic disorder with delusions',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F18.150',
                ],
            250 =>
                [
                    'id'                  => 284,
                    'name'                => 'Inhalant abuse with inhalant-induced psychotic disorder with hallucinations',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F18.151',
                ],
            251 =>
                [
                    'id'                  => 285,
                    'name'                => 'Inhalant abuse with inhalant-induced psychotic disorder, unspecified',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F18.159',
                ],
            252 =>
                [
                    'id'                  => 286,
                    'name'                => 'Inhalant abuse with intoxication delirium',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F18.121',
                ],
            253 =>
                [
                    'id'                  => 287,
                    'name'                => 'Inhalant abuse with other inhalant-induced disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F18.188',
                ],
            254 =>
                [
                    'id'                  => 288,
                    'name'                => 'Inhalant abuse with unspecified inhalant-induced disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F18.19',
                ],
            255 =>
                [
                    'id'                  => 289,
                    'name'                => 'Inhalant dependence with inhalant-induced anxiety disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F18.280',
                ],
            256 =>
                [
                    'id'                  => 290,
                    'name'                => 'Inhalant dependence with inhalant-induced dementia',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F18.27',
                ],
            257 =>
                [
                    'id'                  => 291,
                    'name'                => 'Inhalant dependence with inhalant-induced mood disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F18.24',
                ],
            258 =>
                [
                    'id'                  => 292,
                    'name'                => 'Inhalant dependence with inhalant-induced psychotic disorder with delusions',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F18.250',
                ],
            259 =>
                [
                    'id'                  => 293,
                    'name'                => 'Inhalant dependence with inhalant-induced psychotic disorder with hallucinations',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F18.251',
                ],
            260 =>
                [
                    'id'                  => 294,
                    'name'                => 'Inhalant dependence with inhalant-induced psychotic disorder, unspecified',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F18.259',
                ],
            261 =>
                [
                    'id'                  => 295,
                    'name'                => 'Inhalant dependence with intoxication delirium',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F18.221',
                ],
            262 =>
                [
                    'id'                  => 296,
                    'name'                => 'Inhalant use, unspecified with inhalant-induced anxiety disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F18.980',
                ],
            263 =>
                [
                    'id'                  => 297,
                    'name'                => 'Inhalant use, unspecified with inhalant-induced mood disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F18.94',
                ],
            264 =>
                [
                    'id'                  => 298,
                    'name'                => 'Inhalant use, unspecified with inhalant-induced persisting dementia',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F18.97',
                ],
            265 =>
                [
                    'id'                  => 299,
                    'name'                => 'Inhalant use, unspecified with inhalant-induced psychotic disorder with delusions',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F18.950',
                ],
            266 =>
                [
                    'id'                  => 300,
                    'name'                => 'Inhalant use, unspecified with inhalant-induced psychotic disorder with hallucinations',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F18.951',
                ],
            267 =>
                [
                    'id'                  => 301,
                    'name'                => 'Inhalant use, unspecified with inhalant-induced psychotic disorder, unspecified',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F18.959',
                ],
            268 =>
                [
                    'id'                  => 302,
                    'name'                => 'Inhalant use, unspecified with intoxication with delirium',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F18.921',
                ],
            269 =>
                [
                    'id'                  => 303,
                    'name'                => 'Insomnia due to other mental disorder',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F51.05',
                ],
            270 =>
                [
                    'id'                  => 304,
                    'name'                => 'Insufficient sleep syndrome',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F51.12',
                ],
            271 =>
                [
                    'id'                  => 305,
                    'name'                => 'Intermittent explosive disorder',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F63.81',
                ],
            272 =>
                [
                    'id'                  => 306,
                    'name'                => 'Kleptomania',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F63.2',
                ],
            273 =>
                [
                    'id'                  => 307,
                    'name'                => 'Major depressive disorder, recurrent severe without psychotic features',
                    'simple_name'         => 'Depression',
                    'default_icd_10_code' => 'F33.2',
                ],
            274 =>
                [
                    'id'                  => 308,
                    'name'                => 'Major depressive disorder, recurrent, in full remission',
                    'simple_name'         => 'Depression',
                    'default_icd_10_code' => 'F33.42',
                ],
            275 =>
                [
                    'id'                  => 309,
                    'name'                => 'Major depressive disorder, recurrent, in partial remission',
                    'simple_name'         => 'Depression',
                    'default_icd_10_code' => 'F33.41',
                ],
            276 =>
                [
                    'id'                  => 310,
                    'name'                => 'Major depressive disorder, recurrent, in remission, unspecified',
                    'simple_name'         => 'Depression',
                    'default_icd_10_code' => 'F33.40',
                ],
            277 =>
                [
                    'id'                  => 311,
                    'name'                => 'Major depressive disorder, recurrent, mild',
                    'simple_name'         => 'Depression',
                    'default_icd_10_code' => 'F33.0',
                ],
            278 =>
                [
                    'id'                  => 312,
                    'name'                => 'Major depressive disorder, recurrent, moderate',
                    'simple_name'         => 'Depression',
                    'default_icd_10_code' => 'F33.1',
                ],
            279 =>
                [
                    'id'                  => 313,
                    'name'                => 'Major depressive disorder, recurrent, severe with psychotic symptoms',
                    'simple_name'         => 'Depression',
                    'default_icd_10_code' => 'F33.3',
                ],
            280 =>
                [
                    'id'                  => 314,
                    'name'                => 'Major depressive disorder, recurrent, unspecified',
                    'simple_name'         => 'Depression',
                    'default_icd_10_code' => 'F33.9',
                ],
            281 =>
                [
                    'id'                  => 315,
                    'name'                => 'Major depressive disorder, single episode, in full remission',
                    'simple_name'         => 'Depression',
                    'default_icd_10_code' => 'F32.5',
                ],
            282 =>
                [
                    'id'                  => 316,
                    'name'                => 'Major depressive disorder, single episode, in partial remission',
                    'simple_name'         => 'Depression',
                    'default_icd_10_code' => 'F32.4',
                ],
            283 =>
                [
                    'id'                  => 317,
                    'name'                => 'Major depressive disorder, single episode, mild',
                    'simple_name'         => 'Depression',
                    'default_icd_10_code' => 'F32.0',
                ],
            284 =>
                [
                    'id'                  => 318,
                    'name'                => 'Major depressive disorder, single episode, moderate',
                    'simple_name'         => 'Depression',
                    'default_icd_10_code' => 'F32.1',
                ],
            285 =>
                [
                    'id'                  => 319,
                    'name'                => 'Major depressive disorder, single episode, severe with psychotic features',
                    'simple_name'         => 'Depression',
                    'default_icd_10_code' => 'F32.3',
                ],
            286 =>
                [
                    'id'                  => 320,
                    'name'                => 'Major depressive disorder, single episode, severe without psychotic features',
                    'simple_name'         => 'Depression',
                    'default_icd_10_code' => 'F32.2',
                ],
            287 =>
                [
                    'id'                  => 321,
                    'name'                => 'Major depressive disorder, single episode, unspecified',
                    'simple_name'         => 'Depression',
                    'default_icd_10_code' => 'F32.9',
                ],
            288 =>
                [
                    'id'                  => 322,
                    'name'                => 'Male erectile disorder',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F52.21',
                ],
            289 =>
                [
                    'id'                  => 323,
                    'name'                => 'Male orgasmic disorder',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F52.32',
                ],
            290 =>
                [
                    'id'                  => 324,
                    'name'                => 'Manic episode in full remission',
                    'simple_name'         => 'Bipolar',
                    'default_icd_10_code' => 'F30.4',
                ],
            291 =>
                [
                    'id'                  => 325,
                    'name'                => 'Manic episode in partial remission',
                    'simple_name'         => 'Bipolar',
                    'default_icd_10_code' => 'F30.3',
                ],
            292 =>
                [
                    'id'                  => 326,
                    'name'                => 'Manic episode without psychotic symptoms, mild',
                    'simple_name'         => 'Bipolar',
                    'default_icd_10_code' => 'F30.11',
                ],
            293 =>
                [
                    'id'                  => 327,
                    'name'                => 'Manic episode without psychotic symptoms, moderate',
                    'simple_name'         => 'Bipolar',
                    'default_icd_10_code' => 'F30.12',
                ],
            294 =>
                [
                    'id'                  => 328,
                    'name'                => 'Manic episode without psychotic symptoms, unspecified',
                    'simple_name'         => 'Bipolar',
                    'default_icd_10_code' => 'F30.10',
                ],
            295 =>
                [
                    'id'                  => 329,
                    'name'                => 'Manic episode, severe with psychotic symptoms',
                    'simple_name'         => 'Bipolar',
                    'default_icd_10_code' => 'F30.2',
                ],
            296 =>
                [
                    'id'                  => 330,
                    'name'                => 'Manic episode, severe, without psychotic symptoms',
                    'simple_name'         => 'Bipolar',
                    'default_icd_10_code' => 'F30.13',
                ],
            297 =>
                [
                    'id'                  => 331,
                    'name'                => 'Manic episode, unspecified',
                    'simple_name'         => 'Bipolar',
                    'default_icd_10_code' => 'F30.9',
                ],
            298 =>
                [
                    'id'                  => 332,
                    'name'                => 'Mixed obsessional thoughts and acts',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F42.2',
                ],
            299 =>
                [
                    'id'                  => 333,
                    'name'                => 'Mood disorder due to known physiological condition with depressive features',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F06.31',
                ],
            300 =>
                [
                    'id'                  => 334,
                    'name'                => 'Mood disorder due to known physiological condition with major depressive-like episode',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F06.32',
                ],
            301 =>
                [
                    'id'                  => 335,
                    'name'                => 'Mood disorder due to known physiological condition with manic features',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F06.33',
                ],
            302 =>
                [
                    'id'                  => 336,
                    'name'                => 'Mood disorder due to known physiological condition with mixed features',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F06.34',
                ],
            303 =>
                [
                    'id'                  => 337,
                    'name'                => 'Mood disorder due to known physiological condition, unspecified',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F06.30',
                ],
            304 =>
                [
                    'id'                  => 338,
                    'name'                => 'Narcissistic personality disorder',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F60.81',
                ],
            305 =>
                [
                    'id'                  => 339,
                    'name'                => 'Nightmare disorder',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F51.5',
                ],
            306 =>
                [
                    'id'                  => 340,
                    'name'                => 'Nonpsychotic mental disorder, unspecified',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F48.9',
                ],
            307 =>
                [
                    'id'                  => 341,
                    'name'                => 'Obsessive-compulsive disorder',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F42',
                ],
            308 =>
                [
                    'id'                  => 342,
                    'name'                => 'Obsessive-compulsive personality disorder',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F60.5',
                ],
            309 =>
                [
                    'id'                  => 343,
                    'name'                => 'Opioid abuse with intoxication delirium',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F11.121',
                ],
            310 =>
                [
                    'id'                  => 344,
                    'name'                => 'Opioid abuse with intoxication with perceptual disturbance',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F11.122',
                ],
            311 =>
                [
                    'id'                  => 345,
                    'name'                => 'Opioid abuse with opioid-induced mood disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F11.14',
                ],
            312 =>
                [
                    'id'                  => 346,
                    'name'                => 'Opioid abuse with opioid-induced psychotic disorder with delusions',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F11.150',
                ],
            313 =>
                [
                    'id'                  => 347,
                    'name'                => 'Opioid abuse with opioid-induced psychotic disorder with hallucinations',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F11.151',
                ],
            314 =>
                [
                    'id'                  => 348,
                    'name'                => 'Opioid abuse with opioid-induced psychotic disorder, unspecified',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F11.159',
                ],
            315 =>
                [
                    'id'                  => 349,
                    'name'                => 'Opioid abuse with opioid-induced sexual dysfunction',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F11.181',
                ],
            316 =>
                [
                    'id'                  => 350,
                    'name'                => 'Opioid abuse with opioid-induced sleep disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F11.182',
                ],
            317 =>
                [
                    'id'                  => 351,
                    'name'                => 'Opioid abuse with other opioid-induced disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F11.188',
                ],
            318 =>
                [
                    'id'                  => 352,
                    'name'                => 'Opioid abuse with unspecified opioid-induced disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F11.19',
                ],
            319 =>
                [
                    'id'                  => 353,
                    'name'                => 'Opioid dependence with intoxication delirium',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F11.221',
                ],
            320 =>
                [
                    'id'                  => 354,
                    'name'                => 'Opioid dependence with opioid-induced mood disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F11.24',
                ],
            321 =>
                [
                    'id'                  => 355,
                    'name'                => 'Opioid dependence with opioid-induced psychotic disorder with delusions',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F11.250',
                ],
            322 =>
                [
                    'id'                  => 356,
                    'name'                => 'Opioid dependence with opioid-induced psychotic disorder with hallucinations',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F11.251',
                ],
            323 =>
                [
                    'id'                  => 357,
                    'name'                => 'Opioid dependence with opioid-induced psychotic disorder, unspecified',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F11.259',
                ],
            324 =>
                [
                    'id'                  => 358,
                    'name'                => 'Opioid dependence with opioid-induced sexual dysfunction',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F11.281',
                ],
            325 =>
                [
                    'id'                  => 359,
                    'name'                => 'Opioid dependence with opioid-induced sleep disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F11.282',
                ],
            326 =>
                [
                    'id'                  => 360,
                    'name'                => 'Opioid dependence with other opioid-induced disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F11.288',
                ],
            327 =>
                [
                    'id'                  => 361,
                    'name'                => 'Opioid dependence with unspecified opioid-induced disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F11.29',
                ],
            328 =>
                [
                    'id'                  => 362,
                    'name'                => 'Opioid use, unspecified with intoxication delirium',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F11.921',
                ],
            329 =>
                [
                    'id'                  => 363,
                    'name'                => 'Opioid use, unspecified with intoxication with perceptual disturbance',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F11.922',
                ],
            330 =>
                [
                    'id'                  => 364,
                    'name'                => 'Opioid use, unspecified with opioid-induced mood disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F11.94',
                ],
            331 =>
                [
                    'id'                  => 365,
                    'name'                => 'Opioid use, unspecified with opioid-induced psychotic disorder with delusions',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F11.950',
                ],
            332 =>
                [
                    'id'                  => 366,
                    'name'                => 'Opioid use, unspecified with opioid-induced psychotic disorder with hallucinations',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F11.951',
                ],
            333 =>
                [
                    'id'                  => 367,
                    'name'                => 'Opioid use, unspecified with opioid-induced psychotic disorder, unspecified',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F11.959',
                ],
            334 =>
                [
                    'id'                  => 368,
                    'name'                => 'Opioid use, unspecified with opioid-induced sexual dysfunction',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F11.981',
                ],
            335 =>
                [
                    'id'                  => 369,
                    'name'                => 'Opioid use, unspecified with opioid-induced sleep disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F11.982',
                ],
            336 =>
                [
                    'id'                  => 370,
                    'name'                => 'Opioid use, unspecified with other opioid-induced disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F11.988',
                ],
            337 =>
                [
                    'id'                  => 371,
                    'name'                => 'Oppositional defiant disorder',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F91.3',
                ],
            338 =>
                [
                    'id'                  => 372,
                    'name'                => 'Other animal type phobia',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F40.218',
                ],
            339 =>
                [
                    'id'                  => 373,
                    'name'                => 'Other bipolar disorder',
                    'simple_name'         => 'Bipolar',
                    'default_icd_10_code' => 'F31.89',
                ],
            340 =>
                [
                    'id'                  => 374,
                    'name'                => 'Other childhood disintegrative disorder',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F84.3',
                ],
            341 =>
                [
                    'id'                  => 375,
                    'name'                => 'Other childhood disorders of social functioning',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F94.8',
                ],
            342 =>
                [
                    'id'                  => 376,
                    'name'                => 'Other childhood emotional disorders',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F93.8',
                ],
            343 =>
                [
                    'id'                  => 377,
                    'name'                => 'Other conduct disorders',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F91.8',
                ],
            344 =>
                [
                    'id'                  => 378,
                    'name'                => 'Other depressive episodes',
                    'simple_name'         => 'Depression',
                    'default_icd_10_code' => 'F32.8',
                ],
            345 =>
                [
                    'id'                  => 379,
                    'name'                => 'Other developmental disorders of speech and language',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F80.89',
                ],
            346 =>
                [
                    'id'                  => 380,
                    'name'                => 'Other disorders of psychological development',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F88',
                ],
            347 =>
                [
                    'id'                  => 381,
                    'name'                => 'Other eating disorders',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F50.8',
                ],
            348 =>
                [
                    'id'                  => 382,
                    'name'                => 'Other feeding disorders of infancy and early childhood',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F98.29',
                ],
            349 =>
                [
                    'id'                  => 383,
                    'name'                => 'Other gender identity disorders',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F64.8',
                ],
            350 =>
                [
                    'id'                  => 384,
                    'name'                => 'Other hypochondriacal disorders',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F45.29',
                ],
            351 =>
                [
                    'id'                  => 385,
                    'name'                => 'Other impulse disorders',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F63.8',
                ],
            352 =>
                [
                    'id'                  => 386,
                    'name'                => 'Other insomnia not due to a substance or known physiological condition',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F51.09',
                ],
            353 =>
                [
                    'id'                  => 387,
                    'name'                => 'Other obsessive compulsive disorder',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F42.8',
                ],
            354 =>
                [
                    'id'                  => 388,
                    'name'                => 'Other manic episodes',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F30.8',
                ],
            355 =>
                [
                    'id'                  => 389,
                    'name'                => 'Other mixed anxiety disorders',
                    'simple_name'         => 'Anxiety and Stress',
                    'default_icd_10_code' => 'F41.3',
                ],
            356 =>
                [
                    'id'                  => 390,
                    'name'                => 'Other natural environment type phobia',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F40.228',
                ],
            357 =>
                [
                    'id'                  => 391,
                    'name'                => 'Other paraphilias',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F65.89',
                ],
            358 =>
                [
                    'id'                  => 392,
                    'name'                => 'Other persistent mood ºaffective» disorders',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F34.8',
                ],
            359 =>
                [
                    'id'                  => 393,
                    'name'                => 'Other personality and behavioral disorders due to known physiological condition',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F07.89',
                ],
            360 =>
                [
                    'id'                  => 394,
                    'name'                => 'Other pervasive developmental disorders',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F84.8',
                ],
            361 =>
                [
                    'id'                  => 395,
                    'name'                => 'Other phobic anxiety disorders',
                    'simple_name'         => 'Anxiety and Stress',
                    'default_icd_10_code' => 'F40.8',
                ],
            362 =>
                [
                    'id'                  => 396,
                    'name'                => 'Other psychoactive substance abuse with intoxication delirium',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F19.121',
                ],
            363 =>
                [
                    'id'                  => 397,
                    'name'                => 'Other psychoactive substance abuse with intoxication with perceptual disturbances',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F19.122',
                ],
            364 =>
                [
                    'id'                  => 398,
                    'name'                => 'Other psychoactive substance abuse with other psychoactive substance-induced disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F19.188',
                ],
            365 =>
                [
                    'id'                  => 399,
                    'name'                => 'Other psychoactive substance abuse with psychoactive substance-induced anxiety disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F19.180',
                ],
            366 =>
                [
                    'id'                  => 400,
                    'name'                => 'Other psychoactive substance abuse with psychoactive substance-induced mood disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F19.14',
                ],
            367 =>
                [
                    'id'                  => 401,
                    'name'                => 'Other psychoactive substance abuse with psychoactive substance-induced persisting amnestic disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F19.16',
                ],
            368 =>
                [
                    'id'                  => 402,
                    'name'                => 'Other psychoactive substance abuse with psychoactive substance-induced persisting dementia',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F19.17',
                ],
            369 =>
                [
                    'id'                  => 403,
                    'name'                => 'Other psychoactive substance abuse with psychoactive substance-induced psychotic disorder with delus',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F19.150',
                ],
            370 =>
                [
                    'id'                  => 404,
                    'name'                => 'Other psychoactive substance abuse with psychoactive substance-induced psychotic disorder with hallu',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F19.151',
                ],
            371 =>
                [
                    'id'                  => 405,
                    'name'                => 'Other psychoactive substance abuse with psychoactive substance-induced psychotic disorder, unspecifi',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F19.159',
                ],
            372 =>
                [
                    'id'                  => 406,
                    'name'                => 'Other psychoactive substance abuse with psychoactive substance-induced sexual dysfunction',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F19.181',
                ],
            373 =>
                [
                    'id'                  => 407,
                    'name'                => 'Other psychoactive substance abuse with psychoactive substance-induced sleep disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F19.182',
                ],
            374 =>
                [
                    'id'                  => 408,
                    'name'                => 'Other psychoactive substance abuse with unspecified psychoactive substance-induced disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F19.19',
                ],
            375 =>
                [
                    'id'                  => 409,
                    'name'                => 'Other psychoactive substance dependence with intoxication delirium',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F19.221',
                ],
            376 =>
                [
                    'id'                  => 410,
                    'name'                => 'Other psychoactive substance dependence with intoxication with perceptual disturbance',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F19.222',
                ],
            377 =>
                [
                    'id'                  => 411,
                    'name'                => 'Other psychoactive substance dependence with other psychoactive substance-induced disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F19.288',
                ],
            378 =>
                [
                    'id'                  => 412,
                    'name'                => 'Other psychoactive substance dependence with psychoactive substance-induced anxiety disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F19.280',
                ],
            379 =>
                [
                    'id'                  => 413,
                    'name'                => 'Other psychoactive substance dependence with psychoactive substance-induced mood disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F19.24',
                ],
            380 =>
                [
                    'id'                  => 414,
                    'name'                => 'Other psychoactive substance dependence with psychoactive substance-induced persisting amnestic diso',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F19.26',
                ],
            381 =>
                [
                    'id'                  => 415,
                    'name'                => 'Other psychoactive substance dependence with psychoactive substance-induced persisting dementia',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F19.27',
                ],
            382 =>
                [
                    'id'                  => 416,
                    'name'                => 'Other psychoactive substance dependence with psychoactive substance-induced psychotic disorder with',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F19.251',
                ],
            383 =>
                [
                    'id'                  => 417,
                    'name'                => 'Other psychoactive substance dependence with psychoactive substance-induced psychotic disorder, unsp',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F19.259',
                ],
            384 =>
                [
                    'id'                  => 418,
                    'name'                => 'Other psychoactive substance dependence with psychoactive substance-induced sexual dysfunction',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F19.281',
                ],
            385 =>
                [
                    'id'                  => 419,
                    'name'                => 'Other psychoactive substance dependence with psychoactive substance-induced sleep disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F19.282',
                ],
            386 =>
                [
                    'id'                  => 420,
                    'name'                => 'Other psychoactive substance dependence with unspecified psychoactive substance-induced disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F19.29',
                ],
            387 =>
                [
                    'id'                  => 421,
                    'name'                => 'Other psychoactive substance dependence with withdrawal delirium',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F19.231',
                ],
            388 =>
                [
                    'id'                  => 422,
                    'name'                => 'Other psychoactive substance dependence with withdrawal with perceptual disturbance',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F19.232',
                ],
            389 =>
                [
                    'id'                  => 423,
                    'name'                => 'Other psychoactive substance dependence with withdrawal, unspecified',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F19.239',
                ],
            390 =>
                [
                    'id'                  => 424,
                    'name'                => 'Other psychoactive substance use, unspecified with intoxication with delirium',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F19.921',
                ],
            391 =>
                [
                    'id'                  => 425,
                    'name'                => 'Other psychoactive substance use, unspecified with intoxication with perceptual disturbance',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F19.922',
                ],
            392 =>
                [
                    'id'                  => 426,
                    'name'                => 'Other psychoactive substance use, unspecified with other psychoactive substance-induced disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F19.988',
                ],
            393 =>
                [
                    'id'                  => 427,
                    'name'                => 'Other psychoactive substance use, unspecified with psychoactive substanceinduced anxiety disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F19.980',
                ],
            394 =>
                [
                    'id'                  => 428,
                    'name'                => 'Other psychoactive substance use, unspecified with psychoactive substanceinduced mood disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F19.94',
                ],
            395 =>
                [
                    'id'                  => 429,
                    'name'                => 'Other psychoactive substance use, unspecified with psychoactive substanceinduced persisting amnesti',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F19.96',
                ],
            396 =>
                [
                    'id'                  => 430,
                    'name'                => 'Other psychoactive substance use, unspecified with psychoactive substanceinduced persisting dementi',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F19.97',
                ],
            397 =>
                [
                    'id'                  => 431,
                    'name'                => 'Other psychoactive substance use, unspecified with psychoactive substanceinduced psychotic disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F19.959',
                ],
            398 =>
                [
                    'id'                  => 432,
                    'name'                => 'Other psychoactive substance use, unspecified with psychoactive substanceinduced sexual dysfunction',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F19.981',
                ],
            399 =>
                [
                    'id'                  => 433,
                    'name'                => 'Other psychoactive substance use, unspecified with psychoactive substanceinduced sleep disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F19.982',
                ],
            400 =>
                [
                    'id'                  => 434,
                    'name'                => 'Other psychoactive substance use, unspecified with withdrawal delirium',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F19.931',
                ],
            401 =>
                [
                    'id'                  => 435,
                    'name'                => 'Other psychoactive substance use, unspecified with withdrawal with perceptual disturbance',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F19.932',
                ],
            402 =>
                [
                    'id'                  => 436,
                    'name'                => 'Other psychotic disorder not due to a substance or known physiological condition',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F28',
                ],
            403 =>
                [
                    'id'                  => 437,
                    'name'                => 'Other reactions to severe stress',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F43.8',
                ],
            404 =>
                [
                    'id'                  => 438,
                    'name'                => 'Other recurrent depressive disorders',
                    'simple_name'         => 'Depression',
                    'default_icd_10_code' => 'F33.8',
                ],
            405 =>
                [
                    'id'                  => 439,
                    'name'                => 'Other schizoaffective disorders',
                    'simple_name'         => 'Psychosis & Schizophrenia',
                    'default_icd_10_code' => 'F25.8',
                ],
            406 =>
                [
                    'id'                  => 440,
                    'name'                => 'Other schizophrenia',
                    'simple_name'         => 'Psychosis & Schizophrenia',
                    'default_icd_10_code' => 'F20.89',
                ],
            407 =>
                [
                    'id'                  => 441,
                    'name'                => 'Other sexual disorders',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F66',
                ],
            408 =>
                [
                    'id'                  => 442,
                    'name'                => 'Other sexual dysfunction not due to a substance or known physiological condition',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F52.8',
                ],
            409 =>
                [
                    'id'                  => 443,
                    'name'                => 'Other situational type phobia',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F40.248',
                ],
            410 =>
                [
                    'id'                  => 444,
                    'name'                => 'Other sleep disorders not due to a substance or known physiological condition',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F51.8',
                ],
            411 =>
                [
                    'id'                  => 445,
                    'name'                => 'Other somatoform disorders',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F45.8',
                ],
            412 =>
                [
                    'id'                  => 446,
                    'name'                => 'Other specified depressive episodes',
                    'simple_name'         => 'Depression',
                    'default_icd_10_code' => 'F32.89',
                ],
            413 =>
                [
                    'id'                  => 447,
                    'name'                => 'Other specified eating disorder',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F50.89',
                ],
            414 =>
                [
                    'id'                  => 448,
                    'name'                => 'Other specified persistent mood disorders',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F34.89',
                ],
            415 =>
                [
                    'id'                  => 449,
                    'name'                => 'Other specific personality disorders',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F60.89',
                ],
            416 =>
                [
                    'id'                  => 450,
                    'name'                => 'Other specified anxiety disorders',
                    'simple_name'         => 'Anxiety and Stress',
                    'default_icd_10_code' => 'F41.8',
                ],
            417 =>
                [
                    'id'                  => 451,
                    'name'                => 'Other specified behavioral and emotional disorders with onset usually occurring in childhood and ado',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F98.8',
                ],
            418 =>
                [
                    'id'                  => 452,
                    'name'                => 'Other specified disorders of adult personality and behavior',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F68.8',
                ],
            419 =>
                [
                    'id'                  => 453,
                    'name'                => 'Other specified mental disorders due to known physiological condition',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F06.8',
                ],
            420 =>
                [
                    'id'                  => 454,
                    'name'                => 'Other specified phobia',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F40.298',
                ],
            421 =>
                [
                    'id'                  => 455,
                    'name'                => 'Other stimulant abuse with intoxication delirium',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F15.121',
                ],
            422 =>
                [
                    'id'                  => 456,
                    'name'                => 'Other stimulant abuse with intoxication with perceptual disturbance',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F15.122',
                ],
            423 =>
                [
                    'id'                  => 457,
                    'name'                => 'Other stimulant abuse with other stimulant-induced disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F15.188',
                ],
            424 =>
                [
                    'id'                  => 458,
                    'name'                => 'Other stimulant abuse with stimulant-induced anxiety disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F15.180',
                ],
            425 =>
                [
                    'id'                  => 459,
                    'name'                => 'Other stimulant abuse with stimulant-induced mood disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F15.14',
                ],
            426 =>
                [
                    'id'                  => 460,
                    'name'                => 'Other stimulant abuse with stimulant-induced psychotic disorder with delusions',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F15.150',
                ],
            427 =>
                [
                    'id'                  => 461,
                    'name'                => 'Other stimulant abuse with stimulant-induced psychotic disorder with hallucinations',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F15.151',
                ],
            428 =>
                [
                    'id'                  => 462,
                    'name'                => 'Other stimulant abuse with stimulant-induced psychotic disorder, unspecified',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F15.159',
                ],
            429 =>
                [
                    'id'                  => 463,
                    'name'                => 'Other stimulant abuse with stimulant-induced sexual dysfunction',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F15.181',
                ],
            430 =>
                [
                    'id'                  => 464,
                    'name'                => 'Other stimulant abuse with stimulant-induced sleep disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F15.182',
                ],
            431 =>
                [
                    'id'                  => 465,
                    'name'                => 'Other stimulant abuse with unspecified stimulant-induced disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F15.19',
                ],
            432 =>
                [
                    'id'                  => 466,
                    'name'                => 'Other stimulant dependence with intoxication delirium',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F15.221',
                ],
            433 =>
                [
                    'id'                  => 467,
                    'name'                => 'Other stimulant dependence with intoxication with perceptual disturbance',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F15.222',
                ],
            434 =>
                [
                    'id'                  => 468,
                    'name'                => 'Other stimulant dependence with other stimulant-induced disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F15.288',
                ],
            435 =>
                [
                    'id'                  => 469,
                    'name'                => 'Other stimulant dependence with stimulant-induced anxiety disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F15.280',
                ],
            436 =>
                [
                    'id'                  => 470,
                    'name'                => 'Other stimulant dependence with stimulant-induced mood disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F15.24',
                ],
            437 =>
                [
                    'id'                  => 471,
                    'name'                => 'Other stimulant dependence with stimulant-induced psychotic disorder with delusions',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F15.250',
                ],
            438 =>
                [
                    'id'                  => 472,
                    'name'                => 'Other stimulant dependence with stimulant-induced psychotic disorder with hallucinations',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F15.251',
                ],
            439 =>
                [
                    'id'                  => 473,
                    'name'                => 'Other stimulant dependence with stimulant-induced psychotic disorder, unspecified',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F15.259',
                ],
            440 =>
                [
                    'id'                  => 474,
                    'name'                => 'Other stimulant dependence with stimulant-induced sexual dysfunction',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F15.281',
                ],
            441 =>
                [
                    'id'                  => 475,
                    'name'                => 'Other stimulant dependence with stimulant-induced sleep disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F15.282',
                ],
            442 =>
                [
                    'id'                  => 476,
                    'name'                => 'Other stimulant dependence with unspecified stimulant-induced disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F15.29',
                ],
            443 =>
                [
                    'id'                  => 477,
                    'name'                => 'Other stimulant use, unspecified with intoxication delirium',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F15.921',
                ],
            444 =>
                [
                    'id'                  => 478,
                    'name'                => 'Other stimulant use, unspecified with intoxication with perceptual disturbance',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F15.922',
                ],
            445 =>
                [
                    'id'                  => 479,
                    'name'                => 'Other stimulant use, unspecified with other stimulant-induced disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F15.988',
                ],
            446 =>
                [
                    'id'                  => 480,
                    'name'                => 'Other stimulant use, unspecified with stimulant-induced anxiety disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F15.980',
                ],
            447 =>
                [
                    'id'                  => 481,
                    'name'                => 'Other stimulant use, unspecified with stimulant-induced mood disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F15.94',
                ],
            448 =>
                [
                    'id'                  => 482,
                    'name'                => 'Other stimulant use, unspecified with stimulant-induced psychotic disorder with delusions',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F15.950',
                ],
            449 =>
                [
                    'id'                  => 483,
                    'name'                => 'Other stimulant use, unspecified with stimulant-induced psychotic disorder with hallucinations',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F15.951',
                ],
            450 =>
                [
                    'id'                  => 484,
                    'name'                => 'Other stimulant use, unspecified with stimulant-induced psychotic disorder,  unspecified',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F15.959',
                ],
            451 =>
                [
                    'id'                  => 485,
                    'name'                => 'Other stimulant use, unspecified with stimulant-induced sexual dysfunction',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F15.981',
                ],
            452 =>
                [
                    'id'                  => 486,
                    'name'                => 'Other stimulant use, unspecified with stimulant-induced sleep disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F15.982',
                ],
            453 =>
                [
                    'id'                  => 487,
                    'name'                => 'Other tic disorders',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F95.8',
                ],
            454 =>
                [
                    'id'                  => 488,
                    'name'                => 'Pain disorder exclusively related to psychological factors',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F45.41',
                ],
            455 =>
                [
                    'id'                  => 489,
                    'name'                => 'Pain disorder with related psychological factors',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F45.42',
                ],
            456 =>
                [
                    'id'                  => 490,
                    'name'                => 'Paradoxical insomnia',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F51.03',
                ],
            457 =>
                [
                    'id'                  => 491,
                    'name'                => 'Paranoid personality disorder',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F60.0',
                ],
            458 =>
                [
                    'id'                  => 492,
                    'name'                => 'Paranoid schizophrenia',
                    'simple_name'         => 'Psychosis & Schizophrenia',
                    'default_icd_10_code' => 'F20.0',
                ],
            459 =>
                [
                    'id'                  => 493,
                    'name'                => 'Paraphilia, unspecified',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F65.9',
                ],
            460 =>
                [
                    'id'                  => 494,
                    'name'                => 'Pathological gambling',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F63.0',
                ],
            461 =>
                [
                    'id'                  => 495,
                    'name'                => 'Pedophilia',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F65.4',
                ],
            462 =>
                [
                    'id'                  => 496,
                    'name'                => 'Persistent mood ºaffective» disorder, unspecified',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F34.9',
                ],
            463 =>
                [
                    'id'                  => 497,
                    'name'                => 'Personality change due to known physiological condition',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F07.0',
                ],
            464 =>
                [
                    'id'                  => 498,
                    'name'                => 'Personality disorder, unspecified',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F60.9',
                ],
            465 =>
                [
                    'id'                  => 499,
                    'name'                => 'Pervasive developmental disorder, unspecified',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F84.9',
                ],
            466 =>
                [
                    'id'                  => 500,
                    'name'                => 'Phobic anxiety disorder, unspecified',
                    'simple_name'         => 'Anxiety and Stress',
                    'default_icd_10_code' => 'F40.9',
                ],
            467 =>
                [
                    'id'                  => 501,
                    'name'                => 'Pica of infancy and childhood',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F98.3',
                ],
            468 =>
                [
                    'id'                  => 502,
                    'name'                => 'Post-traumatic stress disorder, acute',
                    'simple_name'         => 'Post-traumatic stress',
                    'default_icd_10_code' => 'F43.11',
                ],
            469 =>
                [
                    'id'                  => 503,
                    'name'                => 'Post-traumatic stress disorder, chronic',
                    'simple_name'         => 'Post-traumatic stress',
                    'default_icd_10_code' => 'F43.12',
                ],
            470 =>
                [
                    'id'                  => 504,
                    'name'                => 'Post-traumatic stress disorder, unspecified',
                    'simple_name'         => 'Post-traumatic stress',
                    'default_icd_10_code' => 'F43.10',
                ],
            471 =>
                [
                    'id'                  => 505,
                    'name'                => 'Postconcussional syndrome',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F07.81',
                ],
            472 =>
                [
                    'id'                  => 506,
                    'name'                => 'Premature ejaculation',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F52.4',
                ],
            473 =>
                [
                    'id'                  => 507,
                    'name'                => 'Premenstrual dysphonic disorder',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F32.81',
                ],
            474 =>
                [
                    'id'                  => 508,
                    'name'                => 'Primary hypersomnia',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F51.11',
                ],
            475 =>
                [
                    'id'                  => 509,
                    'name'                => 'Primary insomnia',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F51.01',
                ],
            476 =>
                [
                    'id'                  => 510,
                    'name'                => 'Pseudobulbar affect',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F48.2',
                ],
            477 =>
                [
                    'id'                  => 511,
                    'name'                => 'Psychotic disorder with delusions due to known physiological condition',
                    'simple_name'         => 'Psychosis & Schizophrenia',
                    'default_icd_10_code' => 'F06.2',
                ],
            478 =>
                [
                    'id'                  => 512,
                    'name'                => 'Psychotic disorder with hallucinations due to known physiological condition',
                    'simple_name'         => 'Psychosis & Schizophrenia',
                    'default_icd_10_code' => 'F06.0',
                ],
            479 =>
                [
                    'id'                  => 513,
                    'name'                => 'Puerperal psychosis',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F53',
                ],
            480 =>
                [
                    'id'                  => 514,
                    'name'                => 'Pyromania',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F63.1',
                ],
            481 =>
                [
                    'id'                  => 515,
                    'name'                => 'Reaction to severe stress, unspecified',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F43.9',
                ],
            482 =>
                [
                    'id'                  => 516,
                    'name'                => 'Reactive attachment disorder of childhood',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F94.1',
                ],
            483 =>
                [
                    'id'                  => 517,
                    'name'                => 'Residual schizophrenia',
                    'simple_name'         => 'Psychosis & Schizophrenia',
                    'default_icd_10_code' => 'F20.5',
                ],
            484 =>
                [
                    'id'                  => 518,
                    'name'                => 'Rumination disorder of infancy',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F98.21',
                ],
            485 =>
                [
                    'id'                  => 519,
                    'name'                => 'Sadomasochism, unspecified',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F65.50',
                ],
            486 =>
                [
                    'id'                  => 520,
                    'name'                => 'Schizoaffective disorder, bipolar type',
                    'simple_name'         => 'Psychosis & Schizophrenia',
                    'default_icd_10_code' => 'F25.0',
                ],
            487 =>
                [
                    'id'                  => 521,
                    'name'                => 'Schizoaffective disorder, depressive type',
                    'simple_name'         => 'Psychosis & Schizophrenia',
                    'default_icd_10_code' => 'F25.1',
                ],
            488 =>
                [
                    'id'                  => 522,
                    'name'                => 'Schizoaffective disorder, unspecified',
                    'simple_name'         => 'Psychosis & Schizophrenia',
                    'default_icd_10_code' => 'F25.9',
                ],
            489 =>
                [
                    'id'                  => 523,
                    'name'                => 'Schizoid personality disorder',
                    'simple_name'         => 'Psychosis & Schizophrenia',
                    'default_icd_10_code' => 'F60.1',
                ],
            490 =>
                [
                    'id'                  => 524,
                    'name'                => 'Schizophrenia, unspecified',
                    'simple_name'         => 'Psychosis & Schizophrenia',
                    'default_icd_10_code' => 'F20.9',
                ],
            491 =>
                [
                    'id'                  => 525,
                    'name'                => 'Schizophreniform disorder',
                    'simple_name'         => 'Psychosis & Schizophrenia',
                    'default_icd_10_code' => 'F20.81',
                ],
            492 =>
                [
                    'id'                  => 526,
                    'name'                => 'Schizotypal disorder',
                    'simple_name'         => 'Psychosis & Schizophrenia',
                    'default_icd_10_code' => 'F21',
                ],
            493 =>
                [
                    'id'                  => 527,
                    'name'                => 'Sedative, hypnotic or anxiolytic abuse with intoxication delirium',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F13.121',
                ],
            494 =>
                [
                    'id'                  => 528,
                    'name'                => 'Sedative, hypnotic or anxiolytic abuse with other sedative, hypnotic or anxiolytic-induced disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F13.188',
                ],
            495 =>
                [
                    'id'                  => 529,
                    'name'                => 'Sedative, hypnotic or anxiolytic abuse with sedative, hypnotic or anxiolytic-induced anxiety disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F13.180',
                ],
            496 =>
                [
                    'id'                  => 530,
                    'name'                => 'Sedative, hypnotic or anxiolytic abuse with sedative, hypnotic or anxiolytic-induced mood disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F13.14',
                ],
            497 =>
                [
                    'id'                  => 531,
                    'name'                => 'Sedative, hypnotic or anxiolytic abuse with sedative, hypnotic or anxiolytic-induced psychotic disor',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F13.150',
                ],
            498 =>
                [
                    'id'                  => 532,
                    'name'                => 'Sedative, hypnotic or anxiolytic abuse with sedative, hypnotic or anxiolytic-induced sexual dysfunct',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F13.181',
                ],
            499 =>
                [
                    'id'                  => 533,
                    'name'                => 'Sedative, hypnotic or anxiolytic abuse with sedative, hypnotic or anxiolytic-induced sleep disorder',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F13.182',
                ],
            500 =>
                [
                    'id'                  => 534,
                    'name'                => 'Sedative, hypnotic or anxiolytic abuse with unspecified sedative, hypnotic or anxiolytic-induced dis',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F13.19',
                ],
            501 =>
                [
                    'id'                  => 535,
                    'name'                => 'Sedative, hypnotic or anxiolytic dependence with intoxication delirium',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F13.221',
                ],
            502 =>
                [
                    'id'                  => 536,
                    'name'                => 'Sedative, hypnotic or anxiolytic dependence with other sedative, hypnotic or anxiolytic-induced diso',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F13.288',
                ],
            503 =>
                [
                    'id'                  => 537,
                    'name'                => 'Sedative, hypnotic or anxiolytic dependence with sedative, hypnotic or anxiolytic-induced anxiety di',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F13.280',
                ],
            504 =>
                [
                    'id'                  => 538,
                    'name'                => 'Sedative, hypnotic or anxiolytic dependence with sedative, hypnotic or anxiolytic-induced mood disor',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F13.24',
                ],
            505 =>
                [
                    'id'                  => 539,
                    'name'                => 'Sedative, hypnotic or anxiolytic dependence with sedative, hypnotic or anxiolytic-induced persisting',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F13.26',
                ],
            506 =>
                [
                    'id'                  => 540,
                    'name'                => 'Sedative, hypnotic or anxiolytic dependence with sedative, hypnotic or anxiolytic-induced psychotic',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F13.250',
                ],
            507 =>
                [
                    'id'                  => 541,
                    'name'                => 'Sedative, hypnotic or anxiolytic dependence with sedative, hypnotic or anxiolytic-induced sexual dys',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F13.281',
                ],
            508 =>
                [
                    'id'                  => 542,
                    'name'                => 'Sedative, hypnotic or anxiolytic dependence with sedative, hypnotic or anxiolytic-induced sleep diso',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F13.282',
                ],
            509 =>
                [
                    'id'                  => 543,
                    'name'                => 'Sedative, hypnotic or anxiolytic dependence with unspecified sedative, hypnotic or anxiolytic-induced',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F13.29',
                ],
            510 =>
                [
                    'id'                  => 544,
                    'name'                => 'Sedative, hypnotic or anxiolytic dependence with withdrawal delirium',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F13.231',
                ],
            511 =>
                [
                    'id'                  => 545,
                    'name'                => 'Sedative, hypnotic or anxiolytic dependence with withdrawal with perceptual disturbance',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F13.232',
                ],
            512 =>
                [
                    'id'                  => 546,
                    'name'                => 'Sedative, hypnotic or anxiolytic dependence with withdrawal, unspecified',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F13.239',
                ],
            513 =>
                [
                    'id'                  => 547,
                    'name'                => 'Sedative, hypnotic or anxiolytic use, unspecified with intoxication delirium',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F13.921',
                ],
            514 =>
                [
                    'id'                  => 548,
                    'name'                => 'Sedative, hypnotic or anxiolytic use, unspecified with other sedative, hypnotic or anxiolytic-induce',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F13.988',
                ],
            515 =>
                [
                    'id'                  => 549,
                    'name'                => 'Sedative, hypnotic or anxiolytic use, unspecified with sedative, hypnotic or anxiolytic-induced anxi',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F13.980',
                ],
            516 =>
                [
                    'id'                  => 550,
                    'name'                => 'Sedative, hypnotic or anxiolytic use, unspecified with sedative, hypnotic or anxiolytic-induced mood',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F13.94',
                ],
            517 =>
                [
                    'id'                  => 551,
                    'name'                => 'Sedative, hypnotic or anxiolytic use, unspecified with sedative, hypnotic or anxiolytic-induced pers',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F13.96',
                ],
            518 =>
                [
                    'id'                  => 552,
                    'name'                => 'Sedative, hypnotic or anxiolytic use, unspecified with sedative, hypnotic or anxiolytic-induced psyc',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F13.951',
                ],
            519 =>
                [
                    'id'                  => 553,
                    'name'                => 'Sedative, hypnotic or anxiolytic use, unspecified with sedative, hypnotic or anxiolytic-induced sexu',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F13.981',
                ],
            520 =>
                [
                    'id'                  => 554,
                    'name'                => 'Sedative, hypnotic or anxiolytic use, unspecified with sedative, hypnotic or anxiolytic-induced slee',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F13.982',
                ],
            521 =>
                [
                    'id'                  => 555,
                    'name'                => 'Sedative, hypnotic or anxiolytic use, unspecified with withdrawal delirium',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F13.931',
                ],
            522 =>
                [
                    'id'                  => 556,
                    'name'                => 'Sedative, hypnotic or anxiolytic use, unspecified with withdrawal with perceptual disturbances',
                    'simple_name'         => 'Substance Abuse (ex-Alcoholism)',
                    'default_icd_10_code' => 'F13.932',
                ],
            523 =>
                [
                    'id'                  => 557,
                    'name'                => 'Selective mutism',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F94.0',
                ],
            524 =>
                [
                    'id'                  => 558,
                    'name'                => 'Separation anxiety disorder of childhood',
                    'simple_name'         => 'Anxiety and Stress',
                    'default_icd_10_code' => 'F93.0',
                ],
            525 =>
                [
                    'id'                  => 559,
                    'name'                => 'Sexual aversion disorder',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F52.1',
                ],
            526 =>
                [
                    'id'                  => 560,
                    'name'                => 'Sexual masochism',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F65.51',
                ],
            527 =>
                [
                    'id'                  => 561,
                    'name'                => 'Sexual sadism',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F65.52',
                ],
            528 =>
                [
                    'id'                  => 562,
                    'name'                => 'Shared psychotic disorder',
                    'simple_name'         => 'Psychosis & Schizophrenia',
                    'default_icd_10_code' => 'F24',
                ],
            529 =>
                [
                    'id'                  => 563,
                    'name'                => 'Sleep disorder not due to a substance or known physiological condition, unspecified',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F51.9',
                ],
            530 =>
                [
                    'id'                  => 564,
                    'name'                => 'Sleep terrors [night terrors]',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F51.4',
                ],
            531 =>
                [
                    'id'                  => 565,
                    'name'                => 'Sleepwalking [somnambulism]',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F51.3',
                ],
            532 =>
                [
                    'id'                  => 566,
                    'name'                => 'Social phobia, generalized',
                    'simple_name'         => 'Anxiety and Stress',
                    'default_icd_10_code' => 'F40.11',
                ],
            533 =>
                [
                    'id'                  => 567,
                    'name'                => 'Social phobia, unspecified',
                    'simple_name'         => 'Anxiety and Stress',
                    'default_icd_10_code' => 'F40.10',
                ],
            534 =>
                [
                    'id'                  => 568,
                    'name'                => 'Social pragmatic communication disorder',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F80.82',
                ],
            535 =>
                [
                    'id'                  => 569,
                    'name'                => 'Somatization disorder',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F45.0',
                ],
            536 =>
                [
                    'id'                  => 570,
                    'name'                => 'Somatoform disorder, unspecified',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F45.9',
                ],
            537 =>
                [
                    'id'                  => 571,
                    'name'                => 'State of emotional shock and stress, unspecified',
                    'simple_name'         => 'Anxiety and Stress',
                    'default_icd_10_code' => 'R45.7',
                ],
            538 =>
                [
                    'id'                  => 572,
                    'name'                => 'Stereotyped movement disorders',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F98.4',
                ],
            539 =>
                [
                    'id'                  => 573,
                    'name'                => 'Tic disorder, unspecified',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F95.9',
                ],
            540 =>
                [
                    'id'                  => 574,
                    'name'                => 'Tourette\'s disorder',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F95.2',
                ],
            541 =>
                [
                    'id'                  => 575,
                    'name'                => 'Transient tic disorder',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F95.0',
                ],
            542 =>
                [
                    'id'                  => 576,
                    'name'                => 'Transsexualism',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F64.0',
                ],
            543 =>
                [
                    'id'                  => 577,
                    'name'                => 'Transvestic fetishism',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F65.1',
                ],
            544 =>
                [
                    'id'                  => 578,
                    'name'                => 'Trichotillomania',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F63.3',
                ],
            545 =>
                [
                    'id'                  => 579,
                    'name'                => 'Undifferentiated schizophrenia',
                    'simple_name'         => 'Psychosis & Schizophrenia',
                    'default_icd_10_code' => 'F20.3',
                ],
            546 =>
                [
                    'id'                  => 580,
                    'name'                => 'Undifferentiated somatoform disorder',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F45.1',
                ],
            547 =>
                [
                    'id'                  => 581,
                    'name'                => 'Unspecified behavioral and emotional disorders with onset usually occurring in childhood and adolesc',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F98.9',
                ],
            548 =>
                [
                    'id'                  => 582,
                    'name'                => 'Unspecified behavioral syndromes associated with physiological disturbances and physical factors',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F59',
                ],
            549 =>
                [
                    'id'                  => 583,
                    'name'                => 'Unspecified dementia with behavioral disturbance',
                    'simple_name'         => 'Dementia',
                    'default_icd_10_code' => 'F03.91',
                ],
            550 =>
                [
                    'id'                  => 584,
                    'name'                => 'Unspecified dementia without behavioral disturbance',
                    'simple_name'         => 'Dementia',
                    'default_icd_10_code' => 'F03.90',
                ],
            551 =>
                [
                    'id'                  => 585,
                    'name'                => 'Unspecified disorder of psychological development',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F89',
                ],
            552 =>
                [
                    'id'                  => 586,
                    'name'                => 'Unspecified mental disorder due to known physiological condition',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F09',
                ],
            553 =>
                [
                    'id'                  => 587,
                    'name'                => 'Unspecified mood ºaffective» disorder',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F39',
                ],
            554 =>
                [
                    'id'                  => 588,
                    'name'                => 'Unspecified personality and behavioral disorder due to known physiological condition',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F07.9',
                ],
            555 =>
                [
                    'id'                  => 589,
                    'name'                => 'Unspecified psychosis not due to a substance or known physiological condition',
                    'simple_name'         => 'Psychosis & Schizophrenia',
                    'default_icd_10_code' => 'F29',
                ],
            556 =>
                [
                    'id'                  => 590,
                    'name'                => 'Unspecified sexual dysfunction not due to a substance or known physiological condition',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F52.9',
                ],
            557 =>
                [
                    'id'                  => 591,
                    'name'                => 'Vaginismus not due to a substance or known physiological condition',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F52.5',
                ],
            558 =>
                [
                    'id'                  => 592,
                    'name'                => 'Vascular dementia with behavioral disturbance',
                    'simple_name'         => 'Dementia',
                    'default_icd_10_code' => 'F01.51',
                ],
            559 =>
                [
                    'id'                  => 593,
                    'name'                => 'Vascular dementia without behavioral disturbance',
                    'simple_name'         => 'Dementia',
                    'default_icd_10_code' => 'F01.50',
                ],
            560 =>
                [
                    'id'                  => 594,
                    'name'                => 'Voyeurism',
                    'simple_name'         => null,
                    'default_icd_10_code' => 'F65.3',
                ],
        ]);
    }
}
