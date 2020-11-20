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
use Illuminate\Support\Facades\App;

class GenerateCameronLetter extends Seeder
{
    use UserHelpers;
    const ANNNE_REITZ_MAIL_REAL         = 'areitz@cameronmch.com';
    const ANNNE_REITZ_MAIL_TEST         = 'AnneTouReitz@example.com';
    const BRANDY_GERMAN_MAIL_REAL       = 'bgerman@cameronmch.com';
    const BRANDY_GERMAN_MAIL_TEST       = 'brandyToGermanou@example.com';
    const CAMERON_LOGO                  = '/img/logos/CameronMemorial/cameron_logo.png';
    const CHRISHAWVA_SCHIEBER_MAIL_REAL = 'cschieber@cameronmch.com';
    const CHRISHAWVA_SCHIEBER_MAIL_TEST = 'chrishawnaTouSchieber@example.com';
    const FAUR_MAIL_REAL                = 'lfaur@cameronmch.com';
    const FAUR_MAIL_TEST                = 'lyunToufaur@example.com';
    const FAUR_SIGNATURE                = '/img/signatures/cameron-memorial/faurs_signature.png';
    const MILLER_MAIL_REAL              = 'tmiller@cameronmch.com';
    const MILLER_MAIL_TEST              = 'tomasTouMiller@example.com';
    const MILLER_SIGNATURE              = '/img/signatures/cameron-memorial/millers_signature.png';
    const SIGNATORY_NAME_FAUR           = 'Dr. Lynn Faur';
    const SIGNATORY_NAME_MILLER         = 'Dr. Thomas Miller';

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

    public function getCustomUiRequest(int $practiceId)
    {
        if ($this->testingMode) {
            return $this->generateTestingData($practiceId);
        }

        return $this->getProvidersProvidingGroupSignature($practiceId);
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->environment    = App::environment();
        $this->testingMode    = /*'production' !== $this->environment*/false;
        $practiceNumber       = EnrollmentInvitationLetter::PRACTICE_NUMBER;
        $signatoryName        = EnrollmentInvitationLetter::SIGNATORY_NAME;
        $practiceName         = EnrollmentInvitationLetter::PRACTICE_NAME;
        $customerSignaturePic = EnrollmentInvitationLetter::CUSTOMER_SIGNATURE_PIC;
        $cameronPractice      = $this->getPractice();
        $customUiRequest      = $this->getCustomUiRequest($cameronPractice->id);

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
                'practice_logo_src'      => self::CAMERON_LOGO,
                'customer_signature_src' => EnrollmentInvitationLetter::DEPENDED_ON_PROVIDER_GROUP,
                'ui_requests'            => json_encode($customUiRequest),
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

    /**
     * @return array
     */
    private function generateTestingData(int $practiceId)
    {
        $providersSignatureAttributesTesting = [
            [
                'first_name' => 'Thomas',
                'last_name'  => 'Miller',
                'email'      => self::MILLER_MAIL_TEST,
                'program_id' => $practiceId,
                'signature'  => self::MILLER_SIGNATURE,
            ],

            [
                'first_name' => 'Lynn',
                'last_name'  => 'Faur',
                'email'      => self::FAUR_MAIL_TEST,
                'program_id' => $practiceId,
                'signature'  => self::FAUR_SIGNATURE,
            ],

            [
                'first_name' => 'Brandy',
                'last_name'  => 'German',
                'email'      => self::BRANDY_GERMAN_MAIL_TEST,
                'program_id' => $practiceId,
                'signature'  => self::MILLER_SIGNATURE,
            ],
            [
                'first_name' => 'Anne',
                'last_name'  => 'Reitz',
                'email'      => self::ANNNE_REITZ_MAIL_TEST,
                'program_id' => $practiceId,
                'signature'  => self::MILLER_SIGNATURE,
            ],

            [
                'first_name' => 'Chrishawna',
                'last_name'  => 'Schieber',
                'email'      => self::CHRISHAWVA_SCHIEBER_MAIL_TEST,
                'program_id' => $practiceId,
                'signature'  => self::FAUR_SIGNATURE,
            ],
        ];

        $fausSignatureProviders   = collect();
        $millerSignatureProviders = collect();
        foreach ($providersSignatureAttributesTesting as $providerAttr) {
            $providerUser = User::firstOrCreate(
                [
                    'program_id' => $practiceId,
                    'email'      => $providerAttr['email'],
                ],
                [
                    'first_name'   => $providerAttr['first_name'],
                    'last_name'    => $providerAttr['last_name'],
                    'display_name' => $providerAttr['first_name'].' '.$providerAttr['last_name'],
                ]
            );

            $providerUser->providerInfo()->firstOrCreate([]);

            $providerUser->fresh();

            if ( ! $providerUser) {
                if (self::FAUR_SIGNATURE === $providerAttr['signature']) {
                    $fausSignatureProviders->push($providerUser);
                }

                if (self::MILLER_SIGNATURE === $providerAttr['signature']) {
                    $millerSignatureProviders->push($providerUser);
                }
            }
        }

        return [
            self::FAUR_SIGNATURE => array_merge(
                $fausSignatureProviders->pluck('id')->toArray(),
                ['signatory_group_name' => self::SIGNATORY_NAME_FAUR]
            ),
            self::MILLER_SIGNATURE => array_merge(
                $millerSignatureProviders->pluck('id')->toArray(),
                ['signatory_group_name' => self::SIGNATORY_NAME_MILLER]
            ),
        ];
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

    private function getProvidersProvidingGroupSignature(int $practiceId)
    {
        $providersSignatureAttributesReal = [
            [
                'first_name' => 'Thomas',
                'last_name'  => 'Miller',
                'email'      => self::MILLER_MAIL_REAL,
                'signature'  => self::MILLER_SIGNATURE,
            ],

            [
                'first_name' => 'Lynn',
                'last_name'  => 'Faur',
                'email'      => self::FAUR_MAIL_REAL,
                'signature'  => self::FAUR_SIGNATURE,
            ],

            [
                'first_name' => 'Brandy',
                'last_name'  => 'German',
                'email'      => self::BRANDY_GERMAN_MAIL_REAL,
                'signature'  => self::MILLER_SIGNATURE,
            ],
            [
                'first_name' => 'Anne',
                'last_name'  => 'Reitz',
                'email'      => self::ANNNE_REITZ_MAIL_REAL,
                'signature'  => self::MILLER_SIGNATURE,
            ],

            [
                'first_name' => 'Chrishawna',
                'last_name'  => 'Schieber',
                'email'      => self::CHRISHAWVA_SCHIEBER_MAIL_REAL,
                'signature'  => self::FAUR_SIGNATURE,
            ],
        ];

        $fausSignatureProviders   = collect();
        $millerSignatureProviders = collect();
        foreach ($providersSignatureAttributesReal as $providerAttr) {
            $providerUser = User::where('program_id', $practiceId)
                ->where('email', $providerAttr['email'])
                ->where('first_name', $providerAttr['first_name'])
                ->where('last_name', $providerAttr['last_name'])
                ->first();

            if ( ! is_null($providerUser)) {
                if (self::FAUR_SIGNATURE === $providerAttr['signature']) {
                    $fausSignatureProviders->push($providerUser);
                }

                if (self::MILLER_SIGNATURE === $providerAttr['signature']) {
                    $millerSignatureProviders->push($providerUser);
                }
            } else {
                \Log::warning("Provider with email {$providerAttr['email']} not found during Cameron letter data generation.");
            }
        }

        return [
            self::FAUR_SIGNATURE => array_merge(
                $fausSignatureProviders->pluck('id')->toArray(),
                ['signatory_group_name' => self::SIGNATORY_NAME_FAUR]
            ),
            self::MILLER_SIGNATURE => array_merge(
                $millerSignatureProviders->pluck('id')->toArray(),
                ['signatory_group_name' => self::SIGNATORY_NAME_MILLER]
            ),
        ];
    }
}
