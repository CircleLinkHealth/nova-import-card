<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\SnomedToCpmIcdMap;
use CircleLinkHealth\SharedModels\Entities\CarePlanTemplate;
use CircleLinkHealth\SharedModels\Entities\CpmInstruction;
use CircleLinkHealth\SharedModels\Entities\CpmProblem;
use Illuminate\Database\Seeder;

class AddNewDefaultCarePlanTemplate extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $oldCpt = CarePlanTemplate::updateOrCreate([
            'display_name' => 'Old CLH Default (Deprecated)',
            'type'         => 'Old CLH Default (Deprecated)',
        ]);

        $newCpt = CarePlanTemplate::updateOrCreate([
            'display_name' => 'CLH Default',
            'type'         => 'CLH Default',
        ]);

        $config = AppConfig::updateOrCreate([
            'config_key'   => 'default_care_plan_template_id',
            'config_value' => $newCpt->id,
        ]);

        $this->setupCpmProblems();

        foreach ($this->problems() as $problemName => $data) {
            if ($data['instructions']) {
                $instruction = CpmInstruction::create([
                    'is_default' => true,
                    'name'       => $data['instructions'],
                ]);

                $cpmProblem = CpmProblem::whereName($problemName)->first();

                $rel = $newCpt->cpmProblems()->updateExistingPivot($cpmProblem->id, [
                    'has_instruction'    => true,
                    'cpm_instruction_id' => $instruction->id,
                ]);
            }
        }
    }

    /**
     * The array of instructions to be added.
     */
    private function problems(): array
    {
        $problems['Acquired Hypothyroidism'] = [
            'instructions' => '',
        ];

        $problems['Myocardial Infarction'] = [
            'instructions' => '',
        ];

        $problems['Alzheimer\'s Disease'] = [
            'instructions' => '',
        ];

        $problems['Dementia'] = [
            'instructions' => '- Keep a list of important phone numbers next to every phone.

- Have clocks and calendars around the house so you stay aware of the date and what time it is.

- Label important items.

- Develop habits and routines that are easy to follow.

- Plan activities that improve your thinking, such as puzzles, games, baking, or indoor gardening. 

- Have someone nearby for any tasks that may have a risk of injury.
',
        ];

        $problems['Anemia'] = [
            'instructions' => '',
        ];

        $problems['Asthma'] = [
            'instructions' => '- Avoid tobacco smoke—including secondhand smoke— it is unhealthy for everyone, especially people with asthma and/or COPD.

- Limit exposure to common allergens (dust mites, pollen, mold and animal dander) and protect yourself against pollution.

- Keep active to build up strength.

- Build your strength even when you are sitting, by using small weights or rubber tubing to make your arms and shoulders stronger, standing up and sit down, or holding your legs straight out in front of you, then put them down. Repeat these movements several times.

- Know how and when to take your COPD drugs.

- Know the difference between your maintenance vs. rescue medications. If you need help with this, our nurses can assist you.

- Take your quick-relief inhaler when you feel short of breath and need help fast.

- Take your long-term inhaler every day.

- Eat smaller meals more often -- 6 smaller meals a day. It might be easier to breathe when your stomach is not full.
',
        ];

        $problems['Afib'] = [
            'instructions' => '- If you are on a blood thinner, great! Please let us know any bleeding (e.g., gums)

- If you are not on a blood thinner, we would recommend bringing up blood thinners at you next doctor’s visit, if not already discussed. 

- Tell your care team about any changes in the medication you take, including prescription and over-the-counter as well as any supplements. They interfere with some medications given for atrial fibrillation.

- Limit the consumption of alcohol. Sometimes alcohol needs to be avoided to better treat atrial fibrillation. If you are taking blood-thinner medications, alcohol may interfere with them by increasing their effect.

- Never take stimulants such as amphetamines or cocaine. These drugs can speed up your heart rate and trigger atrial fibrillation.
',
        ];

        $problems['BPH'] = [
            'instructions' => '',
        ];

        $problems['Kidney Disease'] = [
            'instructions' => '- Check your blood pressure regularly and report blood pressure over 140/90 to your MD right away.

- Eat 1500 mg or less of sodium daily.

- Eat less than 1500 milligrams to 2700 milligrams of potassium daily.

- As instructed, limit protein in your diet.

- Move around and bend your legs to avoid getting blood clots when you rest for a long period of time.

- Stay on schedule with your lab works.
',
        ];

        $problems['COPD'] = [
            'instructions' => '- Avoid tobacco smoke—including secondhand smoke— it is unhealthy for everyone, especially people with asthma and/or COPD.

- Limit exposure to common allergens (dust mites, pollen, mold and animal dander) and protect yourself against pollution.

- Keep active to build up strength.

- Build your strength even when you are sitting, by using small weights or rubber tubing to make your arms and shoulders stronger, standing up and sit down, or holding your legs straight out in front of you, then put them down. Repeat these movements several times.

- Know how and when to take your COPD drugs.

- Know the difference between your maintenance vs. rescue medications. If you need help with this, our nurses can assist you.

- Take your quick-relief inhaler when you feel short of breath and need help fast.

- Take your long-term inhaler every day.

- Eat smaller meals more often -- 6 smaller meals a day. It might be easier to breathe when your stomach is not full.
',
        ];

        $problems['Depression'] = [
            'instructions' => '- Take your medication as prescribed.

- Contact your care team if anything changes.

- Our RN will check in with a couple questions every month.
',
        ];

        $problems['Diabetes'] = [
            'instructions' => '- Measure your Blood Sugar frequently as indicated in your glucose monitoring kit (or insulin RX, if applicable).

- Make sure you have plenty of lances, test strips, etc., and a working glucometer (we can help you obtain this if needed!).

- Take your medications.

- Have a yearly eye exam.

- Check your feet regularly for dryness, cracking, calluses and sores.

- Keep all your appointments.

- Get all tests as recommended.
',
        ];

        $problems['Glaucoma'] = [
            'instructions' => '',
        ];

        $problems['CHF'] = [
            'instructions' => '- Weigh yourself every day at the same time on the same scale.  

- Call the care team if you gain more than 2lbs in two days (as a small medication adjustment may be needed to avoid hospitalization)

- Take your medicines exactly as prescribed. Don\'t skip doses. If you miss a dose of your medicine, take it as soon as you remember -- unless it\'s almost time for your next dose. In that case, just wait and take your next dose at the normal time. Don\'t take a double dose. If you are unsure, call your care team.

- Subscribe to a low sodium diet.

- Take in no more than 7 cups of liquid per day. During warm weather, restricting sodium intake is key since you need to increase liquid intake to stay hydrated.
',
        ];

        $problems['Hip/Pelvic Fracture'] = [
            'instructions' => '',
        ];

        $problems['High Cholesterol'] = [
            'instructions' => '- Take your cholesterol meds as directed, if applicable.

- Eat more vegetables, they contain fiber and help in lowering your cholesterol.

- When it comes to vegetables, the least prepared food is the better food.

- Eat all meat skinless, opt for lean proteins (chicken, fish, beef) and eat less fried food.

- Cook most things in olive oil and avoid butter.
',
        ];

        $problems['Hypertension'] = [
            'instructions' => '- Learn to take and monitor your own blood pressure. It may save your life.

- Take your blood pressure medication exactly as directed. Do not skip doses. Missing doses can cause your blood pressure to get out of control.

- Avoid medications that contain heart stimulants, including over-the-counter drugs like decongestants (cold, flu, allergy) and pain relievers (ibuprofen, motrin, advil, naproxen, aleve). Check for warnings about high blood pressure on the label.

- Cut back on salt.

- Follow the DASH (Dietary Approaches to Stop Hypertension) eating plan. Try eating more raw nuts, vegetables, legumes (beans, peas, etc.), lean proteins (skinless chicken, fish, beef) and complex carbohydrates (brown rice, wholegrain bread), instead of processed foods.

- Begin an exercise program. Ask your provider how to get started. The American Heart Association recommends aerobic exercise 3 to 4 times a week for an average of 40 minutes at a time, along with resistance exercise 2 days a week, with your provider’s approval. 
',
        ];

        $problems['CAD/IHD'] = [
            'instructions' => '- Do not start or stop any medicines unless your doctor/provider tells you to. Many medicines cannot be used with blood thinners such as Ibuprofen, Aleve or other pain medicines.

- Let us know if you start noticing new chest pains or shortness of breath when exerting yourself.

- Tell your provider right away if you forget to take the medicine, or if you take too much.

- Do not get a flu vaccine before speaking to your care team.
',
        ];

        $problems['Osteoporosis'] = [
            'instructions' => '',
        ];

        $problems['Arthritis'] = [
            'instructions' => '',
        ];

        $problems['Stroke'] = [
            'instructions' => '',
        ];

        $problems['Breast Cancer'] = [
            'instructions' => '',
        ];

        $problems['Colorectal Cancer'] = [
            'instructions' => '',
        ];

        $problems['Prostate Cancer'] = [
            'instructions' => '',
        ];

        $problems['Lung Cancer'] = [
            'instructions' => '',
        ];

        $problems['Endometrial Cancer'] = [
            'instructions' => '',
        ];

        $problems['Smoking'] = [
            'instructions' => '- Each cigarette you smoke damages your lungs, your blood vessels, and cells throughout your body. Even occasional smoking is harmful.

- Quitting is hard. There are nicotine replacements such as nicorette to help you transition. Ask your care coach or doctor for more information! It takes commitment and effort to quit smoking.

- Take quitting one day at a time, even one minute at a time—whatever you need to succeed.

- Many people worry about gaining weight when they quit smoking.  Focus on stopping smoking, which is much worse for your health than gaining a few pounds. 

- When you have the urge to smoke, do something active instead. Walk around the block. Head to the gym. Garden. Do housework. Walk the dog. Play with the kids.

- Ask friends and family members who are smokers not to smoke around you, and try to avoid situations that remind you of smoking.

- Let people help you quit!  Call 1-877-44U-QUIT (1-877-448-7848).  The National Cancer Institute\'s trained counselors are available to provide information and help with quitting in English or Spanish, Monday through Friday, 8:00 a.m. to 8:00 p.m. Eastern Time.  You can also call your state quitline: 1-800-QUIT-NOW (1-800-784-8669) (hours vary).
',
        ];

        return $problems;
    }

    private function setupCpmProblems()
    {
        $defaultCarePlan = CarePlanTemplate::findOrFail(AppConfig::pull('default_care_plan_template_id'));

        CpmProblem::get()->map(function ($cpmProblem) use ($defaultCarePlan) {
            if ( ! in_array($cpmProblem->id, $defaultCarePlan->cpmProblems->pluck('id')->all())) {
                $defaultCarePlan->cpmProblems()->attach($cpmProblem, [
                    'has_instruction' => true,
                    'page'            => 1,
                ]);
            }

            SnomedToCpmIcdMap::updateOrCreate([
                'icd_10_code' => $cpmProblem->default_icd_10_code,
            ], [
                'cpm_problem_id' => $cpmProblem->id,
                'icd_10_name'    => $cpmProblem->name,
                'snomed_code'    => 0,
            ]);

//            $this->command->info("{$cpmProblem->name} has been added");
        });
    }
}
