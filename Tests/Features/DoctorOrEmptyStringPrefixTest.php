<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Tests\Features;

use CircleLinkHealth\Customer\Tests\CustomerTestCase;
use Illuminate\Support\Str;

class DoctorOrEmptyStringPrefixTest extends CustomerTestCase
{
    public function test_it_does_not_show_dr_prefix_with_specialty_and_no_suffix()
    {
        $provider                          = $this->provider();
        $provider->providerInfo->specialty = 'Something Else';
        $provider->providerInfo->save();
        $provider->suffix = '';
        $provider->save();

        $this->assertFalse(Str::startsWith($provider->getDoctorFullNameWithSpecialty(), 'Dr.'));
    }

    public function test_it_shows_dr_prefix_with_specialty_and_no_suffix()
    {
        $provider                          = $this->provider();
        $provider->providerInfo->specialty = 'DO';
        $provider->providerInfo->save();
        $provider->suffix = '';
        $provider->save();

        $this->assertTrue(Str::startsWith($provider->getDoctorFullNameWithSpecialty(), 'Dr.'));
    }

    public function test_it_shows_dr_prefix_with_specialty_and_suffix()
    {
        $provider                          = $this->provider();
        $provider->providerInfo->specialty = 'Something Else';
        $provider->providerInfo->save();
        $provider->suffix = 'MD';
        $provider->save();

        $this->assertTrue(Str::startsWith($provider->getDoctorFullNameWithSpecialty(), 'Dr.'));
    }

    public function test_it_shows_dr_prefix_with_suffix_and_no_specialty()
    {
        $provider         = $this->provider();
        $provider->suffix = 'DO';
        $provider->save();
        $provider->providerInfo->specialty = '';
        $provider->providerInfo->save();

        $this->assertTrue(Str::startsWith($provider->getDoctorFullNameWithSpecialty(), 'Dr.'));
    }
}
