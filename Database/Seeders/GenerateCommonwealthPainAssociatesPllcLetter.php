<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Database\Seeders;

use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Eligibility\Entities\EnrollmentInvitationLetter;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

class GenerateCommonwealthPainAssociatesPllcLetter extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @throws Exception
     *
     * @return void
     */
    public function run()
    {
        $providerLastName                  = EnrollmentInvitationLetter::PROVIDER_LAST_NAME;
        $locationEnrollButton              = EnrollmentInvitationLetter::LOCATION_ENROLL_BUTTON;
        $practiceNumber                    = EnrollmentInvitationLetter::PRACTICE_NUMBER;
        $signatoryName                     = EnrollmentInvitationLetter::SIGNATORY_NAME;
        $practiceName                      = EnrollmentInvitationLetter::PRACTICE_NAME;
        $customerSignaturePic              = EnrollmentInvitationLetter::CUSTOMER_SIGNATURE_PIC;
        $optionalParagraph                 = EnrollmentInvitationLetter::OPTIONAL_PARAGRAPH;
        $locationEnrollButtonSecondVersion = EnrollmentInvitationLetter::LOCATION_ENROLL_BUTTON_SECOND;
        $optionalTitle                     = EnrollmentInvitationLetter::OPTIONAL_TITLE;

        $practice = $this->getPractice();

        $bodyPageOne = "<p><span>$practiceName</span>
<span> has invested in a new Personalized Care Program to help patients get care at home, which is especially important given current events, and I'm inviting you to join.</span></p>

<p><span>You are getting this invitation because you're eligible according to Medicare guidelines, and we believe you will benefit from it greatly, particularly during this pandemic.</span></p>

<p><span>Here's how it works:</span></p>
<ul class='browser-default'>
<li><span>You'll get monthly calls from a Registered Nurse Care Coach to help you manage your health conditions, so you can stay as active and healthy as you can be.</span></li>
</ul>
<ul class='browser-default'>
<li><span>By staying healthy in between office visits, you'll be less likely to need extra/expensive medical care, including visits to the ER or the hospital, which helps reduce your medical bills.</span></li>
</ul>
<ul class='browser-default'>
<li><span>You can avoid being on hold when you need something: your nurse can help with prescription refills, appointment scheduling, transportation assistance, and any general questions.</span></li>
</ul>
<ul class='browser-default'>
<li><span>You can disenroll at any time. This is a voluntary program meant to provide assistance and benefits outside of our physical office.</span></li>
</ul>
<p style=\"text-decoration: underline;\"><span>What's the Cost?</span></p>
<p><span>The program is covered by Medicare. If you have Medicaid or a supplemental insurance, it will likely cover the copay, which means you'll have $0 out-of-pocket costs. In addition, during this crisis, your Dr. may waive co-pays for this kind of remote care. Medicare has invested in this program because it saves them money by keeping people like you healthy.</span></p>

<p style=\"text-decoration: underline;\"><span>What's Next?</span></p>
<p><span>$locationEnrollButton For more information, please see below Frequently Asked Questions or call $practiceNumber</span><span> to be connected with one of our care coordinators</span><strong style='font-weight: 600;'>.</strong></p>

<p><span>I look forward to having you join this program to continue keeping you healthy between office visits.</span></p>

<p><span>On Behalf of Our Medical Staff,&nbsp;</span></p>
<p><span>$customerSignaturePic</span></p>
<p><span>$signatoryName</span></p>
<p><span>$practiceName</span></p>
<p><br/></p>";

        $bodyPageTwo = "<p style=\"text-decoration: underline;\"><strong style='font-weight: 600;'>Frequently Asked Questions</strong></p>
                        <p><strong style='font-weight: 600;'>What is the Personalized Care Program?</strong></p>
                        <p>The Personalized Care Program provides support and care between doctor visits to eligible patients. Services include access to a care team who can answer your healthcare questions and help you get the information, appointments, treatments, and care you need to live a healthier life.</p>
                        
                        <p><strong style='font-weight: 600;'>Why does my doctor want this for me?</strong></p>
                        <p>While everyone can benefit from having their care coordinated, it can be especially important if you are managing multiple conditions. You may be seeing different types of doctors or taking several medications. When your care is coordinated properly, your doctors get the information they need when they need it and have peace of mind knowing that your healthcare needs are being met.</strong></p>
                        
                        <p><strong style='font-weight: 600;'>Is my information private and secure?</strong></p>
                        <p>Yes - just like there are rules in banking that protect your financial information, there are rules in healthcare that protect your medical information.</p>
                        
                        <p><strong style='font-weight: 600;'>But what if I feel fine?</strong></p>
                        <p>Great. Let's keep it that way. One of the reasons your doctor is inviting you to participate in this program is to help you get and stay as healthy as possible. The program also focuses on things like helping you keep on top of preventive care and helping you find valuable healthcare resources and community services.</p>
                     
                        <p><strong style='font-weight: 600;'>What does the program cost?</strong></p>
                        <p>The Personalized Care Program is a benefit under Medicare Part B. However, there may be a co-payment for this benefit. If you have a secondary health plan, it will likely cover the remainder. For example, if you have both Medicare and Medicaid, there is $0 out of pocket cost.  In addition, during this crisis, your Dr. may waive co-pays for this kind of remote care. You can contact your health plan if you’re not sure of your coverage or you can ask our care coordinators for assistance when they reach out to you.</p> <br>";

        $bodyPageThree = "<p><strong style='font-weight: 600;'>What are the benefits of signing up for the Personalized Care Program?</strong></p>
                        <p>When you sign up, you will be taking an important step toward living a healthier life. Benefits of the program include:</p>
                        <ul class='browser-default'>
                            <li>
                                Access to a 24 hours a day / 7 days a week care hotline
                            </li>
                            <li>
                                A dedicated care team that will coordinate all of your health care, including at another doctor's office, at the pharmacy, in your home, or from a community service organization
                            </li>
                            <li>
                                A personalized care plan that includes steps you can take to help you reach the goals you and your doctor have set
                            </li>
                            <li>
                                Updates and communication with your doctors so they have the most accurate and complete information about your health
                            </li>
                            <li>
                                Help managing your medications, securing appointments and addressing preventive care needs
                            </li>
                            <li>
                                Help following through on doctor's instructions, including locating and following up with specialists and lab facilities
                            </li>
                            <li>
                                Greater access to services and support that may help you avoid medical problems, expensive emergency department visits, and hospital stays
                            </li>
                        </ul>
                        <p>
                            Your doctor can count on them to look out for you between visits and make sure that you get the information, appointments, treatments and care you need when you need it.
                        </p>
                        
                        <p>
                            <strong style='font-weight: 600;'>Can I cancel these services if I change my mind?</strong>
                        </p>
                        <p>Yes. You can discontinue the services at any time for any reason. To do so, just call <strong style='font-weight: 600;'>$practiceNumber</strong> and the services will stop at the end of the month that you cancel them.</p>
                        
                        <p><strong style='font-weight: 600;'>$optionalTitle</strong></p>
                        <p>$optionalParagraph</p>
                        <p>$locationEnrollButtonSecondVersion</p> <br>";

        EnrollmentInvitationLetter::updateOrCreate(
            [
                'practice_id' => $practice->id,
            ],
            [
                'practice_logo_src'      => '/img/logos/CommonWealth/commonwealth_logo.png',
                'customer_signature_src' => '/img/logos/CommonWealth/commonwealth_signature.png',
                'letter'                 => json_encode([
                    'page_1' => [
                        'identifier' => 'letter_main_subject',
                        'body'       => $bodyPageOne,
                    ],

                    'page_2' => [
                        'identifier' => 'faq',
                        'body'       => $bodyPageTwo,
                    ],

                    'page_3' => [
                        'identifier' => 'faq',
                        'body'       => $bodyPageThree,
                    ],
                ]),
            ]
        );
    }

    private function getPractice()
    {
        $commonwealthName     = 'commonwealth-pain-associates-pllc';
        $commonwealthPractice = Practice::where('name', '=', $commonwealthName)->first();
        if (App::environment(['testing'])) {
            $commonwealthPractice = Practice::firstOrCreate(
                [
                    'name' => $commonwealthName,
                ],
                [
                    'active'                => 1,
                    'display_name'          => ucfirst(str_replace('-', ' ', $commonwealthName)),
                    'is_demo'               => 1,
                    'clh_pppm'              => 0,
                    'term_days'             => 30,
                    'outgoing_phone_number' => 2025550196,
                ]
            );
        }
        if ( ! $commonwealthPractice) {
            throw new Exception('Commonwealth Practice not found in Practices');
        }

        return $commonwealthPractice;
    }
}
