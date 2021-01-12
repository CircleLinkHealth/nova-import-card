<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\CcdaImporter\Traits;

trait HasImportingHooks
{
    public function storeImportingHook(string $hook, string $listener, array $args = [])
    {
        $this->importing_hooks = ($this->importing_hooks ?? collect())->put($hook, [
            'listener' => $listener,
        ]);

        $this->save();
    }
}
