<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use App\Contracts\Efax;
use Tests\TestCase;

class PhaxioServiceTest extends TestCase
{
    public function test_it_does_not_send_fax_without_to()
    {
        $exception = false;
        try {
            $this->getService()->send(
                ['file' => storage_path('pdfs/careplans/sample-careplan.pdf')]
            );
        } catch (\InvalidArgumentException $e) {
            $exception = true;
        }
        $this->assertTrue($exception);
    }

    public function test_it_sends_fax()
    {
        $fax = $this->getService()->createFaxFor('+12012819204')->send(
            ['file' => storage_path('pdfs/careplans/sample-careplan.pdf'), 'direction' => 'received']
        );
        $this->assertEquals(1, $fax->count());
    }

    private function getService()
    {
        return app(Efax::class);
    }
}
