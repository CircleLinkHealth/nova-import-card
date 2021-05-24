<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Tests;

use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Tests\CustomerTestCase;
use CircleLinkHealth\Eligibility\MedicalRecord\Templates\HtmlInXmlMedicalRecord;
use CircleLinkHealth\SharedModels\Entities\Ccda;

class HtmlInXmlParserTest extends CustomerTestCase
{
    public function test_it_can_parse_html()
    {
        /** @var Practice $practice */
        $practice = Practice::firstOrCreate(
            [
                'name' => 'estill-medical-clinic',
            ],
            [
                'display_name' => 'Estill Medical Clinic',
            ]
        );

        /** @var Location $location */
        $location = factory(Location::class)->create(['practice_id' => $practice->id]);

        $userId = $this->superadmin()->id;

        $xml = file_get_contents(__DIR__.'/Test Data Estill.xml');
        /** @var Ccda $ccda */
        $ccda = Ccda::create([
            'practice_id' => $practice->id,
            'location_id' => $location->id,
            'user_id'     => $userId,
            'xml'         => $xml,
        ]);

        $json   = $ccda->bluebuttonJson(true);
        $mr     = new HtmlInXmlMedicalRecord($json, $ccda->getXml());
        $result = $mr->toArray();

        self::assertEquals('html-in-xml', $result['type']);
        self::assertEquals(46, sizeof($result['problems']));
        self::assertEquals('OTHER B-COMPLEX DEFICIENCIES', $result['problems'][0]['name']);
    }
}
