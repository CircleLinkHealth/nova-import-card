<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\CcdaImporter;

use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\CcdaImporter\Settings\Hooks;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use Illuminate\Support\Collection;

class FiresImportingHooks
{
    public static function fireImportingHook(string $hookName, User $patient, Ccda $ccda, $payload)
    {
        $patient->loadMissing('primaryPractice');

        if ( ! $patient->primaryPractice) {
            return null;
        }

        return self::runHook($hookName, $patient->primaryPractice, $patient, $ccda, $payload);
    }

    public static function hasListener(string $hookName, string $listener, Practice $practice): bool
    {
        if ( ! $practice->importing_hooks instanceof Collection) {
            return false;
        }

        if ( ! $practice->importing_hooks->keys()->contains($hookName)) {
            return false;
        }

        $args = $practice->importing_hooks->get($hookName) ?? [];

        if ( ! array_key_exists('listener', $args)) {
            return false;
        }

        if ($args['listener'] !== $listener) {
            return false;
        }

        return true;
    }

    public static function shouldRunHook(string $hookName, Practice $practice): bool
    {
        if ( ! $practice->importing_hooks instanceof Collection) {
            return false;
        }

        if ( ! $practice->importing_hooks->keys()->contains($hookName)) {
            return false;
        }

        $args = $practice->importing_hooks->get($hookName) ?? [];

        if ( ! array_key_exists('listener', $args)) {
            return false;
        }

        if ( ! array_key_exists($args['listener'], Hooks::LISTENERS)) {
            return false;
        }

        return true;
    }

    private static function runHook(string $hookName, Practice $practice, User $user, Ccda $ccda, $payload)
    {
        if ( ! self::shouldRunHook($hookName, $practice)) {
            return null;
        }

        $args = $practice->importing_hooks->get($hookName);

        return app(Hooks::LISTENERS[$args['listener']], ['patient' => $user, 'ccda' => $ccda, 'payload' => $payload])->run();
    }
}
