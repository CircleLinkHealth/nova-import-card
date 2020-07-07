<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Observations;

class ObservationKeysMap
{
    public const BIOMETRICS = [
        'CF_RPT_20' => 'Blood Pressure',
        'CF_RPT_30' => 'Blood Sugar',
        'CF_RPT_60' => 'A1c',
        'CF_RPT_40' => 'Weight',
        'CF_RPT_50' => 'Cigarette Count',
    ];

    public const LIFESTYLE = [
        'CF_SOL_LFS_10' => 'Exercise 20 minutes',
        'CF_LFS_40'     => 'Following Healthy Diet',
        'CF_LFS_80'     => 'Low salt diet',
        'CF_SOL_LFS_90' => 'Diabetes diet',
    ];

    public const MEDICATIONS = [
        'CF_SOL_MED_BP'  => 'Blood Pressure meds',
        'CF_SOL_MED_CHL' => 'Cholesterol meds',
        'CF_SOL_MED_BT'  => 'Blood Thinners (e.g., Plavix, Aspirin)',
        'CF_SOL_MED_WPD' => 'Water pills/diuretics',
        'CF_SOL_MED_OHM' => 'Other meds',
        'CF_SOL_MED_OD'  => 'Oral diabetes meds',
        'CF_SOL_MED_IID' => 'Insulin or injectable diabetes meds',
        'CF_SOL_MED_BRE' => 'Breathing meds',
        'CF_SOL_MED_DEP' => 'Mood/Depression meds',
    ];

    public const SYMPTOMS = [
        'CF_SYM_51' => 'Shortness of breath',
        'CF_SYM_52' => 'Coughing or wheezing',
        'CF_SYM_53' => 'Chest pain or chest tightness',
        'CF_SYM_54' => 'Fatigue',
        'CF_SYM_55' => 'Weakness or dizziness',
        'CF_SYM_56' => 'Swelling in legs/feet',
        'CF_SYM_57' => 'Feeling down,  helpless, or sleep changes',
    ];
}
