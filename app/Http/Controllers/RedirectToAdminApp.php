<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

class RedirectToAdminApp extends Controller
{
    public function getCADirectorIndex()
    {
        return $this->redirectToAdmin('ca-director');
    }

    public function getCreateNotifications(string $practiceSlug)
    {
        return redirect()->to($this->redirectToAdmin("/practices/$practiceSlug/notifications"));
    }

    public function getCreatePractice(string $practiceSlug)
    {
        return redirect()->to($this->redirectToAdmin("/practices/$practiceSlug/practice"));
    }

    public function getPAM()
    {
        return $this->redirectToAdmin('pam');
    }

    private function redirectToAdmin($url)
    {
        return redirect()->to(rtrim(config('core.apps.cpm-admin.url'), '/')."/$url");
    }
}
