<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use CircleLinkHealth\Core\Entities\SqlViewModel;

/**
 * App\EnrollmentView.
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrollmentView newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrollmentView newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrollmentView query()
 * @mixin \Eloquent
 */
class EnrollmentView extends SqlViewModel
{
    protected $table = 'auto_enrollment_view';
}
