<?php

use App\Question;
use App\QuestionGroup;
use App\QuestionType;
use App\Survey;
use App\SurveyInstance;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class SurveySeeder extends Seeder
{
    protected $date;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->date = Carbon::now();
        $this->createHraSurvey();
        $this->createVitalsSurvey();
    }

    private function createVitalsSurvey()
    {
        $vitals = Survey::firstOrCreate([
            'name'        => Survey::VITALS,
            'description' => 'Vitals Survey',
        ]);

        $currentInstance = SurveyInstance::firstOrCreate([
            'survey_id' => $vitals->id,
            'year'      => $this->date->year,
        ]);

        $questionsData = $this->vitalsQuestionData();

        $this->createQuestions($currentInstance, $questionsData);

    }

    private function createHraSurvey()
    {
        $hra = Survey::firstOrCreate([
            'name'        => Survey::HRA,
            'description' => 'Health Risk Assessment',
        ]);

        $currentInstance = SurveyInstance::firstOrCreate([
            'survey_id' => $hra->id,
            'year'      => $this->date->year,
        ]);

        $questionsData = $this->hraQuestionData();

        $this->createQuestions($currentInstance, $questionsData);

    }

    private function createQuestions($instance, $questionsData)
    {
        foreach ($questionsData as $questionData) {

            if (array_key_exists('question_group', $questionData)) {
                $groupId = QuestionGroup::firstOrCreate([
                    'body' => $questionData['question_group'],
                ])
                    ->id;
            } else {
                $groupId = null;
            }

            $question = Question::create([
                'survey_id'         => $instance->survey_id,
                'body'              => $questionData['question_body'],
                'question_group_id' => $groupId,
                'optional'          => array_key_exists('optional', $questionData)
                    ? $questionData['optional']
                    : false,
                'conditions'        => array_key_exists('conditions', $questionData)
                    ? $questionData['conditions']
                    : null,
            ]);


            $questionType = $question->type()->create([
                'type' => $questionData['question_type'],
            ]);

            if (array_key_exists('question_type_answers', $questionData)) {
                foreach ($questionData['question_type_answers'] as $questionTypeAnswer) {
                    $questionType->questionTypeAnswers()->create([
                        'value'   => array_key_exists('type_answer_body', $questionTypeAnswer)
                            ? $questionTypeAnswer['type_answer_body']
                            : null,
                        'options' => array_key_exists('options', $questionTypeAnswer)
                            ? $questionTypeAnswer['options']
                            : null,
                    ]);
                }
            }


            $instance->questions()->attach(
                $question->id,
                [
                    'order'     => $questionData['order'],
                    'sub_order' => array_key_exists('sub_order', $questionData)
                        ? $questionData['sub_order']
                        : null,
                ]
            );
        }

    }

    private function vitalsQuestionData(): Collection
    {
        return collect([
            [
                'order'                 => 1,
                'question_body'         => "What is the patient's blood pressure?",
                'question_type'         => QuestionType::NUMBER,
                'question_type_answers' => [
                    [
                        'options' => [
                            'sub-parts'               => [
                                [
                                    'key' => 'first_metric',
                                ],
                                [
                                    'key' => 'second_metric',
                                ],
                            ],
                            'separate_sub_parts_with' => 'dash',
                        ],
                    ],
                ],
            ],
            [
                'order'                 => 2,
                'question_body'         => "What is the patient's weight?",
                'question_type'         => QuestionType::NUMBER,
                'question_type_answers' => [
                    [
                        'options' => [
                            'placeholder' => 'ex. 150 (lbs)',
                        ],
                    ],
                ],
            ],
            [
                'order'                 => 3,
                'question_body'         => "What is the patient's height?",
                'question_type'         => QuestionType::NUMBER,
                'question_type_answers' => [
                    [
                        'options' =>
                            [
                                'sub_parts' => [
                                    [
                                        'placeholder' => "Feet'",
                                        'key'         => 'feet',
                                    ],
                                    [
                                        'placeholder' => 'Inches"',
                                        'key'         => 'inches',
                                    ],
                                ],

                            ],
                    ],
                ],
            ],
            [
                'order'                => 4,
                'question_body'        => "What is the patient's body mass index (BMI)?",
                'question_type'        => QuestionType::NUMBER,
                'question_type_answer' => [
                    [
                        'options' => [
                            'placeholder' => 'ex. 25',
                        ],
                    ],
                ],
            ],
            [
                'order'                 => 5,
                'sub_order'             => 'a',
                'question_body'         => 'Word Recall (1 point for each word spontaneously recalled without cueing) 
                http://mini-cog.com/wp-content/uploads/2015/12/Universal-Mini-Cog-Form-011916.pdf',
                'question_type'         => QuestionType::RADIO,
                //we have to see about (c) and inserting the link
                'question_group'        => 'Based off of the Mini-Cog(c) assessment, how did your patient score? (insert link)',
                'question_type_answers' => [
                    [
                        'type_answer_body' => 0,
                    ],
                    [
                        'type_answer_body' => 1,
                    ],
                    [
                        'type_answer_body' => 2,
                    ],
                    [
                        'type_answer_body' => 3,
                    ],
                ],
            ],
            [
                'order'                 => 5,
                'sub_order'             => 'b',
                'question_body'         => 'Clock Draw (Normal clock = 2 points. A normal clock has all numbers placed in the cor-rect sequence and approximately correct position (e.g., 12, 3, 6 and 9 are in anchor positions) with no missing or duplicate numbers. Hands are point-ing to the 11 and 2 (11:10). Hand length is not scored.Inability or refusal to draw a clock (abnormal) = 0 points.)',
                'question_type'         => QuestionType::RADIO,
                'question_group'        => 'Based off of the Mini-Cog(c) assessment, how did your patient score? (insert link)',
                'question_type_answers' => [
                    [
                        'type_answer_body' => 0,
                    ],
                    [
                        'type_answer_body' => 2,
                    ],
                ],
            ],
            [
                'order'                 => 5,
                'sub_order'             => 'c',
                'question_body'         => 'Total Score (Total score = Word Recall score + Clock Draw score)',
                'question_type'         => QuestionType::RADIO,
                'conditions'            => [
                    'is_auto_generated' => true,
                    'generated_from'    => [
                        [
                            'order'     => 5,
                            'sub_order' => 'a',
                        ],
                        [
                            'order'     => 5,
                            'sub_order' => 'b',
                        ],
                    ],
                ],
                'question_group'        => 'Based off of the Mini-Cog(c) assessment, how did your patient score? (insert link)',
                'question_type_answers' => [
                    [
                        'type_answer_body' => 0,
                    ],
                    [
                        'type_answer_body' => 1,
                    ],
                    [
                        'type_answer_body' => 2,
                    ],
                    [
                        'type_answer_body' => 3,
                    ],
                    [
                        'type_answer_body' => 4,
                    ],
                    [
                        'type_answer_body' => 5,
                    ],
                ],
            ],
        ]);
    }

    private function hraQuestionData(): Collection
    {
        return collect([
            [
                'order'                 => 1,
                'question_body'         => 'What is your race?',
                'question_type'         => QuestionType::RADIO,
                'question_type_answers' => [
                    ['type_answer_body' => 'African American/Black'],
                    ['type_answer_body' => 'Asian'],
                    ['type_answer_body' => 'Caucasian/White'],
                    ['type_answer_body' => 'Hispanic or Latino Origin or Descent'],
                    ['type_answer_body' => 'Indian'],
                    ['type_answer_body' => 'Native American or Alaskan Native'],
                    ['type_answer_body' => 'Native Hawaiian or other Pacific Islander'],
                    ['type_answer_body' => 'Other'],
                ],
            ],
            [
                'order'         => 2,
                'question_body' => 'What is your age?',
                'question_type' => QuestionType::NUMBER,
            ],
            [
                'order'                 => 3,
                'question_body'         => 'What is your height?',
                'question_type'         => QuestionType::NUMBER,
                'question_type_answers' => [
                    [
                        'options' =>
                            [
                                'sub_parts' => [
                                    [
                                        'placeholder' => "Feet'",
                                        'key'         => 'feet',
                                    ],
                                    [
                                        'placeholder' => 'Inches"',
                                        'key'         => 'inches',
                                    ],
                                ],

                            ],
                    ],
                ],
            ],
            [
                'order'                 => 4,
                'question_body'         => 'What is your sex?',
                'question_type'         => QuestionType::RADIO,
                'question_type_answers' => [
                    [
                        'type_answer_body' => 'Male',
                    ],
                    [
                        'type_answer_body' => 'Female',
                    ],
                    [
                        'type_answer_body' => 'Transgender',
                    ],
                    [
                        'type_answer_body' => 'Other',
                    ],
                ],
            ],
            [
                'order'                 => 5,
                'question_body'         => 'In general, how would you rate your health?',
                'question_type'         => QuestionType::RADIO,
                'question_type_answers' => [
                    [
                        'type_answer_body' => 'Excellent',
                    ],
                    [
                        'type_answer_body' => 'Very Good',
                    ],
                    [
                        'type_answer_body' => 'Good',
                    ],
                    [
                        'type_answer_body' => 'Fair',
                    ],
                    [
                        'type_answer_body' => 'Poor',
                    ],
                ],
            ],
            [
                'order'                 => 6,
                'question_body'         => 'In the past 7 days, how many servings of fruits and vegetables did you typically eat each day? (1 serving = 1 cup of fresh vegetables, 1/2 cup of cooked vegetables, or 1 medium piece of fruit. 1 cup = size of a baseball).',
                'question_type'         => QuestionType::RADIO,
                'question_type_answers' => [
                    [
                        'type_answer_body' => '0',
                    ],
                    [
                        'type_answer_body' => '1-2',
                    ],
                    [
                        'type_answer_body' => '3',
                    ],
                    [
                        'type_answer_body' => '4+',
                    ],
                ],
            ],
            [
                'order'                 => 7,
                'question_body'         => 'In the past 7 days, how many servings of high fiber or whole grain foods did you typically eat each day? (1 serving = 1 slice of 100% of whole wheat bread, 1 cup of whole-grain or high fiber ready to eat cereal, 1/2 cup of cooked cereal such as oatmeal, or 1/2 cup of cooked brown rice or whole wheat pasta).',
                'question_type'         => QuestionType::RADIO,
                'question_type_answers' => [
                    [
                        'type_answer_body' => '0',
                    ],
                    [
                        'type_answer_body' => '1-2',
                    ],
                    [
                        'type_answer_body' => '3-4',
                    ],
                    [
                        'type_answer_body' => '5+',
                    ],
                ],
            ],
            [
                'order'                 => 8,
                'question_body'         => 'In the past 7 days, how many servings of fried or high-fat foods did you typically eat each day? (Examples include fried chicken, fried fish, bacon, French fries, potato chips, corn chips, doughnuts, creamy salad dressings, and foods made with whole milk, cream, cheese, or mayonnaise).',
                'question_type'         => QuestionType::RADIO,
                'question_type_answers' => [
                    [
                        'type_answer_body' => '0',
                    ],
                    [
                        'type_answer_body' => '1-2',
                    ],
                    [
                        'type_answer_body' => '3',
                    ],
                    [
                        'type_answer_body' => '4+',
                    ],
                ],
            ],
            [
                'order'                 => 9,
                'question_body'         => 'In the past 7 days, how many sugar-sweetened (not diet) beverages and candy servings did you typically consume each day?',
                'question_type'         => QuestionType::RADIO,
                'question_type_answers' => [
                    [
                        'type_answer_body' => '0',
                    ],
                    [
                        'type_answer_body' => '1-2',
                    ],
                    [
                        'type_answer_body' => '3',
                    ],
                    [
                        'type_answer_body' => '4+',
                    ],
                ],
            ],
            [
                'order'                 => 10,
                'question_body'         => 'In the past 2 weeks, have you experienced a change in the amount you normally eat, either poor appetite or overeating?',
                'question_type'         => QuestionType::RADIO,
                'question_type_answers' => [
                    [
                        'type_answer_body' => 'Yes',
                        'options'          => [
                            //yes or no answers have smaller boxes
                            'yes_or_no_question' => true,
                        ],
                    ],
                    [
                        'type_answer_body' => 'No',
                        'options'          => [
                            'yes_or_no_question' => true,
                        ],
                    ],
                ],
            ],
            [
                'order'                 => 11,
                'question_body'         => 'Do or did you ever smoke or use any tobacco products (cigarettes, chew, snuff, pipes, cigars, vapor/e-cigarettes)?',
                'question_type'         => QuestionType::RADIO,
                'question_type_answers' => [
                    [
                        'type_answer_body' => 'Yes',
                        'options'          => [
                            'yes_or_no_question' => true,
                        ],
                    ],
                    [
                        'type_answer_body' => 'No',
                        'options'          => [
                            'yes_or_no_question' => true,
                        ],
                    ],
                ],
            ],
            [
                'order'                 => 11,
                'sub_order'             => 'a',
                'question_body'         => 'How many years ago did you start smoking?',
                'optional'              => false,
                'conditions'            => [
                    [
                        'related_question_order_number'    => 11,
                        'related_question_expected_answer' => 'Yes',
                    ],
                ],
                'question_type'         => QuestionType::RADIO,
                'question_type_answers' => [
                    [
                        'type_answer_body' => 'This Year',
                    ],
                    [
                        'options' => [
                            'placeholder'               => 'Other, ex. 10',
                            'answer_type'               => 'text',
                            'allow_single_custom_input' => true,
                        ],
                    ],
                ],
            ],
            [
                'order'                 => 11,
                'sub_order'             => 'b',
                'question_body'         => 'When was the last time you smoked or used any tobacco products?',
                'optional'              => false,
                'conditions'            => [
                    [
                        'related_question_order_number'    => 11,
                        'related_question_expected_answer' => 'Yes',
                    ],
                ],
                'question_type'         => QuestionType::RADIO,
                'question_type_answers' => [
                    [
                        'type_answer_body' => 'This Year',
                    ],
                    [
                        'options' => [
                            'placeholder'               => 'Other, ex: 10 years ago',
                            'answer_type'               => 'text',
                            'allow_single_custom_input' => true,
                        ],
                    ],
                ],
            ],
            [
                'order'                 => 11,
                'sub_order'             => 'c',
                'question_body'         => 'On average, how many packs/day do or did you smoke?',
                'optional'              => false,
                'conditions'            => [
                    [
                        'related_question_order_number'    => 11,
                        'related_question_expected_answer' => 'Yes',
                    ],
                ],
                'question_type'         => QuestionType::RADIO,
                'question_type_answers' => [
                    [
                        'type_answer_body' => '<1/2',
                    ],
                    [
                        'type_answer_body' => '1/2',
                    ],
                    [
                        'type_answer_body' => '1',
                    ],
                    [
                        'type_answer_body' => 'Other',
                    ],
                ],
            ],
            [
                'order'                 => 11,
                'sub_order'             => 'd',
                'question_body'         => 'Are you interested in quitting?',
                'optional'              => false,
                'conditions'            => [
                    [
                        'related_question_order_number'    => 11,
                        'related_question_expected_answer' => 'Yes',
                    ],
                ],
                'question_type'         => QuestionType::RADIO,
                'question_type_answers' => [
                    [
                        'type_answer_body' => 'Yes',
                    ],
                    [
                        'type_answer_body' => 'No',
                    ],
                    [
                        'type_answer_body' => 'Maybe',
                    ],
                    [
                        'type_answer_body' => 'I already quit',
                    ],
                ],
            ],
            [
                'order'                 => 12,
                'question_body'         => 'Do you drink alcohol?',
                'question_type'         => QuestionType::RADIO,
                'question_type_answers' => [
                    [
                        'type_answer_body' => 'Yes',
                    ],
                    [
                        'type_answer_body' => 'No',
                    ],
                    [
                        'type_answer_body' => 'Yes, but i am now sober',
                    ],
                ],
            ],
            [
                'order'                 => 12,
                'sub_order'             => 'a',
                'question_body'         => 'On average, how many alcoholic beverages do you consume per week? (One standard drink is defined as 12.0 oz of beer, 5.0 oz of wine, or 1.5 oz of liquor)',
                'optional'              => true,
                'conditions'            => [
                    [
                        'related_question_order_number'    => 12,
                        'related_question_expected_answer' => 'Yes',
                    ],
                ],
                'question_type'         => QuestionType::RADIO,
                'question_type_answers' => [
                    [
                        'type_answer_body' => '0',
                    ],
                    [
                        'type_answer_body' => '<7 drinks per week',
                    ],
                    [
                        'type_answer_body' => '7-10 drinks per week',
                    ],
                    [
                        'type_answer_body' => '10-14 drinks per week',
                    ],
                    [
                        'type_answer_body' => '14+ drinks per week',
                    ],
                ],
            ],
            [
                'order'                 => 13,
                'question_body'         => 'Have you used recreational drugs in the past year?',
                'question_type'         => QuestionType::RADIO,
                'question_type_answers' => [
                    [
                        'type_answer_body' => 'Yes',
                        'options'          => [
                            'yes_or_no_question' => true,
                        ],
                    ],
                    [
                        'type_answer_body' => 'No',
                        'options'          => [
                            'yes_or_no_question' => true,
                        ],
                    ],
                ],
            ],
            [
                'order'                 => 13,
                'sub_order'             => 'a',
                'question_body'         => 'Which recreational drugs, and how often?',
                'optional'              => false,
                'conditions'            => [
                    [
                        'related_question_order_number'    => 13,
                        'related_question_expected_answer' => 'Yes',
                    ],
                ],
                'question_type'         => QuestionType::TEXT,
                'question_type_answers' => [
                    [
                        'options' => [
                            'sub_parts'                => [
                                [
                                    'title'       => 'Drug',
                                    'key'         => 'name',
                                    'placeholder' => 'Ex. cannabis',
                                ],
                                [
                                    'title'       => 'Frequency',
                                    'key'         => 'frequency',
                                    'placeholder' => 'Ex. 4 per month',
                                ],
                            ],
                            'allow_multiple'           => true,
                            'add_extra_answer_text'    => 'Add additional drug',
                            'remove_extra_answer_text' => 'Remove drug',
                        ],
                    ],
                ],
            ],
            [
                'order'                 => 14,
                'question_body'         => 'How often do you exercise?',
                'question_type'         => QuestionType::RADIO,
                'question_type_answers' => [
                    [
                        'type_answer_body' => 'Never',
                    ],
                    [
                        'type_answer_body' => '<3 times a week',
                    ],
                    [
                        'type_answer_body' => '3+ times a week',
                    ],
                    [
                        'type_answer_body' => 'Daily',
                    ],
                ],
            ],
            [
                'order'                 => 15,
                'question_body'         => 'Are you sexually active?',
                'question_type'         => QuestionType::RADIO,
                'question_type_answers' => [
                    [
                        'type_answer_body' => 'Yes',
                        'options'          => [
                            'yes_or_no_question' => true,
                        ],
                    ],
                    [
                        'type_answer_body' => 'No',
                        'options'          => [
                            'yes_or_no_question' => true,
                        ],
                    ],
                ],
            ],
            [
                'order'                 => 15,
                'sub_order'             => 'a',
                'question_body'         => 'Do you have multiple sexual partners',
                'optional'              => true,
                'conditions'            => [
                    [
                        'related_question_order_number'    => 15,
                        'related_question_expected_answer' => 'Yes',
                    ],
                ],
                'question_type'         => QuestionType::RADIO,
                'question_type_answers' => [
                    [
                        'type_answer_body' => 'Yes',
                        'options'          => [
                            'yes_or_no_question' => true,
                        ],
                    ],
                    [
                        'type_answer_body' => 'No',
                        'options'          => [
                            'yes_or_no_question' => true,
                        ],
                    ],
                ],
            ],
            [
                'order'                 => 15,
                'sub_order'             => 'b',
                'question_body'         => 'Are you practicing safe sex?',
                'optional'              => true,
                'conditions'            => [
                    [
                        'related_question_order_number'    => 15,
                        'related_question_expected_answer' => 'Yes',
                    ],
                ],
                'question_type'         => QuestionType::RADIO,
                'question_type_answers' => [
                    [
                        'type_answer_body' => 'Always',
                    ],
                    [
                        'type_answer_body' => 'Sometimes',
                    ],
                    [
                        'type_answer_body' => 'Never',
                    ],
                ],
            ],
            [
                'order'                 => 16,
                'question_body'         => 'Please check/uncheck if you have ever had the following conditions:',
                'question_type'         => QuestionType::CHECKBOX,
                'optional'              => true,
                'question_type_answers' => [
                    [
                        'type_answer_body' => 'Arrhythmia',
                        'options'          => [
                            'key' => 'name',
                        ],
                    ],
                    [
                        'type_answer_body' => 'Asthma',
                        'options'          => [
                            'key' => 'name',
                        ],
                    ],
                    [
                        'type_answer_body' => 'Cancer',
                        'options'          => [
                            'allow_custom_input' => true,
                            'placeholder'        => 'What type...',
                            'custom_input_key'   => 'type',
                            'key'                => 'name',
                        ],
                    ],
                    [
                        'type_answer_body' => 'Cognitive Impairment',
                        'options'          => [
                            'key' => 'name',
                        ],
                    ],
                    [
                        'type_answer_body' => 'Diabetes',
                        'options'          => [
                            'key' => 'name',
                        ],
                    ],
                    [
                        'type_answer_body' => 'Depression',
                        'options'          => [
                            'key' => 'name',
                        ],
                    ],
                    [
                        'type_answer_body' => 'Emphysema',
                        'options'          => [
                            'key' => 'name',
                        ],
                    ],
                    [
                        'type_answer_body' => 'Eye Problems',
                        'options'          => [
                            'allow_custom_input' => true,
                            'placeholder'        => 'What type...',
                            'custom_input_key'   => 'type',
                            'key'                => 'name',
                        ],
                    ],
                    [
                        'type_answer_body' => 'Heart Disease',
                        'options'          => [
                            'key' => 'name',
                        ],
                    ],
                    [
                        'type_answer_body' => 'Hepatitis',
                        'options'          => [
                            'key' => 'name',
                        ],
                    ],
                    [
                        'type_answer_body' => 'High Blood Pressure',
                        'options'          => [
                            'key' => 'name',
                        ],
                    ],
                    [
                        'type_answer_body' => 'High Cholesterol',
                        'options'          => [
                            'key' => 'name',
                        ],
                    ],
                    [
                        'type_answer_body' => 'Kidney Disease',
                        'options'          => [
                            'key' => 'name',
                        ],
                    ],
                    [
                        'type_answer_body' => 'Seizures',
                        'options'          => [
                            'key' => 'name',
                        ],
                    ],
                    [
                        'type_answer_body' => 'Sexually Transmitted Disease/Infection',
                        'options'          => [
                            'allow_custom_input' => true,
                            'placeholder'        => 'What type...',
                            'custom_input_key'   => 'type',
                            'key'                => 'name',
                        ],
                    ],
                    [
                        'type_answer_body' => 'Stroke',
                        'options'          => [
                            'key' => 'name',
                        ],
                    ],
                    [
                        'type_answer_body' => 'Thyroid Disease',
                        'options'          => [
                            'key' => 'name',
                        ],
                    ],
                ],
            ],
            [
                'order'                 => 17,
                'question_body'         => 'If you have any medical problems or serious injuries that were not listed above, please describe them here',
                'question_type'         => QuestionType::TEXT,
                'optional'              => true,
                'question_type_answers' => [
                    [
                        'options' => [
                            'placeholder'              => 'Type response here...',
                            'allow_multiple'           => true,
                            'add_extra_answer_text'    => 'Add additional response',
                            'remove_extra_answer_text' => 'Remove response',
                        ],
                    ],
                ],
            ],
            [
                'order'                 => 18,
                'question_body'         => 'Please check to indicate if you have ever had any of the following conditions in your family?',
                'question_type'         => QuestionType::CHECKBOX,
                'optional'              => true,
                'question_type_answers' => [
                    [
                        'type_answer_body' => 'Alcoholism or Drug Use',
                    ],
                    [
                        'type_answer_body' => 'Breast Cancer',
                    ],
                    [
                        'type_answer_body' => 'Colorectal Cancer',
                    ],
                    [
                        'type_answer_body' => 'Cognitive Impairment',
                    ],
                    [
                        'type_answer_body' => 'Diabetes',
                    ],
                    [
                        'type_answer_body' => 'Depression',
                    ],
                    [
                        'type_answer_body' => 'Heart Disease',
                    ],
                    [
                        'type_answer_body' => 'High Blood Pressure',
                    ],
                    [
                        'type_answer_body' => 'High Cholesterol',
                    ],
                    [
                        'type_answer_body' => 'Mental Illness',
                    ],
                    [
                        'type_answer_body' => 'Osteoporosis',
                    ],
                    [
                        'type_answer_body' => 'Prostate Cancer',
                    ],
                    [
                        'type_answer_body' => 'Skin Cancer',
                    ],
                    [
                        'type_answer_body' => 'Stroke',
                    ],
                    [
                        'type_answer_body' => 'Thyroid Disease',
                    ],
                ],
            ],
            [
                'order'                 => 18,
                'sub_order'             => 'a',
                'question_body'         => 'Who in your family has had:',
                'question_type'         => QuestionType::MULTI_SELECT,
                'conditions'            => [
                    [
                        'related_question_order_number' => 18,
                        //accept any answer
                    ],
                ],
                'question_type_answers' => [
                    [
                        'options' => [
                            'import_answers_from_question' => [
                                'question_order' => 18,
                            ],
                            'allow_multiple_from_answers'  => true,
                            'multi_select_options'         => [
                                'Mother',
                                'Father',
                                'Sibling',
                                'Maternal Grandmother',
                                'Maternal Grandfather',
                                'Paternal Grandmother',
                                'Paternal Grandfather',
                                'Child',
                            ],
                            'placeholder'                  => 'Choose individuals here...',
                            'multi_select_key'             => 'family',
                            'key'                          => 'name',

                        ],
                    ],
                ],
            ],
            [
                'order'                 => 19,
                'optional'              => true,
                'question_body'         => 'Please list any surgeries/hospital stays you have had and their approximate date/year:',
                'question_type'         => QuestionType::TEXT,
                'question_type_answers' => [
                    [
                        'options' => [
                            'sub_parts'                => [
                                [
                                    'title'       => 'Reason for Visit',
                                    'placeholder' => 'Type response here...',
                                    'key'         => 'reason',
                                ],
                                [
                                    'title'       => 'Location',
                                    'placeholder' => 'Type response here...',
                                    'key'         => 'location',
                                ],
                                [
                                    'title'       => 'Year',
                                    'placeholder' => 'Type response here...',
                                    'key'         => 'year',
                                ],
                            ],
                            'allow_multiple'           => true,
                            'add_extra_answer_text'    => 'Add additional response',
                            'remove_extra_answer_text' => 'Remove response',
                        ],
                    ],
                ],
            ],
            [
                'order'                 => 20,
                'optional'              => true,
                'question_body'         => 'If you are taking any medications regularly, please list them here, including over-the-counter pharmaceuticals:',
                'question_type'         => QuestionType::TEXT,
                'question_type_answers' => [
                    [
                        'options' => [
                            'sub_parts'                => [
                                [
                                    'title'       => 'Drug',
                                    'key'         => 'drug',
                                    'placeholder' => 'Type response here...',
                                ],
                                [
                                    'title'       => 'Dose',
                                    'key'         => 'dose',
                                    'placeholder' => 'Type response here...',
                                ],
                                [
                                    'title'       => 'Frequency',
                                    'key'         => 'frequency',
                                    'placeholder' => 'Type response here...',
                                ],
                            ],
                            'allow_multiple'           => true,
                            'add_extra_answer_text'    => 'Add additional response',
                            'remove_extra_answer_text' => 'Remove response',
                        ],
                    ],
                ],
            ],
            [
                'order'                 => 21,
                'optional'              => true,
                'question_body'         => 'Please list any allergies or reactions:',
                'question_type'         => QuestionType::TEXT,
                'question_type_answers' => [
                    [
                        'options' => [
                            'title'                    => 'Allergy',
                            'placeholder'              => 'Type response here...',
                            'allow_multiple'           => true,
                            'add_extra_answer_text'    => 'Add additional response',
                            'remove_extra_answer_text' => 'Remove response',
                            'key'                      => 'name',
                        ],
                    ],
                ],
            ],
            [
                'order'                 => 22,
                'sub_order'             => '1',
                'question_body'         => 'Little interest or pleasure in doing things',
                'question_group'        => 'Over the past 2 weeks, how often have you been bothered by any of the following problems?',
                'question_type'         => QuestionType::RADIO,
                'question_type_answers' => [
                    [
                        'type_answer_body' => 'Not at all',
                    ],
                    [
                        'type_answer_body' => 'Several days',
                    ],
                    [
                        'type_answer_body' => 'More than half the days',
                    ],
                    [
                        'type_answer_body' => 'Nearly every day',
                    ],
                ],
            ],
            [
                'order'                 => 22,
                'sub_order'             => '2',
                'question_body'         => 'Feeling down, depressed or hopeless',
                'question_group'        => 'Over the past 2 weeks, how often have you been bothered by any of the following problems?',
                'question_type'         => QuestionType::RADIO,
                'question_type_answers' => [
                    [
                        'type_answer_body' => 'Not at all',
                    ],
                    [
                        'type_answer_body' => 'Several days',
                    ],
                    [
                        'type_answer_body' => 'More than half the days',
                    ],
                    [
                        'type_answer_body' => 'Nearly every day',
                    ],
                ],
            ],
            [
                'order'                 => 23,
                'question_body'         => 'Please check to indicate if you have ever had difficulty/needed help performing any of the following tasks:',
                'question_type'         => QuestionType::CHECKBOX,
                'optional'              => true,
                'question_type_answers' => [
                    [
                        'type_answer_body' => 'Standing from a sitting position',
                    ],
                    [
                        'type_answer_body' => 'Walking in the house',
                    ],
                    [
                        'type_answer_body' => 'Walking outside of the house',
                    ],
                    [
                        'type_answer_body' => 'Eating a meal',
                    ],
                    [
                        'type_answer_body' => 'Preparing a meal',
                    ],
                    [
                        'type_answer_body' => 'Getting dressed',
                    ],
                    [
                        'type_answer_body' => 'Bathing',
                    ],
                    [
                        'type_answer_body' => 'Using the toilet',
                    ],
                    [
                        'type_answer_body' => 'Organizing your day',
                    ],
                    [
                        'type_answer_body' => 'Driving or getting to places',
                    ],
                ],
            ],
            [
                'order'                 => 23,
                'sub_order'             => 'a',
                'question_body'         => 'If you answered yes to any of the above, do you have someone who can assist you?',
                'question_type'         => QuestionType::RADIO,
                'optional'              => true,
                'conditions'            => [
                    [
                        'related_question_order_number' => 23,
                        //accept any answer
                    ],
                ],
                'question_type_answers' => [
                    [
                        'type_answer_body' => 'Yes',
                        'options'          => [
                            'yes_or_no_question' => true,
                        ],
                    ],
                    [
                        'type_answer_body' => 'No',
                        'options'          => [
                            'yes_or_no_question' => true,
                        ],
                    ],
                ],
            ],
            [
                'order'                 => 24,
                'question_body'         => 'Have you fallen in the past 6 months? (a fall is when the body goes to the ground without being pushed)',
                'question_type'         => QuestionType::RADIO,
                'question_type_answers' => [
                    [
                        'type_answer_body' => 'Yes',
                        'options'          => [
                            'yes_or_no_question' => true,
                        ],
                    ],
                    [
                        'type_answer_body' => 'No',
                        'options'          => [
                            'yes_or_no_question' => true,
                        ],
                    ],
                ],
            ],
            [
                'order'                 => 25,
                'question_body'         => 'Do you have difficulty with your hearing?',
                'question_type'         => QuestionType::RADIO,
                'question_type_answers' => [
                    [
                        'type_answer_body' => 'Yes',
                    ],
                    [
                        'type_answer_body' => 'Sometimes',
                    ],
                    [
                        'type_answer_body' => 'No',
                    ],
                ],
            ],
            [
                'order'                 => 26,
                'question_body'         => 'Have you had a flu shot this year or are you planning to receive one this year?',
                'question_type'         => QuestionType::RADIO,
                'question_type_answers' => [
                    [
                        'type_answer_body' => 'Yes',
                        'options'          => [
                            'yes_or_no_question' => true,
                        ],
                    ],
                    [
                        'type_answer_body' => 'No',
                        'options'          => [
                            'yes_or_no_question' => true,
                        ],
                    ],
                ],
            ],
            [
                'order'                 => 27,
                'question_body'         => 'Have you received a Tdap Vaccination (for Tetanus, Diphtheria, and Pertussis)?',
                'question_type'         => QuestionType::RADIO,
                'question_type_answers' => [
                    [
                        'type_answer_body' => 'Yes',
                    ],
                    [
                        'type_answer_body' => 'No',
                    ],
                    [
                        'type_answer_body' => 'Unsure',
                    ],
                ],
            ],
            [
                'order'                 => 28,
                'question_body'         => 'Have you received a Tetanus Diphtheria/Td BOOSTER (separate from Tdap vaccine) in the past 10 years?',
                'question_type'         => QuestionType::RADIO,
                'question_type_answers' => [
                    [
                        'type_answer_body' => 'Yes',
                    ],
                    [
                        'type_answer_body' => 'No',
                    ],
                    [
                        'type_answer_body' => 'Unsure',
                    ],
                ],
            ],
            [
                'order'                 => 29,
                'question_body'         => 'Have you received a Varicella vaccination (for Chickenpox)?',
                'question_type'         => QuestionType::RADIO,
                'question_type_answers' => [
                    [
                        'type_answer_body' => 'Yes',
                    ],
                    [
                        'type_answer_body' => 'No',
                    ],
                    [
                        'type_answer_body' => 'Unsure',
                    ],
                ],
            ],
            [
                'order'                 => 30,
                'question_body'         => 'Have you received a Hepatitis B Vaccination?',
                'question_type'         => QuestionType::RADIO,
                'question_type_answers' => [
                    [
                        'type_answer_body' => 'Yes',
                    ],
                    [
                        'type_answer_body' => 'No',
                    ],
                    [
                        'type_answer_body' => 'Unsure',
                    ],
                ],
            ],
            [
                'order'                 => 31,
                'question_body'         => 'Have you received 2 doses of Measles Mumps Rubella (MMR) Vaccination?',
                'question_type'         => QuestionType::RADIO,
                'question_type_answers' => [
                    [
                        'type_answer_body' => 'Yes',
                    ],
                    [
                        'type_answer_body' => 'No',
                    ],
                    [
                        'type_answer_body' => 'Unsure',
                    ],
                ],
            ],
            [
                'order'                 => 32,
                'question_body'         => 'Have you received 2 doses of Human Papillomavirus (HPV) Vaccination before age 15 OR 3 doses between ages 15 and 26?',
                'question_type'         => QuestionType::RADIO,
                'question_type_answers' => [
                    [
                        'type_answer_body' => 'Yes',
                    ],
                    [
                        'type_answer_body' => 'No',
                    ],
                    [
                        'type_answer_body' => 'Unsure',
                    ],
                ],
            ],
            [
                'order'                 => 33,
                'question_body'         => 'Have you received 2 doses of RZV OR 1 dose of ZVL (for Shingles/Herpes zoster)?',
                'question_type'         => QuestionType::RADIO,
                'question_type_answers' => [
                    [
                        'type_answer_body' => 'Yes',
                    ],
                    [
                        'type_answer_body' => 'No',
                    ],
                    [
                        'type_answer_body' => 'Unsure',
                    ],
                ],
            ],
            [
                'order'                 => 34,
                'question_body'         => 'Have you received 1 dose of PCV13 and at least 1 dose of PPSV23 (for Pneumococccal Infection (Pneumonia, blood infection, sinus, meningitis))?',
                'question_type'         => QuestionType::RADIO,
                'question_type_answers' => [
                    [
                        'type_answer_body' => 'Yes',
                    ],
                    [
                        'type_answer_body' => 'No',
                    ],
                    [
                        'type_answer_body' => 'Unsure',
                    ],
                ],
            ],
            [
                'order'                 => 35,
                'question_body'         => 'When was the last time you had a Breast Cancer Screening (Mammogram)?',
                'question_type'         => QuestionType::RADIO,
                'conditions'            => [
                    [
                        'related_question_order_number'    => 4,
                        'related_question_expected_answer' => 'Female',
                    ],
                    [
                        'related_question_order_number'    => 4,
                        'related_question_expected_answer' => 'Transgender',
                    ],
                    [
                        'related_question_order_number'    => 4,
                        'related_question_expected_answer' => 'Other',
                    ],
                ],
                'question_type_answers' => [
                    [
                        'type_answer_body' => 'In the last year',
                    ],
                    [
                        'type_answer_body' => 'In the last 2-3 years',
                    ],
                    [
                        'type_answer_body' => 'In the last 4-5 years',
                    ],
                    [
                        'type_answer_body' => 'In the last 6-10 years',
                    ],
                    [
                        'type_answer_body' => '10+ years ago/Never/Unsure',
                    ],
                ],
            ],
            [
                'order'                 => 36,
                'question_body'         => 'When was the last time you had a Cervical cancer Screening (Pap Smear)?',
                'question_type'         => QuestionType::RADIO,
                'conditions'            => [
                    [
                        'related_question_order_number'    => 4,
                        'related_question_expected_answer' => 'Female',
                    ],
                    [
                        'related_question_order_number'    => 4,
                        'related_question_expected_answer' => 'Transgender',
                    ],
                    [
                        'related_question_order_number'    => 4,
                        'related_question_expected_answer' => 'Other',
                    ],
                ],
                'question_type_answers' => [
                    [
                        'type_answer_body' => 'In the last year',
                    ],
                    [
                        'type_answer_body' => 'In the last 2-3 years',
                    ],
                    [
                        'type_answer_body' => 'In the last 4-5 years',
                    ],
                    [
                        'type_answer_body' => 'In the last 6-10 years',
                    ],
                    [
                        'type_answer_body' => '10+ years ago/Never/Unsure',
                    ],
                ],
            ],
            [
                'order'                 => 37,
                'question_body'         => 'When was the last time you had a Colorectal Cancer Screening (e.g. Fecal Occult Blood Test (FOBT), Fecal Immunochemistry Testing (FIT), Sigmoidoscopy, Colonoscopy)?',
                'question_type'         => QuestionType::RADIO,
                'question_type_answers' => [
                    [
                        'type_answer_body' => 'In the last year',
                    ],
                    [
                        'type_answer_body' => 'In the last 2-3 years',
                    ],
                    [
                        'type_answer_body' => 'In the last 4-5 years',
                    ],
                    [
                        'type_answer_body' => 'In the last 6-10 years',
                    ],
                    [
                        'type_answer_body' => '10+ years ago/Never/Unsure',
                    ],
                ],
            ],
            [
                'order'                 => 38,
                'question_body'         => 'When was the last time you had a Skin Cancer Screening?',
                'question_type'         => QuestionType::RADIO,
                'question_type_answers' => [
                    [
                        'type_answer_body' => 'In the last year',
                    ],
                    [
                        'type_answer_body' => 'In the last 2-3 years',
                    ],
                    [
                        'type_answer_body' => 'In the last 4-5 years',
                    ],
                    [
                        'type_answer_body' => 'In the last 6-10 years',
                    ],
                    [
                        'type_answer_body' => '10+ years ago/Never/Unsure',
                    ],
                ],
            ],
            [
                'order'                 => 39,
                'question_body'         => 'When was the last time you had a Prostate Cancer Screening (Prostate specific antigen (PSA))?',
                'question_type'         => QuestionType::RADIO,
                'conditions'            => [
                    [
                        'related_question_order_number'    => 4,
                        'related_question_expected_answer' => 'Male',
                    ],
                    [
                        'related_question_order_number'    => 4,
                        'related_question_expected_answer' => 'Transgender',
                    ],
                    [
                        'related_question_order_number'    => 4,
                        'related_question_expected_answer' => 'Other',
                    ],
                ],
                'question_type_answers' => [
                    [
                        'type_answer_body' => 'In the last year',
                    ],
                    [
                        'type_answer_body' => 'In the last 2-3 years',
                    ],
                    [
                        'type_answer_body' => 'In the last 4-5 years',
                    ],
                    [
                        'type_answer_body' => 'In the last 6-10 years',
                    ],
                    [
                        'type_answer_body' => '10+ years ago/Never/Unsure',
                    ],
                ],
            ],
            [
                'order'                 => 40,
                'question_body'         => 'When was the last time you had a Glaucoma Screening?',
                'question_type'         => QuestionType::RADIO,
                'question_type_answers' => [
                    [
                        'type_answer_body' => 'In the last year',
                    ],
                    [
                        'type_answer_body' => 'In the last 2-3 years',
                    ],
                    [
                        'type_answer_body' => 'In the last 4-5 years',
                    ],
                    [
                        'type_answer_body' => 'In the last 6-10 years',
                    ],
                    [
                        'type_answer_body' => '10+ years ago/Never/Unsure',
                    ],
                ],
            ],
            [
                'order'                 => 41,
                'question_body'         => 'When was the last time you had a Osteoporosis Screening (Bone Density Test)?',
                'question_type'         => QuestionType::RADIO,
                'question_type_answers' => [
                    [
                        'type_answer_body' => 'In the last year',
                    ],
                    [
                        'type_answer_body' => 'In the last 2-3 years',
                    ],
                    [
                        'type_answer_body' => 'In the last 4-5 years',
                    ],
                    [
                        'type_answer_body' => 'In the last 6-10 years',
                    ],
                    [
                        'type_answer_body' => '10+ years ago/Never/Unsure',
                    ],
                ],
            ],
            [
                'order'                 => 42,
                'question_body'         => 'When was the last time you had an Intimate Partner Violence/Domestic Violence Screening?',
                'question_type'         => QuestionType::RADIO,
                'question_type_answers' => [
                    [
                        'type_answer_body' => 'In the last year',
                    ],
                    [
                        'type_answer_body' => 'In the last 2-3 years',
                    ],
                    [
                        'type_answer_body' => 'In the last 4-5 years',
                    ],
                    [
                        'type_answer_body' => 'In the last 6-10 years',
                    ],
                    [
                        'type_answer_body' => '10+ years ago/Never/Unsure',
                    ],
                ],
            ],
            [
                'order'                 => 43,
                'question_body'         => 'List the Physicians and Specialists you currently see. Please list the names, locations and phone numbers as best as you can.',
                'question_type'         => QuestionType::TEXT,
                'question_type_answers' => [
                    [
                        'options' => [
                            'sub_parts'                => [
                                [
                                    'title'       => 'Provider Name',
                                    'key'         => 'provider_name',
                                    'placeholder' => 'Type response here...',
                                ],
                                [
                                    'title'       => 'Specialty',
                                    'key'         => 'specialty',
                                    'placeholder' => 'Type response here...',
                                ],
                                [
                                    'title'       => 'Location',
                                    'key'         => 'location',
                                    'placeholder' => 'Type response here...',
                                ],
                                [
                                    'title'       => 'Phone Number',
                                    'key'         => 'phone_number',
                                    'placeholder' => '(123) 456 7890',
                                ],
                            ],
                            'allow_multiple'           => true,
                            'add_extra_answer_text'    => 'Add additional response',
                            'remove_extra_answer_text' => 'Remove response',
                        ],
                    ],
                ],
            ],
            [
                'order'                 => 44,
                'question_body'         => 'Do you have a Medical Power of Attorney? (Someone to make medical decisions for you in the event you are unable to)',
                'question_type'         => QuestionType::RADIO,
                'question_type_answers' => [
                    [
                        'type_answer_body' => 'Yes',
                    ],
                    [
                        'type_answer_body' => 'No',
                    ],
                    [
                        //todo: empty on zepelin ask raph
                        'type_answer_body' => 'Unsure',
                    ],
                ],
            ],
            [
                'order'                 => 45,
                'question_body'         => 'Do you have a living will/advance directive? (Documents that make your health care wishes known)',
                'question_type'         => QuestionType::RADIO,
                'question_type_answers' => [
                    [
                        'type_answer_body' => 'Yes',
                    ],
                    [
                        'type_answer_body' => 'No',
                    ],
                    [
                        'type_answer_body' => 'Unsure',
                    ],
                ],
            ],
            [
                'order'                 => 45,
                'sub_order'             => 'a',
                'question_body'         => "Is a copy of your advance directive on file at your doctor's office?",
                'optional'              => true,
                'conditions'            => [
                    [
                        'related_question_order_number'    => 45,
                        'related_question_expected_answer' => 'Yes',
                    ],
                ],
                'question_type'         => QuestionType::RADIO,
                'question_type_answers' => [
                    [
                        'type_answer_body' => 'Yes',
                    ],
                    [
                        'type_answer_body' => 'No',
                    ],
                    [
                        'type_answer_body' => 'Unsure',
                    ],
                ],
            ],
            [
                'order'                 => 46,
                'question_body'         => 'Do you have any other questions or concerns that you would like to speak to your provider about at your next Annual Wellness Visit?',
                'question_type'         => QuestionType::TEXT,
                'question_type_answers' => [
                    [
                        'options' => [
                            'title'          => '',
                            'placeholder'    => 'Type response here...',
                            'allow_multiple' => false,
                            'key'            => 'name',
                        ],
                    ],
                ],
            ],
        ]);
    }
}
