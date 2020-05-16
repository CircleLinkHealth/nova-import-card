<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Models\PracticePull;

use CircleLinkHealth\Core\Entities\BaseModel;

/**
 * App\Models\PracticePull\Allergy.
 *
 * @property string                                                                                      $mrn
 * @property string|null                                                                                 $name
 * @property int                                                                                         $id
 * @property int|null                                                                                    $location_id
 * @property int|null                                                                                    $billing_provider_user_id
 * @property int                                                                                         $practice_id
 * @property \Illuminate\Support\Carbon|null                                                             $created_at
 * @property \Illuminate\Support\Carbon|null                                                             $updated_at
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @property int|null                                                                                    $revision_history_count
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\Models\PracticePull\Allergy newModelQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\Models\PracticePull\Allergy newQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\Models\PracticePull\Allergy query()
 * @mixin \Eloquent
 */
class Allergy extends BaseModel
{
    protected $fillable = [
        'billing_provider_user_id',
        'location_id',
        'practice_id',
        'mrn',
        'name',
    ];
    protected $table = 'practice_pull_allergies';
}
