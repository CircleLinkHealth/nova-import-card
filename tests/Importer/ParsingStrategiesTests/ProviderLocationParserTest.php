<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Importer\ParsingStrategiesTests;

use Tests\TestCase;

/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 3/3/16
 * Time: 1:03 PM.
 */
class ProviderLocationParserTest extends TestCase
{
    private $nullLocation    = '{"ids":[],"organization":null,"phone":null,"address":{"street":[null],"city":null,"state":null,"zip":null,"country":null}  }';
    private $workingLocation = '{"ids":[{"extension":null,"root":"","assigningAuthorityName":null}],"organization":"","phone":"","address":{"street":["1234 Summer Street, 6th FL "],"city":"Middletown","state":"NY","zip":"10940","country":"USA"}  }';

    public function getParser()
    {
        return new \App\CLH\CCD\Importer\ParsingStrategies\Location\ProviderLocation();
    }

    public function test_existing_location_is_matched()
    {
        //Assuming Location01 exists
        $this->assertArraySubset(
            [\App\Location::whereName('Location02')->first()],
            $this->getParser()->parse(json_decode($this->workingLocation))
        );
    }

    public function test_null_location_returns_false()
    {
        $this->assertFalse($this->getParser()->parse(json_decode($this->nullLocation)));
    }
}
