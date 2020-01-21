<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\SharedModels\Entities\CpmInstruction;
use CircleLinkHealth\SharedModels\Entities\CpmProblem;
use Illuminate\Database\Seeder;

class CpmDefaultInstructionSeeder extends Seeder
{
    public function behavioralInstructions()
    {
        return [
            'Depression' => [
                'problems' => [
                    'Depression',
                ],
                'instruction' => "- Take your medication as prescribed.\n\n- Contact your care team if anything changes.\n\n- Our RN will check in with a couple questions every month.\n\n- Watch for medication side effects like changing thought patterns or upset stomach.\n\n- Some medications have recommended lab tests which your doctor will prescribe; discuss this with our telephone RNs to coordinate if needed.\n\n- Make sure to do things that you enjoy (Gardening, going to a move, taking a walk, etc…)\n\n- Reward yourself for small successes.\n\n- Take care of your body. Eat a healthy diet and establish an exercise plan of at least three times a week. Even moderate exercise will help you feel better.\n\n- Avoid alcohol which can make depression worse.\n\n- For additional information, call the National Depressive Assoc. at (800) 826-3632
                ",
            ],
            'Dementia' => [
                'problems' => [
                    'Dementia',
                ],
                'instruction' => "- Keep a list of important phone numbers next to every phone.\n\n- Have clocks and calendars around the house so you stay aware of the date and time.\n\n- Label important items.\n\n- Develop habits and routines that are easy to follow.\n\n- Plan activities that improve your thinking, such as puzzles, games, baking, or indoor gardening.\n\n- Keep pictures of friends and family around the house.\n\n- Keep good sleep hygiene: Avoid beeping or noises at night time. Avoid fluids before bed time and don’t watch TV in bed.\n\n- Try to keep shades up during the day and down during the night.\n\n- Exercising 30 minutes three times a week, or more, will improve health.\n\n- Have someone nearby for any tasks that may have a risk of injury.\n\n- For additional information, call the Alzheimer’s Foundation of America at (866) 232-8484
                ",
            ],
            'Post-traumatic stress' => [
                'problems' => [
                    'Post-traumatic stress',
                ],
                'instruction' => "- Join a Peer Support / Self-Help group. They are helpful. To find a group, visit https://www.nami.org/Find-Your-Local-NAMI, and select your state on the right side.\n\n- Get adequate rest at night. Keeping good sleep hygiene can help: Avoid beeping or noises at night time. Avoid fluids and screens like TVs, mobile phones/tablets before and during bed time.\n\n- Try to reduce or avoid caffeine and nicotine, which can worsen anxiety.\n\n- Get physical exercise multiple times per week.\n\n- When you feel anxious, take a brisk walk or jump into a hobby to re-focus.\n\n- Spend time with supportive people like family or friends. You don’t have to talk about your feelings, just being around loved ones helps.\n\n- Keep a diary or journal about your anxiety and emotions. Consider the causes and possible solutions.
                ",
            ],
            'Anxiety and Stress' => [
                'problems' => [
                    'Anxiety and Stress',
                ],
                'instruction' => "- Try to identify the areas of stress in your life: they may not be obvious, like:\n\n\t- Overload: feeling you have too many responsibilities and cannot take care of everything at once\n\t- Helplessness: feeling that you cannot solve your problems\n\t- Daily hassles of life: vehicle trouble, traffic, bills\n\t- Major life challenges: both good and bad\n\n- Eating a healthy diet reduces stress: More vegetables and less sugary or fried foods.\n\n- Exercise at least three times a week. Even moderate exercise will help you feel better. \n\n- Let us know if you have trouble breathing or chest pain, severe headaches, bad thoughts, or irregular heartbeats.
                ",
            ],
            'Psychosis & Schizophrenia' => [
                'problems' => [
                    'Psychosis & Schizophrenia',
                ],
                'instruction' => "- Avoid drugs that are known to cause you trouble.\n\n- Do not stop your medications on your own.\n\n- Avoid narcotics and alcohol.\n\n- Focus on and schedule meaningful activities.\n\n- Spend time and gain support from staff, family and other supportive people\n\n- Eating a healthy diet helps: More vegetables and less sugary or fried foods.\n\n- Exercise at least three times a week. Even moderate exercise will help.\n\n- Join a Peer Support / Self-Help group. They are helpful. To find a group, visit https://www.nami.org/Find-Your-Local-NAMI, and select your state on the right side.
                ",
            ],
            'Substance Abuse (ex-Alcoholism)' => [
                'problems' => [
                    'Substance Abuse (ex-Alcoholism)',
                ],
                'instruction' => "- Develop alternative behaviours to drug use, such as exercise and scheduling meaningful activities you enjoy.\n\n- Eating a healthy diet helps: More vegetables and less sugary or fried foods.\n\n- Exercise at least three times a week. Even moderate exercise will help you feel better.\n\n- Join a Peer Support / Self-Help group. They are helpful. To find a group, visit https://www.nami.org/Find-Your-Local-NAMI, and select your state on the right side.\n\n- Help is just a phone call away: 1-800-662-HELP
                ",
            ],
            'Alcoholism' => [
                'problems' => [
                    'Alcoholism',
                ],
                'instruction' => "- Sobriety is achievable.\n\n- Develop alternative behaviours to alcohol use, such as exercise and scheduling meaningful activities you enjoy.\n\n- Eating a healthy diet helps: More vegetables and less sugary or fried foods.\n\n- Exercise at least three times a week. Even moderate exercise will help you feel better.\n\n- Join a Peer Support / Self-Help group. They are helpful. To find a group, visit https://www.nami.org/Find-Your-Local-NAMI, and select your state on the right side.\n\n- Seek immediate medical assistance if you have abdominal pain, fever over 100.5, repeated vomiting or vomiting blood.
                ",
            ],
            'Bipolar' => [
                'problems' => [
                    'Bipolar',
                ],
                'instruction' => "- Bipolar is one of most treatable conditions.\n\n- Do NOT discontinue medications when you feel better, always consult with your doctor before changing your care plan.\n\n- Eating a healthy diet helps: More vegetables and less sugary or fried foods.\n\n- Exercise at least three times a week. Even moderate exercise will help you feel better.\n\n
                ",
            ],
        ];
    }

    public function instructions()
    {
        return [
            'Hypertension' => "- Learn to take and monitor your own blood pressure. It may save your life.\n\n- Take your blood pressure medication exactly as directed. Do not skip doses. Missing doses can cause your blood pressure to get out of control.\n\n- Avoid medications that contain heart stimulants, including over-the-counter drugs like decongestants (cold, flu, allergy) and pain relievers (ibuprofen, motrin, advil, naproxen, aleve). Check for warnings about high blood pressure on the label.\n\n- Cut back on salt.\n\n- Follow the DASH (Dietary Approaches to Stop Hypertension) eating plan. Try eating more raw nuts, vegetables, legumes (beans, peas, etc.), lean proteins (skinless chicken, fish, beef) and complex carbohydrates (brown rice, wholegrain bread), instead of processed foods.\n\n- Begin an exercise program. Ask your provider how to get started. The American Heart Association recommends aerobic exercise 3 to 4 times a week for an average of 40 minutes at a time, along with resistance exercise 2 days a week, with your provider’s approval. 
            ",
            'High Cholesterol' => "- Take your cholesterol meds as directed, if applicable.\n\n- Eat more vegetables, they contain fiber and help in lowering your cholesterol.\n\n- When it comes to vegetables, the least prepared food is the better food.\n\n- Eat all meat skinless, opt for lean proteins (chicken, fish, beef) and eat less fried food.\n\n- Cook most things in olive oil and avoid butter.
            ",
            'CAD/IHD' => "- Do not start or stop any medicines unless your doctor/provider tells you to. Many medicines cannot be used with blood thinners such as Ibuprofen, Aleve or other pain medicines.\n\n- Let us know if you start noticing new chest pains or shortness of breath when exerting yourself.\n\n- Tell your provider right away if you forget to take the medicine, or if you take too much.\n\n- Do not get a flu vaccine before speaking to your care team.
            ",
            'CHF' => "- Weigh yourself every day at the same time on the same scale.  \n\n- Call the care team if you gain more than 2lbs in two days (as a small medication adjustment may be needed to avoid hospitalization)\n\n- Take your medicines exactly as prescribed. Don't skip doses. If you miss a dose of your medicine, take it as soon as you remember -- unless it's almost time for your next dose. In that case, just wait and take your next dose at the normal time. Don't take a double dose. If you are unsure, call your care team.\n\n- Subscribe to a low sodium diet.\n\n- Take in no more than 7 cups of liquid per day. During warm weather, restricting sodium intake is key since you need to increase liquid intake to stay hydrated.
            ",
            'Afib' => "- If you are on a blood thinner, great! Please let us know any bleeding (e.g., gums)\n\n- If you are not on a blood thinner, we would recommend bringing up blood thinners at you next doctor’s visit, if not already discussed. \n\n- Tell your care team about any changes in the medication you take, including prescription and over-the-counter as well as any supplements. They interfere with some medications given for atrial fibrillation.\n\n- Limit the consumption of alcohol. Sometimes alcohol needs to be avoided to better treat atrial fibrillation. If you are taking blood-thinner medications, alcohol may interfere with them by increasing their effect.\n\n- Never take stimulants such as amphetamines or cocaine. These drugs can speed up your heart rate and trigger atrial fibrillation.
            ",
            'Kidney Disease' => "- Check your blood pressure regularly and report blood pressure over 140/90 to your MD right away\n\n- Eat 1500 mg or less of sodium daily\n\n- Eat less than 1500 milligrams to 2700 milligrams of potassium daily.\n\n- As instructed, limit protein in your diet.\n\n- Move around and bend your legs to avoid getting blood clots when you rest for a long period of time.\n\n- Stay on schedule with your lab works.
            ",
            'Diabetes' => "- Measure your Blood Sugar frequently as indicated in your glucose monitoring kit (or insulin RX, if applicable)\n\n- Make sure you have plenty of lances, test strips, etc., and a working glucometer (we can help you obtain this if needed!)\n\n- Take your medications\n\n- Have a yearly eye exam\n\n- Check your feet regularly for dryness, cracking, calluses and sores\n\n- Keep all your appointments\n\n- Get all tests as recommended
            ",
            'Diabetes Type 1' => "- Measure your Blood Sugar frequently as indicated in your glucose monitoring kit (or insulin RX, if applicable)\n\n- Make sure you have plenty of lances, test strips, etc., and a working glucometer (we can help you obtain this if needed!)\n\n- Take your medications\n\n- Have a yearly eye exam\n\n- Check your feet regularly for dryness, cracking, calluses and sores\n\n- Keep all your appointments\n\n- Get all tests as recommended
            ",
            'Diabetes Type 2' => "- Measure your Blood Sugar frequently as indicated in your glucose monitoring kit (or insulin RX, if applicable)\n\n- Make sure you have plenty of lances, test strips, etc., and a working glucometer (we can help you obtain this if needed!)\n\n- Take your medications\n\n- Have a yearly eye exam\n\n- Check your feet regularly for dryness, cracking, calluses and sores\n\n- Keep all your appointments\n\n- Get all tests as recommended
            ",
            'COPD' => "- Avoid tobacco smoke—including secondhand smoke— it is unhealthy for everyone, especially people with asthma and/or COPD.\n\n- Limit exposure to common allergens (dust mites, pollen, mold and animal dander) and protect yourself against pollution\n\n- Keep active to build up strength\n\n- Build your strength even when you are sitting, by using small weights or rubber tubing to make your arms and shoulders stronger, standing up and sit down, or holding your legs straight out in front of you, then put them down. Repeat these movements several times.\n\n- Know how and when to take your asthma/COPD drugs.\n\n- Know the difference between your maintenance vs. rescue medications. If you need help with this, our nurses can assist you.\n\n- Take your quick-relief inhaler when you feel short of breath and need help fast.\n\n- Take your long-term inhaler every day.\n\n- Eat smaller meals more often -- 6 smaller meals a day. It might be easier to breathe when your stomach is not full
            ",
            'Asthma' => "- Avoid tobacco smoke—including secondhand smoke— it is unhealthy for everyone, especially people with asthma and/or COPD.\n\n- Limit exposure to common allergens (dust mites, pollen, mold and animal dander) and protect yourself against pollution\n\n- Keep active to build up strength\n\n- Build your strength even when you are sitting, by using small weights or rubber tubing to make your arms and shoulders stronger, standing up and sit down, or holding your legs straight out in front of you, then put them down. Repeat these movements several times.\n\n- Know how and when to take your asthma/COPD drugs.\n\n- Know the difference between your maintenance vs. rescue medications. If you need help with this, our nurses can assist you.\n\n- Take your quick-relief inhaler when you feel short of breath and need help fast.\n\n- Take your long-term inhaler every day.\n\n- Eat smaller meals more often -- 6 smaller meals a day. It might be easier to breathe when your stomach is not full
            ",
            'Smoking' => "- Each cigarette you smoke damages your lungs, your blood vessels, and cells throughout your body. Even occasional smoking is harmful.\n\n- Quitting is hard. There are nicotine replacements such as nicorette to help you transition. Ask your care coach or doctor for more information! It takes commitment and effort to quit smoking.\n\n- Take quitting one day at a time, even one minute at a time—whatever you need to succeed.\n\n- Many people worry about gaining weight when they quit smoking.  Focus on stopping smoking, which is much worse for your health than gaining a few pounds. \n\n- When you have the urge to smoke, do something active instead. Walk around the block. Head to the gym. Garden.  Do housework. Walk the dog. Play with the kids.\n\n- Ask friends and family members who are smokers not to smoke around you, and try to avoid situations that remind you of smoking.\n\n- Let people help you quit!  Call 1-877-44U-QUIT (1-877-448-7848).  The National Cancer Institute's trained counselors are available to provide information and help with quitting in English or Spanish, Monday through Friday, 8:00 a.m. to 8:00 p.m. Eastern Time.  You can also call your state quitline: 1-800-QUIT-NOW (1-800-784-8669) (hours vary).
            ",
            'Obesity' => "- Diet can be most impactful, especially if you switch to vegetables and fish/meats instead of breads, sweets, sodas and pastas:\n\n- Avoid carbohydrates such as all breads, sugars/sweets/candy, rice, pasta and potatoes. Some great substitutes are crushed cauliflower, lentils and zucchini pasta.\n\n- Avoid liquid calories like sodas (Coke, Sprite etc.) and other sweetened drinks (Arizona tea, Snapple tea, Honest etc.)\n\n- Be sure to drink plenty of water instead. It keeps you hydrated while reducing hunger. Unsweetened tea is usually fine.\n\n- Avoid high-calorie ingredients like nut oil, seed oil and vegetable oil. However, a little bit of olive oil in your cooking should be fine ☺\n\n- One day per week, cheat a little. You just have to be good until that day! ☺ but reduce alcohol intake (can be high-calorie!)\n\n- Exercise also helps your heart and joints, but prioritize diet:\n\n- Join a walking club at a mall, a dance class or other group activity\n\n- Work in your garden and walk or ride a bike in your neighborhood\n\n- Walk your dog. If you don’t have a dog, pretend you do ☺\n\n- In general, most of us can develop a long-term exercise plan for free\n\n- Please consult with a registered dietitian or physician if you have further questions.",
        ];
    }

    /**
     * Create default instructions for cpm problems.
     */
    public function run()
    {
        foreach ($this->behavioralInstructions() as $name => $detail) {
            $problems = CpmProblem::whereIn('name', $detail['problems'])->with('instructable')->get();
            if ($problems->count()) {
                $instruction = CpmInstruction::firstOrNew(['name' => $detail['instruction'], 'is_default' => 1]);

                $instruction->save();

                $problems->map(function ($problem) use ($instruction) {
                    $instructable = $problem->instructable()->firstOrNew([
                        'cpm_instruction_id' => $instruction->id,
                        'instructable_type'  => CpmProblem::class,
                    ]);

                    $instructable->save();

                    return $instructable;
                });
            }
        }

        //CpmInstructable::where([])->delete();
        foreach ($this->instructions() as $name => $body) {
            $problem = CpmProblem::where(['name' => $name])->with('instructable')->first();
            if ($problem) {
                $instruction = $problem->cpmInstructions()->firstOrNew([]);

                $instruction->name       = $body;
                $instruction->is_default = 1;

                $instruction->save();

                $instructable = $problem->instructable()->firstOrNew([
                    'cpm_instruction_id' => $instruction->id,
                    'instructable_type'  => CpmProblem::class,
                ]);

                $instructable->save();
            }
        }
    }
}
