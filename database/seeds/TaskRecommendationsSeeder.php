<?php

use App\TaskRecommendations;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class TaskRecommendationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->createTaskRecommendationData();
    }

    private function createTaskRecommendationData()
    {
        $taskRecommendations = $this->taskRecommendationData();

        foreach ($taskRecommendations as $taskRecommendation) {

            TaskRecommendations::create([
                'title' => $taskRecommendation['title'],
                'data'  => $taskRecommendation['data'],
            ]);

        }


    }

    private function taskRecommendationData(): Collection
    {
        return collect([
            /*NUTRITION*/
            [
                'title' => 'Nutrition',
                'data'  => [
                    [
                        'sub_title' => 'Poor diet (fruits/veggies)',
                        'task_body'           => 'Fruits and vegetables are important part of healthy eating and provide a source of many nutrients, including potassium, fiber, folate (folic acid) and vitamins A, E and C. People who eat fruit and vegetables as part of their daily diet have a reduced risk of many chronic diseases. Your doctor may recommend:',
                        'recommendation_body' => ['Getting 4-5 servings of fruits and vegetables a day '],
                        'trigger_conditions'  =>
                            [
                                'question_order' => 6,
                            ],
                    ],

                    [
                        'sub_title' => ' Poor diet (whole grain)',
                        'task_body'           => 'Foods made from grains (wheat, rice, and oats) help form the foundation of a nutritious diet. They provide vitamins, minerals, carbohydrates (starch and dietary fiber), and other substances that are important for good health. Eating plenty of whole grains, such as whole wheat bread or oatmeals may help protect you against many chronic diseases. Experts recommend that all adults eat at least half their grains as whole grains. Your doctor may suggest:',
                        'recommendation_body' => ['Aiming for at least 3-5 servings of whole grains a day'],
                        'trigger_conditions'  =>
                            [
                                'question_order'   => 7,
                                'question_order_2' => 16,
                            ],
                    ],

                    [
                        'sub_title' => 'Poor diet (fatty/fried foods)',
                        'task_body'           => 'A small amount of fat is an essential part of a healthy, balanced diet. Although It\'s fine to enjoy fats, fried foods and sweets occasionally, too much sugar and saturated fat in your diet can raise your cholesterol. This increases the risk of heart disease. Your doctor may recommend:',
                        'recommendation_body' => ['Cutting down consumption to <1 servings of fried and high-fat foods a day'],
                        'trigger_conditions'  =>
                            [
                                'question_order' => 8,
                            ],
                    ],

                    [
                        'sub_title' => 'Poor diet (candy/sugary beverages)',
                        'task_body'           => 'The average can of sugar-sweetened (sucrose, high-fructose corn syrup, dextrose, cane sugar etc.) soda or fruit punch provides about 150 calories, almost all of them from sugar, usually high-fructose corn syrup. That’s the equivalent of 10 teaspoons of table sugar. If you were to drink just one can of a sugar-sweetened soft drink every day, and not cut back on calories elsewhere, you could gain up to 5 pounds in a year. People who drink sugary beverages do not feel as full as if they had eaten the same calories from solid food, and studies show that people consuming sugary beverages don’t compensate for their high caloric content by eating less food.  Your doctor may recommend:',
                        'recommendation_body' => ['Cutting down consumption to <1 servings of sugar-sweetened beverages / sweets a day'],
                        'trigger_conditions'  =>
                            [
                                'question_order' => 9,
                            ],
                    ],
                ],
            ],
            /*Tobacco Smoking*/
            [
                'title' => 'Tobacco/Smoking',
                'data'  => [
                    [
                        'sub_title' => 'Current Smoker ',
                        'task_body'           => 'Smoking, or the use of any tobacco products harms nearly every organ of the body, causes many diseases, and reduces the health of smokers in general. Smokers are more likely than nonsmokers to develop heart disease, stroke, and lung cancer. Cigarette smoking is the leading preventable cause of death in the United States, causing more than 480,000 deaths domestically each year. This includes about 90% of all lung cancer deaths, and about 80% of all deaths from chronic obstructive pulmonary disease (COPD). Quitting smoking lowers your risk for smoking-related diseases and can add years to your life. Talk to your doctor about what interventions you may be able to use to help you quit. These may include:',
                        'recommendation_body' => [
                            ['Counseling and/or pharmacotherapy interventions'],
                            ['Lung cancer screening (precautionary)'],
                        ],
                        'trigger_conditions'  =>
                            [
                                'question_order'    => 11,
                                'question_suborder' => 'd',
                            ],
                    ],

                    [
                        'sub_title' => 'Males 65-75 and current or former smoker',
                        'task_body'           => 'Due to your age, sex, and smoking status, your Doctor may also recommend an:',
                        'recommendation_body' => ['AAA (Abdominal Aortic Aneurysm) screening'],
                        'trigger_conditions'  =>
                            [
                                'question_order'   => 11,
                                'question_order_2' => 4,
                                'question_order_3' => 2,
                            ],
                    ],

                    [
                        'sub_title' => 'Former Smoker',
                        'task_body'           => 'Congrats! Having quit smoking is a great achievement. By avoiding smoking, you are lowering your risk of smoking-related illnesses every day. Quitting smoking has health benefits that start right away and improve over many years. Unfortunately, smoking any amount can cause damage that can lead to health problems. The risk of lung cancer decreases over time, though it remains higher than a non-smoker’s. As a result, your doctor may suggest:',
                        'recommendation_body' => ['Lung cancer screening (precautionary)'],
                        'trigger_conditions'  =>
                            [
                                'question_order'    => 11,
                                'question_suborder' => 'd',
                            ],
                    ],
                ],
            ],
            /*Alcohol*/
            [
                'title' => 'Alcohol',
                'data'=>[
                    'sub_title' => 'Risky use of alcohol (gender dependent)',
                    'task_body'=>'Drinking too much – on a single occasion or over time – can take a serious toll on your health.Having more than a 7 drinks a week for females, or more than 14 drinks a week for males may result in health complications including liver disease, pancreatitis, cancer, and ulcers/other GI problems.Talk to your doctor about ways to cut down on alcohol consumption, they may prescribe :',
                    'recommendation_body' =>[
                        ['Counseling ranging from brief single contact to extended multicontact counseling'],
                        ['Liver disease screening']
                    ],
                    'trigger_conditions'  =>
                        [
                            'question_order'    => 4,
                            'question_order_2'    => 12,
                            'question_suborder2' => 'a',
                        ],

                    ],
            ],

            [
              'title' => 'Recreational Drug Use',
              'data'=> [

              ]
            ],

        ]);
    }
}
