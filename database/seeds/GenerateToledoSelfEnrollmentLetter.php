<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Eligibility\Entities\EnrollmentInvitationLetter;
use Illuminate\Database\Seeder;

class GenerateToledoSelfEnrollmentLetter extends Seeder
{
    const UI_REQUESTS = 'ui_requests';

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

        $toledoPractice = $this->getPractice();

        $bodyPageOne = "
<p>$practiceName has invested in a new Personalized Care Program to help patients get care at home, which is especially important given current events, and I'm inviting you to join.</p>
<p>You are getting this invitation because you're eligible according to Medicare guidelines, and we believe you will benefit from it greatly, particularly during this pandemic.</p>
<p>Here's how it works:</p>
<p>&bull; You'll get monthly calls from a Registered Nurse Care Coach to help you manage your health conditions, so you can stay as active and healthy as you can be.</p>
<p>&bull; By staying healthy in between office visits, you'll be less likely to need extra/expensive medical care, including visits to the ER or the hospital, which helps reduce your medical bills.</p>
<p>&bull; You can avoid being on hold when you need something: your nurse can help with prescription refills, appointment scheduling, transportation assistance, and any general questions.</p>
<p>&bull; You can disenroll at any time. This is a voluntary program meant to provide assistance and benefits outside of our physical office.<br />What's the Cost?<br />The program is covered by Medicare. If you have Medicaid or a supplemental insurance, it will likely cover the copay, which means you'll have $0 out-of-pocket costs. In addition, during this crisis, your Dr. may waive co-pays for this kind of remote care. Medicare has invested in this program because it saves them money by keeping people like you healthy.</p>
<p>What's Next?<br />In a few days, you'll get a call from one of our care coordinators from $practiceNumber. They'll be happy to answer your questions, and help you get started if you decide to join during that call.</p>
<p>I look forward to having you join this program to continue keeping you healthy between office visits.</p>
<p>Sincerely,</p>
<p>$customerSignaturePic<br />$signatoryName<br/></p>";

        EnrollmentInvitationLetter::updateOrCreate(
            [
                'practice_id' => $toledoPractice->id,
            ],
            [
                'practice_logo_src'      => '/img/logos/Toledo/toledo_logo.png',
                'customer_signature_src' => 'depended_on_leader_provider',
                'letter'                 => json_encode(
                    [
                        'page_1' => [
                            'identifier' => 'letter_main_subject',
                            'body'       => $bodyPageOne,
                        ],
                    ]
                ),
                self::UI_REQUESTS => json_encode([
                    'logo_position'        => 'text-align:right',
                    'extra_address_header' => [
                        $toledoPractice->display_name => [
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

    private function createToledoPracticeForReviewApp()
    {
        return Practice::firstOrCreate(
            [
                'name' => 'toledo-clinic',
            ],
            [
                'active'                => 1,
                'display_name'          => 'Toledo Clinic',
                'is_demo'               => 1,
                'clh_pppm'              => 0,
                'term_days'             => 30,
                'outgoing_phone_number' => 2025550196,
            ]
        );
    }

    private function getPractice()
    {
        $toledoPractice = \Illuminate\Support\Facades\App::environment(['review', 'local', 'testing']) ?
            $this->createToledoPracticeForReviewApp()
            : Practice::where('display_name', '=', 'Toledo Clinic')->first();

        if ( ! $toledoPractice) {
            throw new Exception('Toledo Practice not found in Practices');
        }

        return $toledoPractice;
    }
}
