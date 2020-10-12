<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

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
 * @method   static      \Illuminate\Database\Eloquent\Builder|UnresolvedCallbacksResourceModel newModelQuery()
 * @method   static      \Illuminate\Database\Eloquent\Builder|UnresolvedCallbacksResourceModel newQuery()
 * @method   static      \Illuminate\Database\Eloquent\Builder|UnresolvedCallbacksResourceModel query()
 * @mixin \Eloquent
 * @property string|null                     $inbound_data
 * @property \Illuminate\Support\Carbon|null $date
 * @property int                             $manually_resolved
 */
class UnresolvedCallbacksResourceModel extends SqlViewModel
{
    protected $dates = [
        'date',
    ];
    protected $primaryKey = 'postmark_id';
    protected $table      = 'unresolved_postmark_callback_view';
}
