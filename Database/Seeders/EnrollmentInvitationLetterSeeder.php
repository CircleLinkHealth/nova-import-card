<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Database\Seeders;

use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Eligibility\Entities\EnrollmentInvitationLetter;
use Illuminate\Database\Seeder;

class EnrollmentInvitationLetterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $enrollmentInvitationLetters = EnrollmentInvitationLetter::first();
//        Run only if table is empty.
        if (empty($enrollmentInvitationLetters)) {
            $providerLastName     = EnrollmentInvitationLetter::PROVIDER_LAST_NAME;
            $locationEnrollButton = EnrollmentInvitationLetter::LOCATION_ENROLL_BUTTON;
            $careAmbassadorPhone  = EnrollmentInvitationLetter::CARE_AMBASSADOR_NUMBER;
            $signatoryName        = EnrollmentInvitationLetter::SIGNATORY_NAME;
            $practiceName         = EnrollmentInvitationLetter::PRACTICE_NAME;
            $customerSignaturePic = EnrollmentInvitationLetter::CUSTOMER_SIGNATURE_PIC;

            $practices = Practice::get();

            foreach ($practices as $practice) {
                EnrollmentInvitationLetter::updateOrCreate(
                    ['practice_id' => $practice->id],
                    [
                        'letter' => json_encode([
                            'page_1' => [
                                'identifier' => 'letter_main_subject',
                                'body'       => "<p>Please note that Dr. $providerLastName invested in a new wellness program called the
                        <strong>Personalized<br>
                            Care Program.</strong></p>
                    <p>It was created to help people living with conditions like diabetes, heart disease and kidney
                        disease,<br>
                        because keeping track of all the Dr. visits, tests and medications can be hard.</p>
                    <p>
                        The Program helps by connecting you to <strong>an experienced registered nurse who works
                            with
                            your<br>provider.</strong>
                        The nurse can answer your questions, send messages to your provider and check up on<br>how
                        you
                        are
                        doing over phone or messages.
                    </p>
                    <p>Some of the help includes:</p>
                    <ul>
                        <li>A care hotline available 24 hours a day / 7 days a week</li>
                        <li>Help with questions about how to take medications and getting refills</li>
                        <li>Setting and tracking doctor appointments and check-ups</li>
                        <li>Help with getting services to make your day-to-day life easier</li>
                        <li>If needed, your care team may loop in other services, like behavioral health specialists
                        </li>
                    </ul>

                    <p>
                        $locationEnrollButton For more information, please
                        see
                        below<br> Frequently Asked Questions or call <strong>$careAmbassadorPhone</strong>
                    </p>

                    <p>
                        This program is covered under Medicare Part B. Some health plans may charge a co-payment.
                        You can<br>
                        contact your health plan if you are not sure or ask for assistance from our care
                        coordinators at
                        <strong>$careAmbassadorPhone</strong>.
                    </p>

                    <p>
                        We look forward to continuing to work with you for better health.
                    </p>
                    Sincerely,
                    <br>
                    $customerSignaturePic
                    <br>
                    $signatoryName
                    $practiceName <br>",
                            ],

                            'page_2' => [
                                'identifier' => 'faq',
                                'body'       => " <p style=\"text-decoration: underline;\"><strong>Frequently Asked Questions</strong></p>
                        <p><strong>What is a chronic illness?</strong></p>
                        <p>A chronic illness is a long-lasting health problem that can often be controlled with proper treatment and management. A few examples include asthma, diabetes, arthritis, hypertension, and heart disease.</p>
                        <p><strong>What is the Personalized Care Program?</strong></p>
                        <p>The Personalized Care Program provides support and care between doctor visits to eligible patients who have multiple chronic illnesses. Services include access to a care team who can answer your healthcare questions and help you get the information, appointments, treatments, and care you need to live a healthier life.</p>
                        <p><strong>Why does my doctor want this for me?</strong></p>
                        <p>While everyone can benefit from having their care coordinated, it can be especially important if you have multiple chronic illnesses. You may be seeing different types of doctors or taking several medications. When your care is coordinated properly, your doctors get the information they need when they need it and have peace of mind knowing that your healthcare needs are being met.</p>
                        <p><strong>Is my information private and secure?</strong></p>
                        <p>Yes - just like there are rules in banking that protect your financial information, there are rules in healthcare that protect your medical information.&nbsp;</p>
                        <p><strong>But what if I feel fine?</strong></p>
                        <p>Great. Let's keep it that way. One of the reasons your doctor is inviting you to participate in this program is to help you get and stay as healthy as possible. The program also focuses on things like helping you keep on top of preventive care and helping you find valuable healthcare resources and community services.</p>
                        <p><strong>What does the program cost?</strong></p>
                        <p>The Personalized Care Program is a benefit under Medicare Part B. However, there may be a co-payment for this benefit. If you have a secondary health plan, it will likely cover the remainder. For example, if you have both Medicare and Medicaid, there is $0 out of pocket cost. You can contact your health plan if you&rsquo;re not sure of your coverage or you can ask our care coordinators for assistance when they reach out to you.&nbsp;</p> <br>",
                            ],

                            'page_3' => [
                                'identifier' => 'faq',
                                'body'       => " <p><strong>What are the benefits of signing up for the Personalized Care Program?</strong></p>
                        <p>When you sign up, you will be taking an important step toward living a healthier life. Benefits of the program include:</p>
                        <ul>
                            <li>Access to a 24 hours a day / 7 days a week care hotline</li>
                            <li>A dedicated care team that will coordinate all of your health care, including at another doctor's office, at the pharmacy, in your home, or from a community service organization</li>
                            <li>A personalized care plan that includes steps you can take to help you reach the goals you and your doctor have set</li>
                            <li>Updates and communication with your doctors so they have the most accurate and complete information about your health</li>
                            <li>Help managing your medications, securing appointments and addressing preventive care needs</li>
                            <li>Help following through on doctor's instructions, including locating and following up with specialists and lab facilities</li>
                            <li>Greater access to services and support that may help you avoid medical problems, expensive emergency department visits, and hospital stays</li>
                        </ul>
                        <p><strong>Can I cancel these services if I change my mind?</strong></p>
                        <p>Yes. You can discontinue the services at any time for any reason. To do so, just call <strong>$careAmbassadorPhone</strong> and the services will stop at the end of the month that you cancel them.</p>
                        <p><strong>How do I sign up?</strong></p>
                        <p>If you would like additional information, or are interested in enrolling today, please call <strong>$careAmbassadorPhone.</strong></p>
                        <p>Your doctor can count on them to look out for you between visits and make sure that you get the information, appointments, treatments and care you need when you need it.</p> <br>",
                            ],
                        ]),
                    ]
                );
            }
        }
    }
}
