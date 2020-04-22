<?php

namespace App\Console\Commands;

use App\Answer;
use Illuminate\Console\Command;

class TestAnswers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:answers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $tasks = collect(
            [
                /*NUTRITION*/
                [
                    'title' => 'Nutrition',
                    'data'  => [
                        [
                            'sub_title'           => 'Poor diet (fruits/veggies)',
                            'task_body'           => 'Fruits and vegetables are important part of healthy eating and provide a source of many nutrients, including potassium, fiber, folate (folic acid) and vitamins A, E and C. People who eat fruit and vegetables as part of their daily diet have a reduced risk of many chronic diseases. Your doctor may recommend:',
                            'recommendation_body' => ['Getting 4-5 servings of fruits and vegetables a day'],
                            'trigger_conditions'  => [
                                    [
                                        'logical_operator' => 'and',
                                        'expressions'      => [
                                            [
                                                'field'    => 'question_id',
                                                'operator' => '=',
                                                'value'    => 6,
                                            ],
                                            [
                                                'field'    => 'value->value',
                                                'operator' => '!=',
                                                'value'    => '4+',
                                            ],
                                        ],
                                    ],
                                ],
                        ],
                        [
                            'sub_title'           => ' Poor diet (whole grain)',
                            'task_body'           => 'Foods made from grains (wheat, rice, and oats) help form the foundation of a nutritious diet. They provide vitamins, minerals, carbohydrates (starch and dietary fiber), and other substances that are important for good health. Eating plenty of whole grains, such as whole wheat bread or oatmeals may help protect you against many chronic diseases. Experts recommend that all adults eat at least half their grains as whole grains. Your doctor may suggest:',
                            'recommendation_body' => ['Aiming for at least 3-5 servings of whole grains a day'],
                            'trigger_conditions'  => [
                                    [
                                        'logical_operator' => 'and',
                                        'expressions'      => [
                                            [
                                                'field'    => 'question_id',
                                                'operator' => '=',
                                                'value'    => 7,
                                            ],
                                            [
                                                'field'    => 'value->value',
                                                'operator' => '!=',
                                                'value'    => '3-4',
                                            ],
                                        ],
                                    ],
                                    [
                                        'logical_operator' => 'or',
                                        'expressions'      => [
                                            [
                                                'field'    => 'question_id',
                                                'operator' => '=',
                                                'value'    => 7,
                                            ],
                                            [

                                                'field'    => 'value->value',
                                                'operator' => '!=',
                                                'value'    => '5+',

                                            ],
                                        ],
                                    ],
                                    [
                                        'logical_operator' => 'and',
                                        'expressions'      => [
                                            [
                                                'field'    => 'question_id',
                                                'operator' => '=',
                                                'value'    => 24,
                                            ],
                                            [
                                                'field'    => 'value->value',
                                                'operator' => '!=',
                                                'value'    => 'Diabetes',
                                            ],
                                        ],
                                    ],

                                ],
                        ],
                    ],
                ],
                /*Tobacco Smoking*/
     /*           [
                    'title'          => 'Tobacco/Smoking',
                    'rec_task_title' =>
                        [
                            ['Smoking Counseling'],
                            ['Smoking Pharmacotherapy'],
                            ['Lung cancer screening (precautionary)'],
                        ],
                    'data'           => [
                        [
                            'sub_title'           => 'Current Smoker ',
                            'task_body'           => 'Smoking, or the use of any tobacco products harms nearly every organ of the body, causes many diseases, and reduces the health of smokers in general. Smokers are more likely than nonsmokers to develop heart disease, stroke, and lung cancer. Cigarette smoking is the leading preventable cause of death in the United States, causing more than 480,000 deaths domestically each year. This includes about 90% of all lung cancer deaths, and about 80% of all deaths from chronic obstructive pulmonary disease (COPD). Quitting smoking lowers your risk for smoking-related diseases and can add years to your life. Talk to your doctor about what interventions you may be able to use to help you quit. These may include:',
                            'recommendation_body' =>
                                [
                                    ['Counseling and/or pharmacotherapy interventions'],
                                    ['Lung cancer screening (precautionary)'],
                                ],

                            'trigger_conditions' =>
                                [
                                    [
                                        'logical_operator' => 'and',
                                        'expressions'      => [
                                            [
                                                'field'    => 'question_id',
                                                'operator' => '=',
                                                'value'    => 11,
                                            ],
                                            [
                                                'field'    => 'value->value',
                                                'operator' => '=',
                                                'value'    => 'yes',
                                            ],
                                        ],
                                    ],
                                    [
                                        'logical_operator' => 'and',
                                        'expressions'      => [
                                            [
                                                'field'    => 'question_id',
                                                'operator' => '!=',
                                                'value'    => 15,
                                            ],
                                            [
                                                'field'    => 'value->value',
                                                'operator' => '=',
                                                'value'    => 'I already quit',
                                            ],
                                        ],
                                    ],
                                ],
                        ],

                        [
                            'sub_title'           => 'Males 65-75 and current or former smoker',
                            'task_body'           => 'Due to your age, sex, and smoking status, your Doctor may also recommend an:',
                            'recommendation_body' => ['AAA (Abdominal Aortic Aneurysm) screening'],
                            'trigger_conditions'  =>
                                [
                                    [
                                        'logical_operator' => 'and',
                                        'expressions'      => [
                                            [
                                                'field'    => 'question_id',
                                                'operator' => '=',
                                                'value'    => 11,
                                            ],
                                            [
                                                'field'    => 'value->value',
                                                'operator' => '=',
                                                'value'    => 'Yes',
                                            ],
                                        ],
                                    ],
                                    [
                                        'logical_operator' => 'and',
                                        'expressions'      => [
                                            [
                                                'field'    => 'question_id',
                                                'operator' => '!=',
                                                'value'    => 4,
                                            ],
                                            [
                                                'field'    => 'value->value',
                                                'operator' => '=',
                                                'value'    => 'Male',
                                            ],
                                        ],
                                    ],
                                    [
                                        'logical_operator' => 'and',
                                        'expressions'      => [
                                            [
                                                'field'    => 'question_id',
                                                'operator' => '!=',
                                                'value'    => 2,
                                            ],
                                            [
                                                'field'    => 'value->value',
                                                'operator' => '>',
                                                'value'    => '65',
                                            ],
                                        ],
                                    ],

                                    [
                                        [
                                            'logical_operator' => 'and',
                                            'expressions'      => [
                                                [
                                                    'field'    => 'question_id',
                                                    'operator' => '!=',
                                                    'value'    => 2,
                                                ],
                                                [
                                                    'field'    => 'value->value',
                                                    'operator' => '<',
                                                    'value'    => '75',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                        ],

                        [
                            'sub_title'           => 'Former Smoker',
                            'task_body'           => 'Congrats! Having quit smoking is a great achievement. By avoiding smoking, you are lowering your risk of smoking-related illnesses every day. Quitting smoking has health benefits that start right away and improve over many years. Unfortunately, smoking any amount can cause damage that can lead to health problems. The risk of lung cancer decreases over time, though it remains higher than a non-smoker’s. As a result, your doctor may suggest:',
                            'recommendation_body' => ['Lung cancer screening (precautionary)'],
                            'trigger_conditions'  =>
                                [
                                    [
                                        'logical_operator' => 'and',
                                        'expressions'      => [
                                            [
                                                'field'    => 'question_id',
                                                'operator' => '=',
                                                'value'    => 11,
                                            ],
                                            [
                                                'field'    => 'value->value',
                                                'operator' => '=',
                                                'value'    => 'Yes',
                                            ],
                                        ],
                                    ],
                                    [
                                        'logical_operator' => 'and',
                                        'expressions'      => [
                                            [
                                                'field'    => 'question_id',
                                                'operator' => '=',
                                                'value'    => 15,
                                            ],
                                            [
                                                'field'    => 'value->value',
                                                'operator' => '=',
                                                'value'    => 'I already quit',
                                            ],
                                        ],
                                    ],
                                ],
                        ],
                    ],
                ],*/
            ]
        );

        $triggerConditions = collect();
        foreach ($tasks as $task) {
            foreach ($task['data'] as $condition) {
                $triggerConditions[] = $condition['trigger_conditions'];
            }
        }
        $query = Answer::query();

        $triggerConditions->each(function ($triggerCondition) use ($query) {
            foreach ($triggerCondition as $condition) {
                $operator = $condition['logical_operator'] === 'and'
                    ? 'where'
                    : 'orWhere';

                $expressions = collect($condition['expressions']);
                $expressions->each(function ($e) use ($query, $operator) {
                    $query->$operator($e['field'], $e['operator'], $e['value']);
                });

                $this->info($query->get());
            }
        });
        /*  $triggerConditions = collect();
          foreach ($conditions['data'] as $condition) {

              $triggerConditions[] = $condition['trigger_conditions'];
          }
          $query = Answer::query();

          $triggerConditions->each(function ($triggerCondition) use ($query) {

              foreach ($triggerCondition as $condition) {

                  $operator = $condition['logical_operator'] === 'and'
                      ? 'where'
                      : 'orWhere';


                  $expressions = collect($condition['expressions']);

                  $expressions->each(function ($e) use ($query, $operator) {
                      $query->$operator($e['field'], $e['operator'], $e['value']);
                  });
                  $this->info($query->get());
                  $this->info($query->count());
              }

          });*/
    }
}
