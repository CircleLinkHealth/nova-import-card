<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

class RedirectToAdminApp extends Controller
{
    public function getCreateNotifications(string $practiceSlug)
    {
        return redirect()->to(config('core.apps.cpm-admin.url')."/practices/$practiceSlug/notifications");
    }

    public function getCreatePractice(string $practiceSlug)
    {
        return redirect()->to(config('core.apps.cpm-admin.url')."/practices/$practiceSlug/practice");
    }
}
