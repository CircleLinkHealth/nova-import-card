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
 * @property string|null $inbound_data
 */
class UnresolvedCallbacksResourceModel extends SqlViewModel
{
    protected $primaryKey = 'postmark_id';
    protected $table      = 'unresolved_postmark_callback_view';
}
