<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * CircleLinkHealth\Customer\Entities\PracticePatientsView.
 *
 * @property int         $id
 * @property string      $first_name
 * @property string      $last_name
 * @property string|null $suffix
 * @property string      $display_name
 * @property string      $city
 * @property string      $state
 * @property string      $program_id
 * @property string|null $ccm_status
 * @property string|null $status
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PracticePatientsView newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PracticePatientsView newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PracticePatientsView query()
 * @mixin \Eloquent
 *
 * @property string|null $preferred_contact_language
 */
class PracticePatientsView extends Model
{
    protected $fillable = [];
    protected $table    = 'practice_patients_view';
}
