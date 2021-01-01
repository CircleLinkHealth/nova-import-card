<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Jobs;

use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use MichaelLedin\LaravelJob\Job;

abstract class EncryptedLaravelJob extends Job implements ShouldBeEncrypted
{
}
