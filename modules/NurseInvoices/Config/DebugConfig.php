<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Config;

use CircleLinkHealth\Core\Entities\AppConfig;

class DebugConfig
{
    const ENABLE_DEBUG_KEY = 'nurse_invoices_enable_debug';

    public static function isEnabled(): bool
    {
        $val = AppConfig::pull(self::ENABLE_DEBUG_KEY, null);
        if (null === $val) {
            return AppConfig::set(self::ENABLE_DEBUG_KEY, true);
        }

        return filter_var($val, FILTER_VALIDATE_BOOLEAN);
    }
}
