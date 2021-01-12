<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Observations;

class ObservationConstants
{
    public const A1C                        = 'A1c';
    public const ACCEPTED_OBSERVATION_TYPES = [
        'CF_SOL_MED_BP' => [
            'name'          => 'Blood Pressure meds',
            'display_name'  => 'Blood Pressure meds',
            'category_name' => self::MEDICATIONS_ADHERENCE_OBSERVATION_TYPE,
        ],
        'CF_SOL_MED_CHL' => [
            'name'          => 'Cholesterol meds',
            'display_name'  => 'Cholesterol meds',
            'category_name' => self::MEDICATIONS_ADHERENCE_OBSERVATION_TYPE,
        ],
        'CF_SOL_MED_BT' => [
            'name'          => 'Blood Thinners (e.g., Plavix, Aspirin)',
            'display_name'  => 'Blood Thinners (e.g., Plavix, Aspirin)',
            'category_name' => self::MEDICATIONS_ADHERENCE_OBSERVATION_TYPE,
        ],
        'CF_SOL_MED_WPD' => [
            'name'          => 'Water pills/diuretics',
            'display_name'  => 'Water pills/diuretics',
            'category_name' => self::MEDICATIONS_ADHERENCE_OBSERVATION_TYPE,
        ],
        'CF_SOL_MED_OHM' => [
            'name'          => 'Other meds',
            'display_name'  => 'Other meds',
            'category_name' => self::MEDICATIONS_ADHERENCE_OBSERVATION_TYPE,
        ],
        'CF_SOL_MED_OD' => [
            'name'          => 'Oral diabetes meds',
            'display_name'  => 'Oral diabetes meds',
            'category_name' => self::MEDICATIONS_ADHERENCE_OBSERVATION_TYPE,
        ],
        'CF_SOL_MED_IID' => [
            'name'          => 'Insulin or injectable diabetes meds',
            'display_name'  => 'Insulin or injectable diabetes meds',
            'category_name' => self::MEDICATIONS_ADHERENCE_OBSERVATION_TYPE,
        ],
        'CF_SOL_MED_BRE' => [
            'name'          => 'Breathing meds',
            'display_name'  => 'Breathing meds',
            'category_name' => self::MEDICATIONS_ADHERENCE_OBSERVATION_TYPE,
        ],
        'CF_SOL_MED_DEP' => [
            'name'          => 'Mood/Depression meds',
            'display_name'  => 'Mood/Depression meds',
            'category_name' => self::MEDICATIONS_ADHERENCE_OBSERVATION_TYPE,
        ],
        'CF_SYM_51' => [
            'name'          => 'Shortness of breath',
            'display_name'  => 'Shortness of breath',
            'category_name' => self::SYMPTOMS_OBSERVATION_TYPE,
        ],
        'CF_SYM_52' => [
            'name'          => 'Coughing or wheezing',
            'display_name'  => 'Coughing or wheezing',
            'category_name' => self::SYMPTOMS_OBSERVATION_TYPE,
        ],
        'CF_SYM_53' => [
            'name'          => 'Chest pain or chest tightness',
            'display_name'  => 'Chest pain or chest tightness',
            'category_name' => self::SYMPTOMS_OBSERVATION_TYPE,
        ],
        'CF_SYM_54' => [
            'name'          => 'Fatigue',
            'display_name'  => 'Fatigue',
            'category_name' => self::SYMPTOMS_OBSERVATION_TYPE,
        ],
        'CF_SYM_55' => [
            'name'          => 'Weakness or dizziness',
            'display_name'  => 'Weakness or dizziness',
            'category_name' => self::SYMPTOMS_OBSERVATION_TYPE,
        ],
        'CF_SYM_56' => [
            'name'          => 'Swelling in legs/feet',
            'display_name'  => 'Swelling in legs/feet',
            'category_name' => self::SYMPTOMS_OBSERVATION_TYPE,
        ],
        'CF_SYM_57' => [
            'name'          => 'Feeling down,  helpless, or sleep changes',
            'display_name'  => 'Feeling down,  helpless, or sleep changes',
            'category_name' => self::SYMPTOMS_OBSERVATION_TYPE,
        ],
        'CF_RPT_20' => [
            'name'          => self::BLOOD_PRESSURE,
            'display_name'  => self::BLOOD_PRESSURE.' (mmHg)',
            'category_name' => self::BIOMETRICS_ADHERENCE_OBSERVATION_TYPE,
        ],
        'CF_RPT_30' => [
            'name'          => self::BLOOD_SUGAR,
            'display_name'  => self::BLOOD_SUGAR.' (mg/dL)',
            'category_name' => self::BIOMETRICS_ADHERENCE_OBSERVATION_TYPE,
        ],
        'CF_RPT_60' => [
            'name'          => self::A1C,
            'display_name'  => self::A1C.' (%)',
            'category_name' => self::BIOMETRICS_ADHERENCE_OBSERVATION_TYPE,
        ],
        'CF_RPT_40' => [
            'name'          => self::WEIGHT,
            'display_name'  => self::WEIGHT.' (lb)',
            'category_name' => self::BIOMETRICS_ADHERENCE_OBSERVATION_TYPE,
        ],
        'CF_RPT_50' => [
            'name'          => self::CIGARETTE_COUNT,
            'display_name'  => 'Smoking (# per day)',
            'category_name' => self::BIOMETRICS_ADHERENCE_OBSERVATION_TYPE,
        ],
        'CF_SOL_LFS_10' => [
            'name'          => 'Exercise 20 minutes',
            'display_name'  => 'Exercise 20 minutes',
            'category_name' => self::LIFESTYLE_OBSERVATION_TYPE,
        ],
        'CF_LFS_40' => [
            'name'          => 'Following Healthy Diet',
            'display_name'  => 'Following Healthy Diet',
            'category_name' => self::LIFESTYLE_OBSERVATION_TYPE,
        ],
        'CF_LFS_80' => [
            'name'          => 'Low salt diet',
            'display_name'  => 'Low salt diet',
            'category_name' => self::LIFESTYLE_OBSERVATION_TYPE,
        ],
        'CF_SOL_LFS_90' => [
            'name'          => 'Diabetes diet',
            'display_name'  => 'Diabetes diet',
            'category_name' => self::LIFESTYLE_OBSERVATION_TYPE,
        ],
    ];
    public const BIOMETRICS = [
    ];
    const BIOMETRICS_ADHERENCE_OBSERVATION_TYPE = 'Biometrics';
    public const BLOOD_PRESSURE                 = 'Blood Pressure';
    public const BLOOD_SUGAR                    = 'Blood Sugar';
    public const CATEGORIES                     = [
        self::MEDICATIONS_ADHERENCE_OBSERVATION_TYPE => [
            'name'         => self::MEDICATIONS_ADHERENCE_OBSERVATION_TYPE,
            'display_name' => 'Medications Taken? Y or N',
        ],
        self::BIOMETRICS_ADHERENCE_OBSERVATION_TYPE => [
            'name'         => self::BIOMETRICS_ADHERENCE_OBSERVATION_TYPE,
            'display_name' => self::BIOMETRICS_ADHERENCE_OBSERVATION_TYPE,
        ],
        self::SYMPTOMS_OBSERVATION_TYPE => [
            'name'         => self::SYMPTOMS_OBSERVATION_TYPE,
            'display_name' => 'Symptoms? (1 - 9)',
        ],
        self::LIFESTYLE_OBSERVATION_TYPE => [
            'name'         => self::LIFESTYLE_OBSERVATION_TYPE,
            'display_name' => 'Lifestyle? Y or N',
        ],
    ];
    public const CIGARETTE_COUNT                        = 'Cigarette Count';
    public const LIFESTYLE_OBSERVATION_TYPE             = 'Lifestyle';
    public const MEDICATIONS_ADHERENCE_OBSERVATION_TYPE = 'Adherence';
    public const SYMPTOMS_OBSERVATION_TYPE              = 'Severity';
    public const WEIGHT                                 = 'Weight';
}
