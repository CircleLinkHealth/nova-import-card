<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Entities;

use CircleLinkHealth\Core\Entities\SqlViewModel;

/**
 * App\UnresolvedCallbacksResourceModel.
 *
 * @property int         $postmark_id
 * @property int|null    $matched_user_id
 * @property string|null $unresolved_reason
 * @property mixed|null  $other_possible_matches
 * @property int|null    $call_id
 * @property int         $resolved
 * @method static      \Illuminate\Database\Eloquent\Builder|UnresolvedCallbacksView newModelQuery()
 * @method static      \Illuminate\Database\Eloquent\Builder|UnresolvedCallbacksView newQuery()
 * @method static      \Illuminate\Database\Eloquent\Builder|UnresolvedCallbacksView query()
 * @mixin \Eloquent
 * @property string|null                     $inbound_data
 * @property \Illuminate\Support\Carbon|null $date
 * @property int                             $manually_resolved
 * @property int                             $assigned_to_ca
 * @property string|null                     $matched_user_name
 */
class UnresolvedCallbacksView extends SqlViewModel
{
    protected $dates = [
        'date',
    ];
    protected $primaryKey = 'postmark_id';
    protected $table      = 'unresolved_postmark_callback_view';
}
