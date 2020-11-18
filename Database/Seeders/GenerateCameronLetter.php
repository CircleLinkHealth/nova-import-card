<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Database\Seeders;

use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Traits\UserHelpers;
use CircleLinkHealth\Eligibility\Entities\EnrollmentInvitationLetter;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;

class GenerateCameronLetter extends Seeder
{
    use UserHelpers;
    const PROVIDER_PROVIDING_SIGNATURE_1 = '';

    const PROVIDER_PROVIDING_SIGNATURE_1_TESTER = 'tomasTouMiller@example.com';
    const PROVIDER_PROVIDING_SIGNATURE_2        = '';
    const PROVIDER_PROVIDING_SIGNATURE_2_TESTER = 'lyunToufaur@example.com';
    /**
     * @bool
     */
    private $environment;

    /**
     * @var
     */
    private $providersInheritingSignatures;
    /**
     * @var
     */
    private $providersProvidingSignatures;
    private $testingMode;

    public function providersSignatorySignatures(int $practiceId)
    {
        if ($this->testingMode) {
            $this->generateTestingData($practiceId);
        }
        $this->getProvidersProvidingSignature($practiceId);
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->environment    = App::environment();
        $this->testingMode    = 'production' !== $this->environment;
        $practiceNumber       = EnrollmentInvitationLetter::PRACTICE_NUMBER;
        $signatoryName        = EnrollmentInvitationLetter::SIGNATORY_NAME;
        $practiceName         = EnrollmentInvitationLetter::PRACTICE_NAME;
        $customerSignaturePic = EnrollmentInvitationLetter::CUSTOMER_SIGNATURE_PIC;
        $cameronPractice      = $this->getPractice();
        $signatoryProviders   = $this->providersSignatorySignatures($cameronPractice->id);

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
                'practice_id' => $cameronPractice->id,
            ],
            [
                'practice_logo_src'      => '/img/logos/CameronMemorial/cameron_logo.png',
                'customer_signature_src' => EnrollmentInvitationLetter::DEPENDED_ON_PROVIDER,
                'letter'                 => json_encode(
                    [
                        'page_1' => [
                            'identifier' => 'letter_main_subject',
                            'body'       => $bodyPageOne,
                        ],
                    ]
                ),
            ]
        );
    }

    private function generateTestingData(int $practiceId)
    {
        $providersProvidingSignatures  = $this->generateTestingProvidersProvidingSignatures($practiceId);
        $providersInheritingSignatures = $this->generateTestingProvidersInheritingSignatures($practiceId);
        $this->processSaveUiRequest($practiceId, $providersProvidingSignatures, $providersInheritingSignatures);
    }

    private function generateTestingProvidersInheritingSignatures(int $practiceId)
    {
        $providerInheritingSignatureAttributes = [
            [
                'first_name' => 'Brandy',
                'last_name'  => 'German',
                'email'      => 'brandyToGermanou@example.com',
                'program_id' => $practiceId,
            ],
            [
                'first_name' => 'Anne',
                'last_name'  => 'Reitz',
                'email'      => ' AnneTouReitz@example.com',
                'program_id' => $practiceId,
            ],

            [
                'first_name' => 'Chrishawna',
                'last_name'  => 'Schieber',
                'email'      => 'chrishawnaTouSchieber@example.com',
                'program_id' => $practiceId,
            ],
        ];

        $providersInheritingSignature = collect();
        foreach ($providerInheritingSignatureAttributes as $provider) {
            $providerUser = User::firstOrCreate(
                [
                    'program_id' => $practiceId,
                    'email'      => $provider['email'],
                ],
                [
                    'first_name' => $provider['first_name'],
                    'last_name'  => $provider['last_name'],
                ]
            );

            $providerUser->providerInfo()->firstOrCreate([]);

            $providerUser->fresh();

            $providersInheritingSignature->push($providerUser);
        }

        return $providersInheritingSignature;
    }

    /**
     * @return \Collection|\Illuminate\Support\Collection
     */
    private function generateTestingProvidersProvidingSignatures(int $practiceId)
    {
        $signatoryProvidersAttributes = [
            [
                'first_name' => 'Thomas',
                'last_name'  => 'Miller',
                'email'      => self::PROVIDER_PROVIDING_SIGNATURE_1_TESTER,
                'program_id' => $practiceId,
            ],

            [
                'first_name' => 'Lynn',
                'last_name'  => 'Faur',
                'email'      => 'lyunToufaur@example.com',
                'program_id' => $practiceId,
            ],
        ];

        $providersProvidingSignature = collect();
        foreach ($signatoryProvidersAttributes as $signatoryProvider) {
            $providerUser = User::firstOrCreate(
                [
                    'program_id' => $practiceId,
                    'email'      => $signatoryProvider['email'],
                ],
                [
                    'first_name' => $signatoryProvider['first_name'],
                    'last_name'  => $signatoryProvider['last_name'],
                ]
            );

            $providerUser->providerInfo()->firstOrCreate([]);

            $providerUser->fresh();

            $providersProvidingSignature->push($providerUser);
        }

        return $providersProvidingSignature;
    }

    private function getPractice()
    {
        $cameronPractice = Practice::where('name', '=', 'cameron-memorial')->first();

        if ($this->testingMode) {
            $cameronPractice = Practice::firstOrCreate(
                [
                    'name' => 'cameron-memorial',
                ],
                [
                    'active'                => 1,
                    'display_name'          => 'Cameron Memorial',
                    'is_demo'               => 1,
                    'clh_pppm'              => 0,
                    'term_days'             => 30,
                    'outgoing_phone_number' => +16419544560,
                ]
            );
        }
        if ( ! $cameronPractice) {
            throw new Exception('Cameron Memorial Practice not found in Practices');
        }

        return $cameronPractice;
    }

    private function getProvidersProvidingSignature(int $practiceId)
    {
        $signatoryProvidersAttributes = [
            'firstNames' => [
                'Thomas',
                'Lynn',
            ],

            'lastNames' => [
                'Miller',
                'Faur',
            ],

            'emails' => [
                'tomasTouMiller@example.com',
                'lyunToufaur@example.com',
            ],
        ];

        $providersProvidingSignature = User::where('program_id', $practiceId)
            ->whereIn('email', $signatoryProvidersAttributes['emails'])
            ->whereIn('first_name', $signatoryProvidersAttributes['firstNames'])
            ->whereIn('last_name', $signatoryProvidersAttributes['lastNames'])
            ->get();
    }

    private function processSaveUiRequest(int $practiceId, Collection $providersProvidingSignatures, Collection $providersInheritingSignatures)
    {
        if ($this->testingMode) {
            $providerProvidingSignatureOne = $providersProvidingSignatures->where('email', self::PROVIDER_PROVIDING_SIGNATURE_1_TESTER)->pluck('id');
            $providerProvidingSignatureTwo = $providersProvidingSignatures->where('email', self::PROVIDER_PROVIDING_SIGNATURE_2_TESTER)->pluck('id');
            $x                             = 1;
        }
    }
}
