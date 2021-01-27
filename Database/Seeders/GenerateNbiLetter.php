<?php

namespace CircleLinkHealth\SelfEnrollment\Database\Seeders;

use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\SelfEnrollment\Entities\EnrollmentInvitationLetter;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

class GenerateNbiLetter extends Seeder
{
    const NBI_PRACTICE_NAME = 'bethcare-newark-beth-israel';
    const PRACTICE_SIGNATORY_NAME = 'Dr. Johanny Garcia <br> Beth Prime Practice';
    const UI_REQUESTS             = 'ui_requests';
    private string $practiceNumber;
    private string $signatoryName;
    private string $practiceName;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->practiceNumber       = EnrollmentInvitationLetter::PRACTICE_NUMBER;
        $this->signatoryName        = EnrollmentInvitationLetter::SIGNATORY_NAME;
        $this->practiceName         = EnrollmentInvitationLetter::PRACTICE_NAME;
        $customerSignaturePic = EnrollmentInvitationLetter::CUSTOMER_SIGNATURE_PIC;

        $nbiPractice = $this->getPractice();

        $bodyPageOne = $this->pageOne($customerSignaturePic);
        $bodyPageTwo = $this->pageTwo();
        $bodyPageThree = $this->pageThree();

        EnrollmentInvitationLetter::updateOrCreate(
            [
                'practice_id' => $nbiPractice->id,
            ],
            [
                'practice_logo_src'      => '/img/logos/Nbi/nbi_logo.png',
                'customer_signature_src' => '/img/signatures/nbi/nbi_signature.png',
                'signatory_name'         => self::PRACTICE_SIGNATORY_NAME,
                'letter'                 => json_encode(
                    [
                        'page_1' => [
                            'identifier' => 'letter_main_subject',
                            'body'       => $bodyPageOne,
                        ],

                        'page_2' => [
                            'identifier' => 'letter_main_subject',
                            'body'       => $bodyPageTwo,
                        ],

                        'page_3' => [
                            'identifier' => 'letter_main_subject',
                            'body'       => $bodyPageThree,
                        ],
                    ]
                ),

                self::UI_REQUESTS => json_encode([
                    'logo_position'        => 'text-align:left',
                ]),
            ]
        );
    }

    private function getPractice()
    {
        $nbiPractice = Practice::where('name', '=', self::NBI_PRACTICE_NAME)->first();

        if ( ! App::environment(['production']) && ! $nbiPractice) {
            $nbiPractice = Practice::firstOrCreate(
                [
                    'name' =>  self::NBI_PRACTICE_NAME,
                ],
                [
                    'active'                => 1,
                    'display_name'          => ucfirst(str_replace('-', ' ',  self::NBI_PRACTICE_NAME)),
                    'is_demo'               => 1,
                    'clh_pppm'              => 0,
                    'term_days'             => 30,
                    'outgoing_phone_number' => +1234567890,
                ]
            );
        }
        if ( ! $nbiPractice) {
            throw new Exception('Nbi Practice not found in Practices');
        }

        return $nbiPractice;
    }

    private function pageOne(string $customerSignaturePic)
    {
        $providerName = $this->signatoryName;
        $phoneNumber = $this->practiceNumber;
        $practiceSignatoryName = self::PRACTICE_SIGNATORY_NAME;
        return "<p><span>We are reaching out to you on behalf of your provider, $providerName, to let you know about a program called the </span><strong style='font-weight: 600;'>Beth Care Coordination Program</strong><span>.
        </span></p>
        <p><span>The Beth Care Coordination Program was created to help people living with chronic medical illnesses like diabetes, heart disease and kidney disease. People with these kinds of illnesses have lots of doctor visits, tests and medications. Keeping track of all this can be very hard.</span></p>
        <p><span>The Beth Care Coordination Program can help by connecting you to </span><strong style='font-weight: 600;'>a registered nurse who works with your provider.</strong><span> The nurse is able to answer questions about your care, sends messages to your provider and checks up on how you are doing without you having to leave your home! This at home care can be especially helpful given current events to supplement your appointments with your provider.&nbsp;</span></p>
        <p><span>The nurses in the Program, Rita, Audrey, Dillenis, Elizabeth, Pam and Jean, have many years of experience.</span></p>
        <p><span>Some of the help they provide include:&nbsp;</span></p>
        <ul class='browser-default'>
        <li><span>A care hotline available 24 hours a day / 7 days a week</span></li>
        </ul>
        <ul class='browser-default'>
        <li><span>Help with questions about how to take medications and getting refills</span></li>
        </ul>

        <ul class='browser-default'>
        <li><span>Keeping track of doctor appointments and making sure you get yearly check-ups as our health centers are still open for in person and virtual appointments during the pandemic</span></li>
        </ul>

        <ul class='browser-default'>
        <li><span>Help with getting services to make your day-to-day life&nbsp;</span></li>
        </ul>

        <p><span>One of our coordinators will be calling you from </span><strong> $phoneNumber </strong><span>in the coming days to give you more information. The Beth Care program is covered under Medicare Part B. However, some health insurance plans may charge a co-payment. You can contact your health plan if you are not sure or you can ask for assistance from our care coordinators when they reach out to you. If you&rsquo;d like any more information, please see enclosed Frequently Asked Questions or call the same number, </span><strong>$phoneNumber</strong><span>.</span></p>
        <p><span>On behalf of Dr. $providerName and your care team, we look forward to continuing to work with you for </span><span>better health.</span></p>
        <span>Sincerely,&nbsp;</span>
        <br>
        $customerSignaturePic
        <br>
        $practiceSignatoryName
         <br> Director, Division of General Internal Medicine.
        <br>";
    }

    private function pageTwo()
    {
        return "<p style=\"text-decoration: underline;\"><strong style='font-weight: 600;''>Frequently Asked Questions</strong></p>
        <p><strong style='font-weight: 600;'>What is a chronic illness?</strong></p>
        <p><span>A chronic illness is a long-lasting health problem that can often be controlled with proper treatment and management. A few examples include asthma, diabetes, arthritis, hypertension, and heart disease.</span></p>
        <p><strong style='font-weight: 600;'>What is the Beth Care Coordination program?</strong></p>
        <p><span>The Beth Care coordination program provides support and care between doctor visits to eligible patients who have multiple chronic illnesses. Services include access to a care team who can answer your healthcare questions and help you get the information, appointments, treatments, and care you need to live a healthier life.</span></p>
        <p><strong style='font-weight: 600;'>Why does my doctor want this for me?</strong></p>
        <p><span>While everyone can benefit from having their care coordinated, it can be especially important if you have multiple chronic illnesses. You may be seeing different types of doctors or taking several medications. When your care is coordinated properly, your doctors get the information they need when they need it and have peace of mind knowing that your healthcare needs are being met.</span></p>
        <p><strong style='font-weight: 600;'>Is my information private and secure?</strong></p>
        <p><span>Yes - just like there are rules in banking that protect your financial information, there are rules in healthcare that protect your medical information.&nbsp;</span></p>
        <p><strong style='font-weight: 600;'>But what if I feel fine?</strong></p>
        <p><span>Great. Let's keep it that way. One of the reasons your doctor is inviting you to participate in this program is to help you get and stay as healthy as possible. The program also focuses on things like helping you keep on top of preventive care and helping you find valuable healthcare resources and community services.</span></p>
        <p><strong style='font-weight: 600;'>What does the program cost?</strong></p>
        <p><span>The Beth Care Coordination program is a benefit under Medicare Part B. However, there may be a co-payment for this benefit. If you have a secondary health plan, it will likely cover the remainder. For example, if you have both Medicare and Medicaid, there is </span><span>$0</span><span> out of pocket cost. You can contact your health plan if you&rsquo;re not sure of your coverage or you can ask our care coordinators for assistance when they reach out to you.&nbsp;</span></p> <br>";
    }

    private function pageThree()
    {
        $phoneNumber = $this->practiceNumber;
        return "<p><strong style='font-weight: 600;'>What are the benefits of signing up for the Beth Care Coordination program?</strong></p>
        <p><span>When you sign up, you will be taking an important step toward living a healthier life. Benefits of the program include:</span></p>
        <ul class='browser-default'>
        <li><span>Access to a 24 hours a day / 7 days a week care hotline</span></li>
        </ul>
        <ul class='browser-default'>
        <li><span>A dedicated care team that will coordinate all of your health care, including at another doctor's office, at the pharmacy, in your home, or from a community service organization</span></li>
        </ul>

        <ul class='browser-default'>
        <li><span>A personalized care plan that includes steps you can take to help you reach the goals you and your doctor have set</span></li>
        </ul>

        <ul class='browser-default'>
        <li><span>Updates and communication with your doctors so they have the most accurate and complete information about your health</span></li>
        </ul>

        <ul class='browser-default'>
        <li><span>Help managing your medications, securing appointments and addressing preventive care needs</span></li>
        </ul>

        <ul class='browser-default'>
        <li><span>Help following through on doctor's instructions, including locating and following up with specialists and lab facilities</span></li>
        </ul>

        <ul class='browser-default'>
        <li><span>Greater access to services and support that may help you avoid medical problems, expensive emergency department visits, and hospital stays</span></li>
        </ul>

        <p><strong style='font-weight: 600;'>Can I cancel these services if I change my mind?</strong></p>
        <p><span>Yes. You can discontinue the services at any time for any reason. To do so, just call $phoneNumber and the services will stop at the end of the month that you cancel them.</span></p>
        <p><strong style='font-weight: 600;'>How do I sign up?</strong></p>
        <p><span>If you would like additional information, or are interested in enrolling today, please call $phoneNumber.</span></p>
        <p><span>Your doctor can count on them to look out for you between visits and make sure that you get the information, appointments, treatments and care you need when you need it.</span></p>";
    }
}
