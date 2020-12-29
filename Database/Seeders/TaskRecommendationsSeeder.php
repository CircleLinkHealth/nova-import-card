<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TaskRecommendationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public static function run()
    {
        self::createTaskRecommendationData();
    }

    private static function createTaskRecommendationData()
    {
        $taskRecommendations = self::taskRecommendationData();

        foreach ($taskRecommendations as $taskRecommendation) {
            DB::table('ppp_task_recommendations')->updateOrInsert(
                [
                    'title' => $taskRecommendation['title'],
                ],
                [
                    'data'       => json_encode($taskRecommendation['data']),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    private static function taskRecommendationData(): Collection
    {
        return collect([
            // NUTRITION
            [
                'title' => 'Nutrition',
                'data'  => [
                    'fruits_veggies' => [
                        'qualitative_trigger' => 'Fruits / Veggies',
                        'task_body'           => 'Fruits and vegetables are important part of healthy eating and provide a source of many nutrients, including potassium, fiber, folate (folic acid) and vitamins A, E and C. People who eat fruit and vegetables as part of their daily diet have a reduced risk of many chronic diseases. Your doctor may recommend:',
                        'recommendation_body' => ['Getting 4-5 servings of fruits and vegetables a day'],
                    ],
                    'whole_grain' => [
                        'qualitative_trigger' => 'Whole Grains',
                        'task_body'           => 'Foods made from grains (wheat, rice, and oats) help form the foundation of a nutritious diet. They provide vitamins, minerals, carbohydrates (starch and dietary fiber), and other substances that are important for good health. Eating plenty of whole grains, such as whole wheat bread or oatmeals may help protect you against many chronic diseases. Experts recommend that all adults eat at least half their grains as whole grains. Your doctor may suggest:',
                        'recommendation_body' => ['Aiming for at least 3 servings of whole grains a day'],
                    ],
                    'fatty_fried_foods' => [
                        'qualitative_trigger' => 'Fatty / Fried Foods',
                        'task_body'           => 'A small amount of fat is an essential part of a healthy, balanced diet. Although It\'s fine to enjoy fats, fried foods and sweets occasionally, too much sugar and saturated fat in your diet can raise your cholesterol. This increases the risk of heart disease. Your doctor may recommend:',
                        'recommendation_body' => ['Cutting down consumption to <1 servings of fried and high-fat foods a day'],
                    ],
                    'candy_sugary_beverages' => [
                        'qualitative_trigger' => 'Candy / Sugary Beverages',
                        'task_body'           => 'The average can of sugar-sweetened (sucrose, high-fructose corn syrup, dextrose, cane sugar etc.) soda or fruit punch provides about 150 calories, almost all of them from sugar, usually high-fructose corn syrup. That’s the equivalent of 10 teaspoons of table sugar. If you were to drink just one can of a sugar-sweetened soft drink every day, and not cut back on calories elsewhere, you could gain up to 5 pounds in a year. People who drink sugary beverages do not feel as full as if they had eaten the same calories from solid food, and studies show that people consuming sugary beverages don’t compensate for their high caloric content by eating less food.  Your doctor may recommend:',
                        'recommendation_body' => ['Cutting down consumption to <1 servings of sugar-sweetened beverages / sweets a day'],
                    ],
                ],
            ],

            // Tobacco Smoking
            [
                'title' => 'Tobacco / Smoking',
                'data'  => [
                    'current_smoker' => [
                        'qualitative_trigger' => 'Current Smoker',
                        'task_body'           => 'Smoking, or the use of any tobacco products harms nearly every organ of the body, causes many diseases, and reduces the health of smokers in general. Smokers are more likely than nonsmokers to develop heart disease, stroke, and lung cancer. Cigarette smoking is the leading preventable cause of death in the United States, causing more than 480,000 deaths domestically each year. This includes about 90% of all lung cancer deaths, and about 80% of all deaths from chronic obstructive pulmonary disease (COPD). Quitting smoking lowers your risk for smoking-related diseases and can add years to your life. Talk to your doctor about what interventions you may be able to use to help you quit. These may include:',
                        'recommendation_body' => [
                            'Counseling and / or pharmacotherapy interventions',
                            'Lung cancer screening (precautionary)', ],

                        'report_table_data' => [
                            [
                                'body'       => 'Smoking Counseling',
                                'code'       => '99406 / 99407',
                                'time_frame' => 'As Needed',
                            ],
                            [
                                'body'       => 'Smoking Pharmacotherapy',
                                'code'       => 'Various',
                                'time_frame' => 'As Needed',
                            ],
                            [
                                'body'       => 'Lung cancer screening (precautionary)',
                                'code'       => '71250 / Z12.2',
                                'time_frame' => 'As Needed',
                            ],
                        ],
                    ],
                    'current_male_smoker' => [
                        'qualitative_trigger' => '',
                        'task_body'           => 'Due to your age, sex, and smoking status, your Doctor may also recommend an:',
                        'recommendation_body' => ['AAA (Abdominal Aortic Aneurysm) screening'],
                        'report_table_data'   => [
                            [
                                'body'       => 'AAA (Abdominal Aortic Aneurysm) screening',
                                'code'       => '76706',
                                'time_frame' => 'As Needed',
                            ],
                        ],
                    ],
                    'former_smoker' => [
                        'qualitative_trigger' => 'Former Smoker',
                        'task_body'           => 'Congrats! Having quit smoking is a great achievement. By avoiding smoking, you are lowering your risk of smoking-related illnesses every day. Quitting smoking has health benefits that start right away and improve over many years. Unfortunately, smoking any amount can cause damage that can lead to health problems. The risk of lung cancer decreases over time, though it remains higher than a non-smoker’s. As a result, your doctor may suggest:',
                        'recommendation_body' => ['Lung cancer screening (precautionary)'],
                        'report_table_data'   => [
                            [
                                'body'       => 'Lung cancer screening (precautionary)',
                                'code'       => '71250 / Z12.2',
                                'time_frame' => 'As Needed',
                            ],
                        ],
                    ],
                ],
            ],

            // Alcohol
            [
                'title' => 'Alcohol',
                'data'  => [
                    'alcohol_dependent' => [
                        'qualitative_trigger' => 'Alcohol',
                        'task_body'           => 'Drinking too much – on a single occasion or over time – can take a serious toll on your health.Having more than a 7 drinks a week for females, or more than 14 drinks a week for males may result in health complications including liver disease, pancreatitis, cancer, and ulcers / other GI problems.Talk to your doctor about ways to cut down on alcohol consumption, they may prescribe:',
                        'recommendation_body' => [
                            'Counseling ranging from brief single contact to extended multicontact counseling',
                            'Liver disease screening',
                        ],
                    ],
                ],
            ],

            // Drug Use
            [
                'title'       => 'Recreational Drug use',
                'task_titles' => [
                    [
                        'body'       => 'Counseling (Drug Use)',
                        'code'       => 'G0396 / G0397',
                        'time_frame' => 'As Needed',
                    ],
                ],
                'data' => [
                    'recreational_drugs' => [
                        'qualitative_trigger' => 'Recreational Drugs',
                        'task_body'           => 'Recreational drug use can be hazardous to your health. When you use street or club drugs, usually there’s no way to know how strong they are or what else may be in them. It\'s even more unsafe to use them along with other substances like alcohol and marijuana. Your doctor can talk to you about ways to stop your recreational drug use, for example:',
                        'recommendation_body' => ['Counseling'],
                    ],
                ],
            ],

            // Physical Activity
            [
                'title'       => 'Physical Activity',
                'task_titles' => [],
                'data'        => [
                    'less_three_week_young' => [
                        'qualitative_trigger' => ' Exercise',
                        'task_body'           => 'Everyone benefits from exercise, regardless of age, sex or physical ability. Exercise can help prevent excess weight gain or help maintain weight loss. It is a fundamental component of healthy active living and involves 3 major components. Aerobic (running, swimming, biking etc.), Resistance (pushups, squats, planks etc.) and Balance training (tai chi, etc.) No matter what your current weight, being active boosts high-density lipoprotein (HDL), or "good," cholesterol and decreases unhealthy triglycerides, decreasing your risk of cardiovascular diseases. Exercise also improves mood, energy, sleep quality, and sex life. Ideally, aim for:	',
                        'recommendation_body' => [
                            'At least 2 hours and 30 minutes to 5 hours a week of moderate-intensity OR 1 hour and 15 minutes to 2 hours and 30 minutes a week of vigorous-intensity aerobic physical activity OR an equivalent combination of moderate- and vigorous-intensity aerobic activity.',
                            'Preferably, aerobic activity should be spread throughout the week',
                            'Muscle-strengthening activities of moderate or greater intensity and that involve all major muscle groups 2+  days a week',
                            'Remember, any exercise is better than no exercise, and additional health benefits are gained by engaging in physical activity beyond the minimum requirements!',
                        ],
                    ],
                    'less_three_week_older' => [
                        'qualitative_trigger' => 'Exercise',
                        'task_body'           => 'Everyone benefits from exercise, regardless of age, sex or physical ability. Exercise can help prevent excess weight gain or help maintain weight loss. It is a fundamental component of healthy active living and involves 3 major components. Aerobic (running, swimming, biking etc.), Resistance (pushups, squats, planks etc.) and Balance training (tai chi, etc.) No matter what your current weight, being active boosts high-density lipoprotein (HDL), or "good," cholesterol and decreases unhealthy triglycerides, decreasing your risk of cardiovascular diseases. Exercise also improves mood, energy, sleep quality, and sex life. Ideally, aim for:',
                        'recommendation_body' => ['At least 2 hours and 30 minutes to 5 hours a week of moderate-intensity
OR 1 hour and 15 minutes to 2 hours and 30 minutes a week of vigorous-intensity aerobic physical activity
OR an equivalent combination of moderate- and vigorous-intensity aerobic activity.
If you cannot do this because of chronic conditions, be as physically active as their abilities and conditions safely allow.',
                            'Preferably, aerobic activity should be spread throughout the week',
                            'Muscle-strengthening activities of moderate or greater intensity and that involve all major muscle groups 2+ days a week',
                            'Multicomponent physical activity that includes balance training as well as aerobic and muscle-strengthening activities',
                            'Remember, any exercise is better than no exercise, and additional health benefits are gained by engaging in physical activity beyond the minimum requirements, but avoid injury by keeping the intensity and frequency manageable!',
                        ],
                    ],
                ],
            ],

            // Weight/BMI
            [
                'title'       => 'Weight / BMI',
                'task_titles' => [],
                'data'        => [
                    'bmi_low' => [
                        'qualitative_trigger' => 'Weight / BMI',
                        'task_body'           => 'Because many of the health problems in America are associated with the high prevalence of obesity, it is easy to forget that being underweight also carries health risks. Although there are many reasons for being underweight, the condition suggests that your body is not obtaining sufficient nutrients to sustain proper function. A body mass index, or BMI, below 18.5 means a person is underweight and this is associated with health complications such as bone loss, decreased immunity, cardiac problems, and infertility. Since you have a BMI on the lower side, you should consider gaining weight and monitor your weight once a month. Your doctor:',
                        'recommendation_body' => ['Can tell you about healthy ways to gain weight gradually',
                            'May require tests like a basic metabolic panel', ],
                    ],
                    'bmi_high' => [
                        'qualitative_trigger' => 'Weight / BMI',
                        'task_body'           => 'BMI is an estimate of body fat and a good gauge of your risk for diseases that can occur with more body fat. The higher your BMI, the higher your risk for certain diseases such as heart disease, high blood pressure, type 2 diabetes, gallstones, breathing problems, and certain cancers.  Since you have a BMI on the higher side, you should work on reducing your weight. Even a small weight loss (between 5 and 10 percent of your current weight) will lower your risk of developing diseases associated with obesity. Your provider can recommend some lifestyle changes and routine tests, such as:',
                        'recommendation_body' => ['Changes to diet, exercise, counseling, or certain drugs',
                            'Diabetes and cholesterol blood tests', ],
                    ],
                ],
            ],

            // SEXUAL PRACTICES
            [
                'title'       => 'Sexual Practices',
                'task_titles' => [],
                'data'        => [
                    'unprotected_sex' => [
                        'qualitative_trigger' => 'Unprotected sex',
                        'task_body'           => 'If you are sexually active with multiple sexual partners, you should be diligent in protecting yourself from sexually transmitted infections (STI) and diseases. Vaginal or anal penetration by an infected partner who isn\'t wearing a latex condom significantly increases the risk of getting an STI. Improper or inconsistent use of condoms can also increase your risk. When being sexually active, make sure to use protection or ask your partner to use protection. Your doctor may suggest:',
                        'recommendation_body' => [
                            'Enrolling in sexual health counseling',
                            'And / or taking HIV and / or STI tests ',
                        ],
                        'report_table_data' => [
                            [
                                'body'       => 'Counseling (Sexual Health)',
                                'code'       => 'G0445',
                                'time_frame' => 'As Needed',
                            ],
                            [
                                'body'       => 'STI Testing',
                                'code'       => 'Various',
                                'time_frame' => 'As Needed',
                            ],
                        ],
                    ],
                    'reproductive_age' => [
                        'qualitative_trigger' => '',
                        'task_body'           => 'As you are a woman of reproductive age, your doctor may also suggest an:',
                        'recommendation_body' => ['Intimate Partner Violence (IPV) screening'],
                        'report_table_data'   => [
                            [
                                'body'       => 'Intimate Partner Violence (IPV) screening',
                                'code'       => 'Various',
                                'time_frame' => 'As Needed',
                            ],
                        ],
                    ],
                ],
            ],

            // Emotional Health
            [
                'title'       => 'Emotional Health',
                'task_titles' => [],

                'data' => [
                    'emotional_health' => [
                        'qualitative_trigger' => 'Mood',
                        'task_body'           => 'Being depressed often feels like carrying a very heavy burden, but you are not alone in this struggle. Millions of Americans suffer from some form of depression every year, making it one of the most common mental disorders in the country. Depression is more than just feeling sad. Everyone feels upset or unmotivated from time to time, but depression is more serious. It is a mood disorder characterized by prolonged feelings of sadness and loss of interest in daily activities. If these symptoms persist for a period of at least two weeks, it is considered a depressive episode. As some of your responses have indicated that you sometimes exhibit feelings of depression, you doctor will talk to you about whether you should seek mental health treatment via:',
                        'recommendation_body' => ['PHQ-9 Questionnaire',
                            'Specific psychotherapy approaches (e.g. CBT or brief psychosocial counseling), alone or in combination',
                            'Medication', ],
                    ],
                ],
            ],

            // Fall Risk
            [
                'title'       => 'Fall Risk',
                'task_titles' => [],

                'data' => [
                    'fall_risk' => [
                        'qualitative_trigger' => 'Fall Risk',
                        'task_body'           => 'If you have fallen in the last 6 months, you\'re not alone. More than one in three people age 65 years or older falls each year. The risk of falling—and fall-related problems—rises with age. But don\'t let a fear of falling keep you from being active. Simple treatments and overcoming this fear can help you stay active, maintain your physical health, and prevent future falls. Since you have fallen in the last six months, it is important to work on strengthening your bones. Your doctor may recommend:',
                        'recommendation_body' => [
                            'Counseling and / or physical therapy',
                            'Calcium or vitamin D supplements',
                            'Bone density test',
                            'Home fall risk evaluation by home health (may include physical therapy, occupational  therapy, home inspection to determine if your house (rugs, cords, etc) are hazards for falls etc.)',
                            'Balance exercises',
                        ],
                        'report_table_data' => [
                            [
                                'body'       => 'Home fall risk evaluation',
                                'code'       => 'N/A',
                                'time_frame' => 'As Needed',
                            ],
                            [
                                'body'       => 'Bone density test',
                                'code'       => '77085 / 77080 / 77082',
                                'time_frame' => 'Every 2 Years',
                            ],
                        ],
                    ],
                ],
            ],

            // Hearing Impairment

            [
                'title'       => 'Hearing Impairment',
                'task_titles' => [],
                'data'        => [
                    'hearing_impairment' => [
                        'qualitative_trigger' => 'Hearing',
                        'task_body'           => 'You indicated that you have difficulty hearing at times. Hearing loss that occurs gradually as you age (presbycusis) is common. About 25 percent of people in the United States between the ages of 55 and 64 have some degree of hearing loss. For those older than 65, the number of people with some hearing loss is almost 1 in 2. Aging and chronic exposure to loud noises are significant factors that contribute to hearing loss. Other factors, such as excessive earwax, can temporarily prevent your ears from conducting sounds as well as they should. You can\'t reverse most types of hearing loss. However, you don\'t have to live in a world of muted, less distinct sounds. You and your doctor, or a hearing specialist, can take steps to improve what you hear.
                            Your doctor may follow up with:',
                        'recommendation_body' => [
                            'A hearing test in the office',
                            'Potential for hearing aids',
                        ],
                        'report_table_data' => [
                            [
                                'body'       => 'A hearing test in the office',
                                'code'       => 'Various',
                                'time_frame' => 'As Needed',
                            ],
                        ],
                    ],
                ],
            ],

            // Cognitive Impairment:
            [
                'title'       => 'Cognitive Impairment',
                'task_titles' => [],
                'data'        => [
                    'mild_cognitive_impairment' => [
                        'qualitative_trigger' => 'Cognitive Overview',
                        'task_body'           => 'Mild cognitive impairment (MCI) is the stage between normal aging and moderate and severe neurocognitive impairment. It can involve problems with memory, language, thinking and judgment that are greater than normal age-related changes. If you have mild cognitive impairment, you may be aware that your memory or mental function has "slipped." Your family and close friends also may notice a change. But these changes aren\'t severe enough to significantly interfere with your daily life and usual activities. Approximately 15 to 20 percent of people age 65 or older have MCI, and it may increase your risk of progression to more severe stages and other neurological conditions. But some people with mild cognitive impairment never get worse, and a few eventually get better. As you have shown initial signs of MCI, your doctor may recommend:',
                        'recommendation_body' => [
                            'Laboratory and / or brain imaging evaluations',
                            'Cognitive training, lifestyle behavioral interventions, exercise, educational interventions, and / or multidisciplinary care interventions',
                            'Medications',
                        ],
                        'report_table_data' => [
                            [
                                'body'       => 'Cognitive training, lifestyle behavioral interventions, exercise, educational interventions, and / or multidisciplinary care interventions',
                                'code'       => 'Various',
                                'time_frame' => 'As Needed',
                            ],
                        ],
                    ],
                    'moderate_cognitive_impairment' => [
                        'qualitative_trigger' => 'Cognitive Overview',
                        'task_body'           => ' Moderate to severe neurocognitive impairment is the loss of cognitive functioning—thinking, remembering, and reasoning—and behavioral abilities to such an extent that it interferes with a person\'s daily life and activities. These functions include memory, language skills, visual perception, problem solving, self-management, and the ability to focus and pay attention. Some people with dementia cannot control their emotions, and their personalities may change. Dementia ranges in severity from the mildest stage, when it is just beginning to affect a person\'s functioning, to the most severe stage, when the person must depend completely on others for basic activities of living.  As you may have shown the initial signs of moderate to severe neurocognitive impairment, your doctor might recommend:',
                        'recommendation_body' => [
                            'Laboratory and / or brain imaging evaluations',
                            'Cognitive training, lifestyle behavioral interventions, exercise, educational interventions, and / or multidisciplinary care interventions',
                            'Pharmacological treatments',
                        ],
                        'report_table_data' => [
                            [
                                'body'       => 'Laboratory and / or brain imaging',
                                'code'       => 'Various',
                                'time_frame' => 'As Needed',
                            ],
                            [
                                'body'       => 'Cognitive training, lifestyle behavioral interventions, exercise, educational interventions, and / or multidisciplinary care interventions',
                                'code'       => 'Various',
                                'time_frame' => 'As Needed',
                            ],
                        ],
                    ],
                ],
            ],

            // ADL
            [
                'title'       => 'ADL',
                'task_titles' => [],
                'data'        => [
                    'adl_issues' => [
                        'qualitative_trigger' => 'Activities in Daily Life',
                        'task_body'           => 'Many older people experience problems in daily activities. Those difficulties restrict their ability to perform self-care, a common reason why older people seek help from outsiders, move to assisted living communities, or enter nursing homes. As you indicated you have trouble doing {insert all selected tasks in Q26} and have indicated you have nobody to assist you, your doctor may suggest:',
                        'recommendation_body' => [
                            'Occupational therapy',
                            'Assisted living or a home care provider',
                        ],
                        'report_table_data' => [
                            [
                                'body'       => 'Occupational therapy',
                                'code'       => 'Various',
                                'time_frame' => 'As Needed',
                            ],
                            [
                                'body'       => 'Assisted living or a home care provider',
                                'code'       => 'Various',
                                'time_frame' => 'As Needed',
                            ],
                        ],
                    ],
                ],
            ],

            // Immunizations/Vaccines:
            [
                'title'       => 'Immunizations / Vaccines',
                'task_titles' => [],
                'data'        => [
                    // Flu/Influenza
                    'flu' => [
                        'sub_title'           => 'Flu / Influenza',
                        'qualitative_trigger' => 'Flu Shot',
                        'task_body'           => 'You indicated you are not planning on receiving a flu shot this year. Influenza is a potentially serious disease that can lead to hospitalization and sometimes even death. Every flu season is different, and influenza infection can affect people differently, but millions of people get the flu every year, and  an annual seasonal flu vaccine is the best way to help protect against flu.  Flu vaccines cause antibodies to develop in the body about two weeks after vaccination. These antibodies provide protection against infection with the viruses that are in the vaccine. Your doctor should advise you to get a:',
                        'recommendation_body' => ['Flu vaccine yearly'],
                        'report_table_data'   => [
                            [
                                'body'       => 'Flu Vaccine',
                                'code'       => 'G0008',
                                'time_frame' => 'Annual',
                            ],
                        ],
                    ],
                    // Tetanus Diphtheria (initial and/or booster)
                    'tetanus' => [
                        'sub_title'           => 'Tetanus Diphtheria (initial and / or booster)',
                        'qualitative_trigger' => 'TDaP or DTaP Immunization',
                        'task_body'           => 'You have indicated you have not received, or are unsure of having received, either an initial Tetanus Diphtheria (DTaP) vaccine or a booster (TDaP) vaccination in the past 10 years.The Td vaccine is used to protect against Tetanus and diphtheria, both infections caused by bacteria. Tetanus (Lockjaw) causes painful muscle tightening and stiffness, and kills 1 out of every 10 people who are infected. Diphtheria can cause a thick coating to form in the back of the throat, and can lead to breathing problems, heart failure, paralysis, and death. Since vaccination began, reports of cases for both diseases have dropped by about 99%. Your doctor should advise you to receive:',
                        'recommendation_body' => ['One dose of Tdap (if you have never gotten it before), and a Tdap booster every 10 years at your next visit'],
                        'report_table_data'   => [
                            [
                                'body'       => 'Tetanus: One dose of Tdap (if you have never gotten it before)',
                                'code'       => '90715 / 90471',
                                'time_frame' => '1x per lifetime',
                            ],
                            [
                                'body'       => 'Tetanus: Tdap booster every 10 years at your next visit',
                                'code'       => '90715 / 90471',
                                'time_frame' => 'Every 10 Years',
                            ],
                        ],
                    ],
                    // Chicken Pox/Varicella
                    'chicken_pox' => [
                        'sub_title'           => 'Chicken Pox / Varicella',
                        'qualitative_trigger' => 'Varicella vaccination',
                        'task_body'           => 'Chickenpox is a very contagious disease caused by the varicella-zoster virus (VZV). It causes a blister-like rash, itching, tiredness, and fever. Each year, chickenpox caused about 10,600 hospitalizations and 100 to 150 deaths. Two doses of the vaccine are about 90% effective at preventing chickenpox. When you get vaccinated, you protect yourself and others in your community. This is especially important for people who cannot get vaccinated, such as those with weakened immune systems or pregnant women. At your next visit, your doctor should advise a:',
                        'recommendation_body' => ['Varicella Vaccination'],
                        'report_table_data'   => [
                            [
                                'body'       => 'Chicken Pox / Varicella Vaccine',
                                'code'       => '90396 / 90471',
                                'time_frame' => '1x per lifetime',
                            ],
                        ],
                    ],
                    // Hepatitis B
                    'hepatitis_b' => [
                        'sub_title'           => 'Hepatitis B',
                        'qualitative_trigger' => 'Hepatitis B Vaccination',
                        'task_body'           => 'Hepatitis B is a serious disease caused by a virus that attacks the liver. The virus, which is called Hepatitis B virus (HBV), can cause lifelong infection, cirrhosis (scarring) of the liver, liver cancer, liver failure, and death. At your next visit, your doctor should consider a:',
                        'recommendation_body' => ['Hepatitis B (HBV) Vaccination'],
                        'report_table_data'   => [
                            [
                                'body'       => 'Hepatitis B (HBV) Vaccination',
                                'code'       => '90746 / 90471',
                                'time_frame' => '1x per lifetime',
                            ],
                        ],
                    ],
                    // Measles Mumps Rubella (MMR)
                    'mmr' => [
                        'sub_title'           => 'Measles Mumps Rubella (MMR)',
                        'qualitative_trigger' => 'Measles Mumps Rubella (MMR)',
                        'task_body'           => 'Measles is a very contagious disease caused by a virus. It spreads through the air when an infected person coughs or sneezes. Measles starts with fever. Soon after, it causes a cough, runny nose, and red eyes. Then a rash of tiny, red spots breaks out. At your next visit, your doctor should consider a:',
                        'recommendation_body' => ['Measles, Mumps, and Rubella (MMR) Vaccination'],
                        'report_table_data'   => [
                            [
                                'body'       => 'Measles, Mumps, and Rubella (MMR) Vaccination',
                                'code'       => '90707 / 90708 / 90710 / 90471',
                                'time_frame' => '1x per lifetime',
                            ],
                        ],
                    ],
                    // Human Papillomavirus (HPV)
                    'hpv' => [
                        'sub_title'           => 'Human Papillomavirus (HPV)',
                        'qualitative_trigger' => 'Human Papillomavirus (HPV)',
                        'task_body'           => 'Human Papillomavirus is a group of more than 150 related viruses which are transmitted through intimate skin-to-skin contact, most commonly vaginal, anal, or oral sex. Some HPV types can cause genital warts (papillomas). Some other HPV types can lead to cancer, including cancer of mouth / throat, anus / rectum, penis in males, and cervix, vagina, and vulva in females. At your next visit, if you are female or a homosexual male, your doctor should advise a:',
                        'recommendation_body' => ['An appropriate number of doses of Human Papillomavirus (HPV) Vaccination (2 if under age 15, 3 if under age 26)'],
                        'report_table_data'   => [
                            [
                                'body'       => 'An appropriate number of doses of Human Papillomavirus (HPV) Vaccination (2 if under age 15, 3 if under age 26)',
                                'code'       => '90650 / 90471',
                                'time_frame' => '1x per lifetime',
                            ],
                        ],
                    ],
                    // Shingles (Herpes Zoster)
                    'herpes_zoster' => [
                        'sub_title'           => 'Shingles (Herpes Zoster)',
                        'qualitative_trigger' => 'Shingles (Herpes Zoster)',
                        'task_body'           => 'Shingles is a painful rash that usually develops on one side of the body, often the face or torso. The rash consists of blisters that typically scab over in 7 to 10 days and clears up within 2 to 4 weeks. This long-lasting pain is called postherpetic neuralgia (PHN), and it is the most common complication of shingles. Your risk of getting shingles and PHN increases as you get older. Your doctor should consider:',
                        'recommendation_body' => ['2 doses of RZV (preferred) or 1 dose of ZVL, (even if you have had shingles before)'],
                        'report_table_data'   => [
                            [
                                'body'       => 'Shingles: 2 doses of RZV (preferred) or 1 dose of ZVL, (even if you have had shingles before)',
                                'code'       => '90736 / 90471',
                                'time_frame' => '1x per lifetime',
                            ],
                        ],
                    ],
                    // Pneumococcal Vaccine
                    'pneumococcal_vaccine' => [
                        'sub_title'           => 'Pneumococcal Vaccine',
                        'qualitative_trigger' => 'Pneumococcal Vaccine',
                        'task_body'           => 'Pneumococcal disease is any type of infection caused by Streptococcus pneumoniae bacteria. The CDC recommends pneumococcal conjugate vaccine for all children younger than 2 years old, all adults 65 years or older, and people 2 through 64 years old with certain medical conditions. The CDC also recommends pneumococcal polysaccharide vaccine for all adults 65 years or older, people 2 through 64 years old with certain medical conditions, and adults 19 through 64 years old who smoke cigarettes. Your doctor should advise you to get:',
                        'recommendation_body' => ['1 dose of PCV13 and at least 1 dose of PPSV23'],
                        'report_table_data'   => [
                            [
                                'body'       => 'Pneumonia vaccine: 1 dose of PCV13 and at least 1 dose of PPSV23',
                                'code'       => 'G0009',
                                'time_frame' => '1x per lifetime',
                            ],
                        ],
                    ],
                ],
            ],

            // Screenings:
            [
                'title'       => 'Screenings',
                'task_titles' => [],
                'data'        => [
                    // Breast Cancer- Mammogram
                    'mammogram' => [
                        'sub_title'           => 'Breast Cancer- Mammogram',
                        'qualitative_trigger' => 'Mammogram',
                        'task_body'           => 'Breast cancer screening is checking a woman’s breasts for cancer before there are signs or symptoms of the disease. At this time, mammograms (X-rays of the breast) are the best way to find breast cancer early, when it is easier to treat and before it is big enough to feel or cause symptoms.  Due to your age and other potential risk factors, your doctor may suggest:',
                        'recommendation_body' => ['Getting a mammogram every 2 years'],
                        'report_table_data'   => [
                            [
                                'body'       => 'Mammogram',
                                'code'       => '77067',
                                'time_frame' => 'Every 2 Years',
                            ],
                        ],
                    ],
                    // Cervical Cancer - Pap Smear
                    'cervical_cancer_young' => [
                        'sub_title'           => 'Cervical Cancer - Pap Smear',
                        'qualitative_trigger' => 'Pap Smear',
                        'task_body'           => 'Screening tests offer the best chance to have cervical cancer found early when successful treatment is likely. Screening can also actually prevent most cervical cancers by finding abnormal cervical cell changes (pre-cancers) so that they can be treated before they have a chance to turn into a cervical cancer. If it’s found early, cervical cancer is one of the most successfully treatable cancers. Since you are between 21-29 years old, your doctor may recommend:',
                        'recommendation_body' => ['Screening for cervical cancer every 3 years with cervical cytology alone'],
                        'report_table_data'   => [
                            [
                                'body'       => 'Screening for cervical cancer with cervical cytology alone',
                                'code'       => 'Various',
                                'time_frame' => 'Every 3 Years',
                            ],
                        ],
                    ],

                    'cervical_cancer_elder' => [
                        'sub_title'           => 'Cervical Cancer - Pap Smear case2',
                        'qualitative_trigger' => 'Pap Smear',
                        'task_body'           => 'Screening tests offer the best chance to have cervical cancer found early when successful treatment is likely. Screening can also actually prevent most cervical cancers by finding abnormal cervical cell changes (pre-cancers) so that they can be treated before they have a chance to turn into a cervical cancer. If it’s found early, cervical cancer is one of the most successfully treatable cancers. Since you are between 30-65 years old, your doctor may recommend:',
                        'recommendation_body' => ['Screening every 3 years with cervical cytology alone, every 5 years with high-risk human papillomavirus (hrHPV) testing alone, or every 5 years with hrHPV testing in combination with cytology (cotesting)'],
                        'report_table_data'   => [
                            [
                                'body'       => 'Screening every 3 years with cervical cytology alone, every 5 years with high-risk human papillomavirus (hrHPV) testing alone, or every 5 years with hrHPV testing in combination with cytology (cotesting)',
                                'code'       => 'Various',
                                'time_frame' => 'Every 3 or 5 Years',
                            ],
                        ],
                    ],
                    // Prostate Cancer- Prostate Screening
                    'prostate_cancer' => [
                        'sub_title'           => 'Prostate Cancer - Prostate Screening',
                        'qualitative_trigger' => 'Prostate Screening',
                        'task_body'           => 'Prostate cancer is the most common cancer among men (after skin cancer), but it can often be treated successfully. For every 1,000 men between the ages of 55 and 69 years old who are screened, about 1 death will be prevented, and 3 men will be prevented from getting prostate cancer that spreads to other places in the body. At your next visit, your doctor may:',
                        'recommendation_body' => ['Discuss the benefits and harms of the prostate specific antigen (PSA) test'],
                        'report_table_data'   => [
                            [
                                'body'       => 'Discuss pros and cons of Prostrate Screening (PSA)',
                                'code'       => 'N/A',
                                'time_frame' => 'Every 10 Years',
                            ],
                        ],
                    ],
                    // Colorectal Cancer Screening:
                    'colorectal_cancer' => [
                        'sub_title'           => 'Colorectal Cancer Screening',
                        'qualitative_trigger' => 'Colorectal Cancer Screening',
                        'task_body'           => 'Colorectal cancer almost always develops from precancerous polyps (abnormal growths) in the colon or rectum. Screening tests can find precancerous polyps, so that they can be removed before they turn into cancer. Screening tests can also find colorectal cancer early, when treatment works best. Your doctor may suggest any one of the following screening tests:',
                        'recommendation_body' => [
                            'Fecal Occult Blood Test (FOBT) every year',
                            'Fecal Immunohistochemistry Test (FIT) every year',
                            'Sigmoidoscopy every 5 years',
                            'Colonoscopy every 10 years',
                        ],
                        'report_table_data' => [
                            [
                                'body'       => 'Dr. may suggest 1 of following: Fecal Occult Blood Test (FOBT) 1x per year, Fecal Immunohistochemistry Test (FIT) 1x per year, Sigmoidoscopy every 5 years, OR Colonoscopy every 10 years',
                                'code'       => 'Various',
                                'time_frame' => 'Annual to every 10 years',
                            ],
                        ],
                    ],
                    // Skin Cancer Screening:
                    'skin_cancer' => [
                        'sub_title'           => 'Skin Cancer Screening',
                        'qualitative_trigger' => 'Skin Cancer Screening',
                        'task_body'           => 'Regular skin checks by a doctor are important for people who have already had skin cancer or have a family history of skin cancer. If you are checking your skin and find a worrisome change, you should report it to your doctor. In addition, your doctor may suggest:',
                        'recommendation_body' => ['A skin cancer screening in office'],
                    ],

                    // Osteoporosis
                    'osteoporosis' => [
                        'sub_title'           => 'Osteoporosis',
                        'qualitative_trigger' => 'Bone Density Test',
                        'task_body'           => 'Bone is living tissue that is constantly being broken down and replaced. Osteoporosis occurs when the creation of new bone doesn\'t keep up with the removal of old bone. Your bones are prone to becoming weak and brittle as you grow older— so brittle that a fall or even mild stresses such as bending over or coughing has the potential to cause a dangerous fracture. Your provider may recommend screening for Osteoporosis by getting a:',
                        'recommendation_body' => ['Bone Density Test'],
                        'report_table_data'   => [
                            [
                                'body'       => 'Bone density test',
                                'code'       => '77085 / 77080 / 77082',
                                'time_frame' => 'Every 2 Years',
                            ],
                        ],
                    ],
                    // Glaucoma
                    'glaucoma' => [
                        'sub_title'           => 'Glaucoma',
                        'qualitative_trigger' => 'Glaucoma',
                        'task_body'           => 'Glaucoma is a group of diseases that damage the eye’s optic nerve and can result in vision loss and even blindness. About 3 million Americans have glaucoma. There are often no early symptoms, which is why 50% of people with glaucoma don’t know they have the disease. There is no cure (yet) for glaucoma, but if it’s caught early, you can preserve your vision and prevent vision loss. Your doctor may suggest:',
                        'recommendation_body' => ['Testing for glaucoma once every 1-2 years'],
                        'report_table_data'   => [
                            [
                                'body'       => 'Glaucoma testing',
                                'code'       => 'G0117 / G0118',
                                'time_frame' => 'Every 1-2 Years',
                            ],
                        ],
                    ],
                    // Diabetes
                    'diabetes' => [
                        'sub_title'           => 'Diabetes',
                        'qualitative_trigger' => 'Blood Sugar',
                        'task_body'           => 'Diabetes mellitus refers to a group of diseases that affect how your body uses blood sugar (glucose). Glucose is vital to your health because it\'s an important source of energy for the cells that make up your muscles and tissues. It\'s also your brain\'s main source of fuel. No matter what type of diabetes you have, it can lead to excess sugar in your blood. Too much sugar in your blood can lead to serious health problems. You may be at risk for diabetes due to your age and BMI. Your doctor may:',
                        'recommendation_body' => [
                            'Test your blood glucose levels',
                            'Suggest behavioral counseling interventions to promote a healthful diet and physical activity',
                        ],
                        'report_table_data' => [
                            [
                                'body'       => 'Blood Glucose Test (A1C) / Diabetes Screening',
                                'code'       => '82947',
                                'time_frame' => 'As Needed',
                            ],
                            [
                                'body'       => 'Behavioral counseling for healthy diet and physical activity',
                                'code'       => 'G0270 / G0271 / G0447 / G0479 /  Others',
                                'time_frame' => 'As Needed',
                            ],
                        ],
                    ],
                    // Cholesterol/Dyslipidemia
                    'cholesterol' => [
                        'sub_title'           => 'Cholesterol / Dyslipidemia',
                        'qualitative_trigger' => 'Cholesterol / Dyslipidemia',
                        'task_body'           => 'Cholesterol is a dense, fatty substance found in every cell of your body. High cholesterol is a condition that occurs when levels of cholesterol in your blood are elevated enough to cause health problems such as heart disease. You may be at risk of High Cholesterol. Your provider may encourage you to:',
                        'recommendation_body' => ['Test your blood for dyslipidemia (high cholesterol)'],
                        'report_table_data'   => [
                            [
                                'body'       => 'Dyslipidemia (high cholesterol) testing',
                                'code'       => '82465 / Others',
                                'time_frame' => 'As Needed',
                            ],
                        ],
                    ],
                ],
            ],
            // Other misc:
            [
                'title'       => 'Other misc',
                'task_titles' => [],
                'data'        => [
                    // Advanced Care Planning
                    'nmpa' => [// No Medical Power of Attorney = NMPA
                        'sub_title'           => 'Advanced Care Planning / NMPA',
                        'qualitative_trigger' => 'Advanced Care Planning',
                        'task_body'           => 'A Medical Power of Attorney is a legal instrument (separate from a durable power of attorney) that allows you to select the person that you want to make healthcare decisions for you if and when you become unable to make them for yourself. The person you pick is your representative in that situation for purposes of healthcare decision-making. You could limit your representative to certain types of decisions, or allow your representative to make any healthcare decision that might come up. Talk to your doctor about taking steps to:',
                        'recommendation_body' => ['Set up a Medical Power of Attorney'],
                        'report_table_data'   => [
                            [
                                'body'       => 'Set up a Medical Power of Attorney (NOTE: $0 co-pay if done during AWV)',
                                'code'       => '99497 / 99498 (if same day as AWV, bill w/ mod. 33 on same claim and Dr. as AWV)',
                                'time_frame' => 'As Needed',
                            ],
                        ],
                    ],
                    'nlwad' => [
                        'sub_title'           => 'Living Will',
                        'qualitative_trigger' => 'Living Will',
                        'task_body'           => 'Living wills and other advance directives are written, legal instructions regarding your preferences for medical care if you are unable to make decisions for yourself. Advance directives guide choices for doctors and caregivers if you\'re terminally ill, seriously injured, in a coma, in the late stages of dementia or near the end of life. By planning ahead, you can get the medical care you want, avoid unnecessary suffering and relieve caregivers of decision-making burdens during moments of crisis or grief. You also help reduce confusion or disagreement about the choices you would want people to make on your behalf. Advance directives aren\'t just for older adults. Unexpected end-of-life situations can happen at any age, so it\'s important for all adults to prepare these documents. Talk to your doctor about taking steps to:',
                        'recommendation_body' => ['Set up a living will / advance directive'],
                        'report_table_data'   => [
                            [
                                'body'       => 'Set up a living will / advance directive (NOTE: $0 co-pay if done during AWV)',
                                'code'       => '99497 / 99498 (if same day as AWV, bill w/ mod. 33 on same claim and Dr. as AWV)',
                                'time_frame' => 'As Needed',
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }
}
