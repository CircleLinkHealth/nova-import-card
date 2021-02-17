<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SelfEnrollment\Database\Seeders;

use App;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\SelfEnrollment\Entities\EnrollmentInvitationLetter;
use Exception;
use Illuminate\Database\Seeder;

class GenerateMarillacHealthLetter extends Seeder
{
    const SIGNATORY_NAME = 'Crystal Owens, MD <br> Chief Medical Officer, MarillacHealth';
    const MARILLAC_PRACTICE_NAME = 'marillac-clinic-inc';

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $practiceNumber       = EnrollmentInvitationLetter::PRACTICE_NUMBER;
        $signatoryName        = EnrollmentInvitationLetter::SIGNATORY_NAME;
        $practiceName         = EnrollmentInvitationLetter::PRACTICE_NAME;
        $customerSignaturePic = EnrollmentInvitationLetter::CUSTOMER_SIGNATURE_PIC;
        $marillacPractice     = $this->getPractice();

        $bodyPageOne = "

<p>Marillac Health has invested in a new Personalized Care Program to help patients get care at home, which is especially important given current events, and I'm inviting you to join.</p>

<p>You are getting this invitation because you're eligible according to Medicare guidelines, and we believe you will benefit from it greatly, particularly during this pandemic.</p>

<p>Here's how it works:</p>

<li>You'll get monthly calls from a Registered Nurse Care Coach to help you manage your health conditions, so you can stay as active and healthy as you can be.</li><br>
<li>By staying healthy in between office visits, you'll be less likely to need extra/expensive medical care, including visits to the ER or the hospital, which helps reduce your medical bills.</li><br>
<li>You can avoid being on hold when you need something: your nurse can help with prescription refills, appointment scheduling, transportation assistance, and any general questions.</li><br>
<li>You can disenroll at any time. This is a voluntary program meant to provide assistance and benefits outside of our physical office.</li>

<p style='text-decoration: underline;'>What's the Cost?</p>
<p>The program is covered by Medicare. If you have Medicaid or a supplemental insurance, it will likely cover the copay, which means you'll have $0 out-of-pocket costs. In addition, during this crisis, your Dr. may waive co-pays for this kind of remote care. Medicare has invested in this program because it saves them money by keeping people like you healthy.</p>

<p style='text-decoration: underline;'>What's Next?</p>
<p>In a few days, you'll get a call from one of our care coordinators from $practiceNumber. They'll be happy to answer your questions, and help you get started if you decide to join during that call.</p>
<p>I look forward to having you join this program to continue keeping you healthy between office visits.</p>
<p>Sincerely,</p>
<p>$customerSignaturePic<br />$signatoryName<br/></p>";

        EnrollmentInvitationLetter::updateOrCreate(
            [
                'practice_id' => $marillacPractice->id,
            ],
            [
                'practice_logo_src'      => '/img/logos/Marillac/marillac-clinic.png',
                'customer_signature_src' => '/img/signatures/marillac-clinic/marillac_signature.png',
                'signatory_name'         => self::SIGNATORY_NAME,
                'letter'                 => json_encode(
                    [
                        'page_1' => [
                            'identifier' => 'letter_main_subject',
                            'body'       => $bodyPageOne,
                        ],
                    ]
                ),

                'ui_requests' => json_encode([
                    'logo_position'        => 'text-align:left',
                    'extra_address_header' => [
                        $marillacPractice->name => [
                            'address_line_1',
                            'city',
                            'state',
                            'postal_code', // zip
                        ],
                    ],
                ]),
            ]
        );
    }

    private function getPractice()
    {
        $marillacPractice = Practice::where('name', '=', 'marillac-clinic-inc')->first();

        if ( ! App::environment(['production'])) {
            $marillacPractice = Practice::firstOrCreate(
                [
                    'name' => self::MARILLAC_PRACTICE_NAME,
                ],
                [
                    'active'                => 1,
                    'display_name'          => 'Marillac Clinic, Inc',
                    'is_demo'               => 1,
                    'clh_pppm'              => 0,
                    'term_days'             => 30,
                    'outgoing_phone_number' => +16419544560,
                ]
            );
        }
        if ( ! $marillacPractice) {
            throw new Exception('Marillac Health Practice not found in Practices');
        }

        return $marillacPractice;
    }
}