<?php

use App\User;
use Faker\Factory;
use Tests\Helpers\CarePlanHelpers;
use Tests\Helpers\UserHelpers;

class PdfableTest extends TestCase
{
    use CarePlanHelpers,
        UserHelpers;
    /**
     * @var Faker\Factory $faker
     */
    protected $faker;

    /**
     * @var
     */
    protected $patient;

    /**
     * @var User $provider
     */
    protected $provider;

    public function test_care_plan_pdf_was_created()
    {
        $fileName = $this->patient->carePlan->toPdf();
        $this->assertNotEmpty($fileName);
    }

    protected function setUp()
    {
        parent::setUp();
        $this->faker = Factory::create();
        $this->provider = $this->createUser(9);
        auth()->login($this->provider);
        $this->patient = $this->createUser(9, 'participant');
        $this->patient->carePlan()->create([
            'care_plan_template_id' => 1,
        ]);
    }

}
