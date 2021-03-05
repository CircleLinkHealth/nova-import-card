<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SelfEnrollment\Database\Seeders;

use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Traits\UserHelpers;
use CircleLinkHealth\SelfEnrollment\DTO\CameronLetterProductionValueObject;
use CircleLinkHealth\SelfEnrollment\DTO\CameronLetterTestValueObject;
use CircleLinkHealth\SelfEnrollment\Entities\EnrollmentInvitationLetter;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

class GenerateCameronLetter extends Seeder
{
    use UserHelpers;
    const CAMERON_PRACTICE_NAME = 'cameron-memorial';

    const CAMERON_LOGO          = '/img/logos/CameronMemorial/cameron_logo.png';
    const FAUR_SIGNATURE        = '/img/signatures/cameron-memorial/faurs_signature.png';
    const MILLER_SIGNATURE      = '/img/signatures/cameron-memorial/millers_signature.png';
    const SIGNATORY_NAME_FAUR   = 'Dr. Lynn Faur';
    const SIGNATORY_NAME_MILLER = 'Dr. Thomas Miller';

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
        $this->testingMode    = 'production' !== App::environment();
        $practiceNumber       = EnrollmentInvitationLetter::PRACTICE_NUMBER;
        $signatoryName        = EnrollmentInvitationLetter::SIGNATORY_NAME;
        $practiceName         = EnrollmentInvitationLetter::PRACTICE_NAME;
        $customerSignaturePic = EnrollmentInvitationLetter::CUSTOMER_SIGNATURE_PIC;
        $cameronPractice      = $this->getPractice();
        $customUiRequest      = $this->getCustomUiRequest($cameronPractice->id);

        $bodyPageOne = "

<p>$practiceName has invested in a new Personalized Care Program to help patients get care at home, which is especially important given current events, and I'm inviting you to join.</p>

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
                'signatory_name'         => '',
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
        $providersSignatureAttributesTesting = (\app(CameronLetterTestValueObject::class))
            ->testingData($practiceId);

        return $this->providersTestingData($practiceId, $providersSignatureAttributesTesting);
    }

    private function getPractice()
    {
        $cameronPractice = Practice::where('name', '=', self::CAMERON_PRACTICE_NAME)->first();

        if ($this->testingMode) {
            $cameronPractice = Practice::firstOrCreate(
                [
                    'name' => self::CAMERON_PRACTICE_NAME,
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
        $providersSignatureAttributesReal = (\app(CameronLetterProductionValueObject::class))
            ->signatoryProvidersGroup();

        return $this->providersGroupSignatures($practiceId, $providersSignatureAttributesReal);
    }

    private function providersGroupSignatures(int $practiceId, array $providersSignatureAttributesReal)
    {
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

    private function providersTestingData(int $practiceId, array $providersSignatureAttributesTesting)
    {
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

            if (self::FAUR_SIGNATURE === $providerAttr['signature']) {
                $fausSignatureProviders->push($providerUser);
            }

            if (self::MILLER_SIGNATURE === $providerAttr['signature']) {
                $millerSignatureProviders->push($providerUser);
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
