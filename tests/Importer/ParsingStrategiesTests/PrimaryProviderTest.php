<?php

namespace Tests\Importer\ParsingStrategiesTests;


use Tests\TestCase;

/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 3/2/16
 * Time: 6:05 PM
 */
class PrimaryProviderTest extends TestCase
{
    //Here we are counting on the fact that a provider called Jim Provider exists
    private $containsJimProvider = '[{"name":{"prefix":"Dr","given":["Jim"],"family":"Provider","suffix":null},"phone":{"work":"tel:+1-"},"address":{"street":[],"city":null,"state":null,"zip":null,"country":null}},{"name":{"prefix":"Dr.","given":["James"],"family":"Brah","suffix":null},"phone":{"work":""},"address":{"street":["518 Broadway, "],"city":"Monticello","state":"NY","zip":"12701","country":null}},{"name":{"prefix":"Mr.","given":["Victor"],"family":"Marinello","suffix":"RN"},"phone":{"work":"tel:+1-"},"address":{"street":[],"city":null,"state":null,"zip":null,"country":null}    }]';
    private $emptyProvider = '[{"name":{"prefix":"Dr","given":[""],"family":"","suffix":null},"phone":{"work":"tel:+1-"},"address":{"street":[],"city":null,"state":null,"zip":null,"country":null}}]';

    public function getParser()
    {
        return new \App\CLH\CCD\Importer\ParsingStrategies\CareTeam\PrimaryProviders;
    }

    public function test_jim_provider_returns_expected()
    {
        $this->assertArraySubset([\App\WpUser::whereDisplayName('Jim Provider')->first()], $this->getParser()->parse(json_decode($this->containsJimProvider)));
    }

    public function test_empty_provider_returns_false()
    {
        $this->assertFalse($this->getParser()->parse(json_decode($this->emptyProvider)));
    }
}
