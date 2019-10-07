<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;

class MoveScreeningsInPppTaskRecommendations extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        $table        = 'ppp_task_recommendations';
        $dataToUpdate = [
            'Screenings:' => json_encode([
                // Breast Cancer- Mammogram
                [
                    'sub_title'           => 'Breast Cancer- Mammogram',
                    'qualitative_trigger' => 'Female and 50-74 years old, OR have family history of breast cancer AND has not had a mammogram in the past 2 years',
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
                [
                    'sub_title'           => 'Cervical Cancer - Pap Smear',
                    'qualitative_trigger' => 'Female and 21-29 years old',
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

                [
                    'sub_title'           => 'Cervical Cancer - Pap Smear case2',
                    'qualitative_trigger' => 'Female and 30-65 years old',
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
                [
                    'sub_title'           => 'Prostate Cancer- Prostate Screening',
                    'qualitative_trigger' => 'Male and 55-69 years old and has not been screened in 10 years',
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
                [
                    'sub_title'           => 'Colorectal Cancer Screening:',
                    'qualitative_trigger' => '50-75 years old OR family history of Colorectal cancer and has not screened for Colorectal cancer in the past 5 years',
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
                [
                    'sub_title'           => 'Skin Cancer Screening:',
                    'qualitative_trigger' => 'Pt. has had skin cancer in the past (*check this trigger) and has not had a screening in the past OR has a family history of skin cancer in 2+ relatives',
                    'task_body'           => 'Regular skin checks by a doctor are important for people who have already had skin cancer. If you are checking your skin and find a worrisome change, you should report it to your doctor. In addition, your doctor may suggest:',
                    'recommendation_body' => ['A skin cancer screening in office'],
                ],

                // Osteoporosis
                [
                    'sub_title'           => 'Osteoporosis',
                    'qualitative_trigger' => 'Female older than 65 or Male older than 70 or fall risk is expressed (and bone mass test not done for two years consecutively in the past)',
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
                [
                    'sub_title'           => 'Glaucoma',
                    'qualitative_trigger' => 'If patient has not had an eye test/Glaucoma screening in the past 2 years',
                    'task_body'           => 'Glaucoma is a group of diseases that damage the eye’s optic nerve and can result in vision loss and even blindness. About 3 million Americans have glaucoma. There are often no early symptoms, which is why 50% of people with glaucoma don’t know they have the disease. There is no cure (yet) for glaucoma, but if it’s caught early, you can preserve your vision and prevent vision loss. Your doctor may suggest:',
                    'recommendation_body' => ['Testing for glaucoma once every 1-2 years'],
                    'report_table_data'   => [
                        [
                            'body'       => 'Home fall risk evaluation',
                            'code'       => 'G0117 / G0118',
                            'time_frame' => 'Every 1-2 Years',
                        ],
                    ],
                ],
                // Diabetes
                [
                    'sub_title'           => 'Diabetes',
                    'qualitative_trigger' => 'Adults 20+ years old who are overweight or obese',
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
                [
                    'sub_title'           => 'Cholesterol/Dyslipidemia',
                    'qualitative_trigger' => 'If high blood pressure, Obesity or any other risk factor (eg Poor diet (High in saturated fats), lack of physical activity, smoking, diabetes etc..)',
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
            ]),
            'Other misc:' => json_encode([
                // Advanced Care Planning
                [// No Medical Power of Attorney = NMPA
                    'sub_title'           => 'Advanced Care Planning/NMPA',
                    'qualitative_trigger' => 'No Medical Power of Attorney',
                    'task_body'           => 'A Medical Power of Attorney is a legal instrument (separate from a durable power of attorney) that allows you to select the person that you want to make healthcare decisions for you if and when you become unable to make them for yourself. The person you pick is your representative in that situation for purposes of healthcare decision-making. You could limit your representative to certain types of decisions, or allow your representative to make any healthcare decision that might come up. Talk to your doctor about taking steps to:',
                    'recommendation_body' => ['Set up a Medical Power of Attorney'],
                    'report_table_data'   => [
                        [
                            'body'       => 'Set up a Medical Power of Attorney (NOTE: $0 co-pay if done during AWV)',
                            'code'       => '99497/99498 (if same day as AWV, bill w/ mod. 33 on same claim and Dr. as AWV)',
                            'time_frame' => 'As Needed',
                        ],
                    ],
                ],
                [
                    'sub_title'           => 'Advanced Care Planning/NLWAD',
                    'qualitative_trigger' => ' No living will/advance directive',
                    'task_body'           => 'Living wills and other advance directives are written, legal instructions regarding your preferences for medical care if you are unable to make decisions for yourself. Advance directives guide choices for doctors and caregivers if you\'re terminally ill, seriously injured, in a coma, in the late stages of dementia or near the end of life. By planning ahead, you can get the medical care you want, avoid unnecessary suffering and relieve caregivers of decision-making burdens during moments of crisis or grief. You also help reduce confusion or disagreement about the choices you would want people to make on your behalf. Advance directives aren\'t just for older adults. Unexpected end-of-life situations can happen at any age, so it\'s important for all adults to prepare these documents. Talk to your doctor about taking steps to:',
                    'recommendation_body' => ['Set up a living will/advance directive'],
                    'report_table_data'   => [
                        [
                            'body'       => 'Set up a living will/advance directive (NOTE: $0 co-pay if done during AWV)',
                            'code'       => '99497/99498 (if same day as AWV, bill w/ mod. 33 on same claim and Dr. as AWV)',
                            'time_frame' => 'As Needed',
                        ],
                    ],
                ],
            ]),
        ];

        foreach ($dataToUpdate as $title => $data) {
            DB::table($table)->where('title', '=', $title)->update(['data' => $data]);
        }
    }
}
