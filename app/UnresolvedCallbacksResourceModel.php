<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use CircleLinkHealth\Core\Entities\SqlViewModel;

class UnresolvedCallbacksResourceModel extends SqlViewModel
{
    protected $primaryKey = 'postmark_id';
    protected $table      = 'unresolved_postmark_callback_view';
}
