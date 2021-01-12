<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

class RedirectToAdminApp extends Controller
{
    public function destroyUser(string $practiceSlug)
    {
        return $this->redirectToAdmin("admin/users/$practiceSlug/destroy");
    }

    public function dmShow(int $dmId)
    {
        return $this->redirectToAdmin("admin/direct-mail/$dmId");
    }

    public function getAdminNurseSchedules()
    {
        return $this->redirectToAdmin('admin/nurses/windows');
    }

    public function getCADirectorIndex()
    {
        return $this->redirectToAdmin('ca-director');
    }

    public function getCreateNotifications(string $practiceSlug)
    {
        return $this->redirectToAdmin("practices/$practiceSlug/notifications");
    }

    public function getCreatePractice(string $practiceSlug)
    {
        return $this->redirectToAdmin("practices/$practiceSlug/practice");
    }

    public function getCreatePracticeStaff(string $practiceSlug)
    {
        return $this->redirectToAdmin("practices/$practiceSlug/staff");
    }

    public function getPAM()
    {
        return $this->redirectToAdmin('pam');
    }

    public function getPracticeChargeableServices(string $practiceSlug)
    {
        return $this->redirectToAdmin("practices/$practiceSlug/chargeable-services");
    }

    private function redirectToAdmin($url)
    {
        return redirect()->to(rtrim(config('core.apps.cpm-admin.url'), '/')."/$url");
    }
}
