<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Browser;

use App\Http\Controllers\Enrollment\SelfEnrollmentController;
use App\SelfEnrollment\Domain\InvitePracticeEnrollees;
use AshAllenDesign\ShortURL\Models\ShortURL;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Eligibility\Entities\EnrollmentInvitationLetter;
use Notification;
use PrepareDataForReEnrollmentTestSeeder;
use Tests\DuskTestCase;
use Tests\Helpers\CustomerTestCaseHelper;

class SelfEnrollmentDuskTestTest extends DuskTestCase
{
    use CustomerTestCaseHelper;
    /**
     * @var
     */
    private $factory;
    private $practice;

    public function test_patients_can_login_and_page_contains_their_name()
    {
        $enrollees = $this->createEnrollees($number = 3);
        Notification::fake();

        InvitePracticeEnrollees::dispatchNow(
            $number,
            $this->practice()->id,
            SelfEnrollmentController::DEFAULT_BUTTON_COLOR,
            ['mail']
        );

        foreach ($enrollees as $enrollee) {
            $enrollee->load('enrollmentInvitationLinks');
            $this->assertTrue(1 === $enrollee->enrollmentInvitationLinks->count());

            $invite = $enrollee->enrollmentInvitationLinks->first();
            $short  = ShortURL::where('destination_url', $invite->url)->firstOrFail();

            $this->browse(function ($browser) use ($enrollee, $short) {
                $browser->visit($short->default_short_url);
                $url = $browser->driver->getCurrentURL();
                $this->assertTrue($short->destination_url === $url);

                $browser->type('birth_date_month', $enrollee->dob->format('m'))
                    ->type('birth_date_day', $enrollee->dob->format('d'))
                    ->type('birth_date_year', $enrollee->dob->format('Y'))
                    ->press('CONTINUE')
                    ->waitForText("Dear {$enrollee->first_name}")
                    ->waitForText($this->practice->display_name)
                    ->waitForText($enrollee->user->billingProviderUser()->display_name)
                    //@todo: For the time this does not assert anything. MAke it assert we get to AWV.
                    //You may visually watch that the test redirects
                    ->press('GET MY CARE COACH');
            });
        }
    }

    private function createEnrollees(int $number = 1)
    {
        if (1 === $number) {
            return $this->factory()->createEnrollee($this->practice(), $this->provider());
        }

        $coll = collect();

        for ($i = 0; $i < $number; ++$i) {
            $coll->push($this->factory()->createEnrollee($this->practice(), $this->provider()));
        }

        return $coll;
    }

    private function factory()
    {
        if (is_null($this->factory)) {
            $this->factory = $this->app->make(PrepareDataForReEnrollmentTestSeeder::class);
        }

        return $this->factory;
    }

    private function practice()
    {
        if (is_null($this->practice)) {
            $this->practice = factory(Practice::class)->create();
            EnrollmentInvitationLetter::create([
                'practice_id' => $this->practice->id,
            ]);
            $this->factory()->firstOrCreateEnrollmentSurvey();
        }

        return $this->practice;
    }
}
