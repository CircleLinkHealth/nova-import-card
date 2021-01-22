<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\RedirectToVaporRequest.
 *
 * @method static \Illuminate\Database\Eloquent\Builder|RedirectToVaporRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RedirectToVaporRequest newQuery()
 * @method static \Illuminate\Database\Query\Builder|RedirectToVaporRequest onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|RedirectToVaporRequest query()
 * @method static \Illuminate\Database\Query\Builder|RedirectToVaporRequest withTrashed()
 * @method static \Illuminate\Database\Query\Builder|RedirectToVaporRequest withoutTrashed()
 * @mixin \Eloquent
 * @property int                             $id
 * @property string                          $token
 * @property int                             $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class RedirectToVaporRequest extends Model
{
    use SoftDeletes;

    protected $fillable = ['user_id', 'token'];
}
