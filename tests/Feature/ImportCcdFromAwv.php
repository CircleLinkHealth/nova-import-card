<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use App\Models\CCD\CcdVendor;
use CircleLinkHealth\Customer\Entities\Role;
use CircleLinkHealth\Customer\Entities\User;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tests\TestCase;

class ImportCcdFromAwv extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->setAdminUser();
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
//    public function test_import_from_source_awv_and_create_patient_with_is_awv()
//    {
//        //getting this exception
//        //Illuminate\Contracts\Container\BindingResolutionException : Unresolvable dependency resolving [Parameter #0 [ <required> $model ]] in class Algolia\ScoutExtended\Builder
//        factory(CcdVendor::class)->create();
//        User::where('display_name', '=', 'Myra Jones')->delete();
//        $ccdaPath = storage_path('ccdas/Samples/demo.xml');
//        $response = $this->json('POST', 'api/ccd-importer/imported-medical-records?json&source=importer_awv', [
//            'file' => [new UploadedFile($ccdaPath, 'demo.xml', 'text/xml', null, true)],
//        ]);
//        self::assertTrue(200 === $response->status());
//    }

    /**
     * Creates an admin user to be used with tests.
     *
     * @return User
     */
    private function createAdminUser()
    {
        /** @var User $user */
        $user      = factory(User::class)->create();
        $adminRole = Role::getIdsFromNames(['administrator']);
        $user->attachGlobalRole($adminRole);

        return $user;
    }

    /**
     * Become an admin user for the session.
     */
    private function setAdminUser()
    {
        $admin = $this->createAdminUser();
        $this->be($admin);
    }
}
