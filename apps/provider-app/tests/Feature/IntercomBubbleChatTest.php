<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\Role;
use CircleLinkHealth\Customer\Tests\CustomerTestCase;
use CircleLinkHealth\Customer\Traits\UserHelpers;

class IntercomBubbleChatTest extends CustomerTestCase
{
    use UserHelpers;

    const CONFIG_KEY = 'roles_allowed_bubble_chat';

    protected function setUp(): void
    {
        parent::setUp();
        AppConfig::remove(self::CONFIG_KEY);
        AppConfig::clearCache();
        $fakePractice = \factory(Practice::class)->make();
        $fakePractice->id = 8;
        $fakePractice->save();
    }

    public function test_if_app_config_is_empty_no_user_can_see_bubble_chat()
    {
        self::assertTrue(empty(AppConfig::pull(self::CONFIG_KEY)));

        $careCoach      = $this->createUser(8, 'care-center');
        $careAmbassador = $this->createUser(8, 'care-ambassador');
        $provider       = $this->createUser(8, 'provider');
        $admin          = $this->createUser(8, 'administrator');
        $patient        = $this->createUser(8, 'participant');

        self::assertFalse($careCoach->isAllowedToBubbleChat());
        self::assertFalse($careAmbassador->isAllowedToBubbleChat());
        self::assertFalse($provider->isAllowedToBubbleChat());
        self::assertFalse($admin->isAllowedToBubbleChat());
        self::assertFalse($patient->isAllowedToBubbleChat());
    }

    public function test_only_roles_in_app_config_are_able_to_see_bubble_chat()
    {
        $this->setChatAppConfig('care-center,care-ambassador');
        self::assertFalse(empty(AppConfig::pull(self::CONFIG_KEY)));
        $careCoach      = $this->createUser(8, 'care-center');
        $careAmbassador = $this->createUser(8, 'care-ambassador');
        $provider       = $this->createUser(8, 'provider');
        $admin          = $this->createUser(8, 'administrator');
        $patient        = $this->createUser(8, 'participant');

        self::assertTrue($careCoach->isAllowedToBubbleChat());
        self::assertTrue($careAmbassador->isAllowedToBubbleChat());

        self::assertFalse($provider->isAllowedToBubbleChat());
        self::assertFalse($admin->isAllowedToBubbleChat());
        self::assertFalse($patient->isAllowedToBubbleChat());
    }

    public function test_user_with_multiple_roles_if_one_role_matches_config_role_then_will_return_true()
    {
        $this->setChatAppConfig('care-center, care-ambassador');
        self::assertFalse(empty(AppConfig::pull(self::CONFIG_KEY)));

        $adminCareAmbassador = $this->createUser(8, 'administrator');
        $adminCareAmbassador->attachGlobalRole(Role::whereName('care-ambassador')->firstOrFail()->id);

        self::assertTrue($adminCareAmbassador->hasRole('administrator'));
        self::assertTrue($adminCareAmbassador->hasRole('care-ambassador'));
        self::assertTrue($adminCareAmbassador->isAllowedToBubbleChat());
    }

    private function setChatAppConfig(string $roles)
    {
        AppConfig::create(
            [
                'config_key'   => self::CONFIG_KEY,
                'config_value' => $roles,
            ]
        );
    }
}
