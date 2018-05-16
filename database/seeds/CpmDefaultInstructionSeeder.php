<?php

use Illuminate\Database\Seeder;
use App\Models\CPM\CpmProblem;
use App\Models\CPM\CpmInstruction;
use App\Models\CPM\CpmInstructable;

class CpmDefaultInstructionSeeder extends Seeder
{   
    /**
     * Create default instructions for cpm problems
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->behavioralInstructions() as $name => $detail) {
            $problems = CpmProblem::whereIn('name', $detail['problems'])->with('instructable')->get();
            if ($problems->count()) {
                $instruction = CpmInstruction::firstOrNew([ 'name' => $detail['instruction'], 'is_default' => 1 ]);

                $instruction->save();

                $problems->map(function ($problem) use ($instruction) {
                    $instructable = $problem->instructable()->firstOrNew([
                        'cpm_instruction_id' => $instruction->id,
                        'instructable_type' => CpmProblem::class
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

                $instruction->name = $body;
                $instruction->is_default = 1;

                $instruction->save();

                $instructable = $problem->instructable()->firstOrNew([
                    'cpm_instruction_id' => $instruction->id,
                    'instructable_type' => CpmProblem::class
                ]);

                $instructable->save();
            }
        }
    }

    public function instructions () {
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
            'Depression' => "- Take your medication as prescribed.\n\n- Contact your care team if anything changes.\n\n- Our RN will check in with a couple questions every month.\n\n- Watch for medication side effects like changing thought patterns or upset stomach.\n\n- Some medications have recommended lab tests which your doctor will prescribe; discuss this with our telephone RNs to coordinate if needed.\n\n- Make sure to do things that you enjoy (Gardening, going to a move, taking a walk, etc…)\n\n- Reward yourself for small successes.\n\n- Take care of your body. Eat a healthy diet and establish an exercise plan of at least three times a week. Even moderate exercise will help you feel better.\n\n- Avoid alcohol which can make depression worse.\n\n- For additional information, call the National Depressive Assoc. at (800) 826-3632
            ",
            'Dementia' => "- Keep a list of important phone numbers next to every phone.\n\n- Have clocks and calendars around the house so you stay aware of the date and time.\n\n- Label important items.\n\n- Develop habits and routines that are easy to follow.\n\n- Plan activities that improve your thinking, such as puzzles, games, baking, or indoor gardening.\n\n- Keep pictures of friends and family around the house.\n\n- Keep good sleep hygiene: Avoid beeping or noises at night time. Avoid fluids before bed time and don’t watch TV in bed.\n\n- Try to keep shades up during the day and down during the night.\n\n- Exercising 30 minutes three times a week, or more, will improve health.\n\n- Have someone nearby for any tasks that may have a risk of injury.\n\n- For additional information, call the Alzheimer’s Foundation of America at (866) 232-8484
            ",
            'Smoking' => "- Each cigarette you smoke damages your lungs, your blood vessels, and cells throughout your body. Even occasional smoking is harmful.\n\n- Quitting is hard. There are nicotine replacements such as nicorette to help you transition. Ask your care coach or doctor for more information! It takes commitment and effort to quit smoking.\n\n- Take quitting one day at a time, even one minute at a time—whatever you need to succeed.\n\n- Many people worry about gaining weight when they quit smoking.  Focus on stopping smoking, which is much worse for your health than gaining a few pounds. \n\n- When you have the urge to smoke, do something active instead. Walk around the block. Head to the gym. Garden.  Do housework. Walk the dog. Play with the kids.\n\n- Ask friends and family members who are smokers not to smoke around you, and try to avoid situations that remind you of smoking.\n\n- Let people help you quit!  Call 1-877-44U-QUIT (1-877-448-7848).  The National Cancer Institute's trained counselors are available to provide information and help with quitting in English or Spanish, Monday through Friday, 8:00 a.m. to 8:00 p.m. Eastern Time.  You can also call your state quitline: 1-800-QUIT-NOW (1-800-784-8669) (hours vary).
            "
        ];
    }

    public function behavioralInstructions() {
        $instructions = array($this->instructions());
        return [
            'Depression' => [
                'problems' => [
                    'Adjustment disorder with depressed mood',
                    'Adjustment disorder with mixed anxiety and depressed mood',
                    'Major depressive disorder, recurrent severe without psychotic features',
                    'Major depressive disorder, recurrent, in full remission',
                    'Major depressive disorder, recurrent, in partial remission',
                    'Major depressive disorder, recurrent, in remission, unspecified',
                    'Major depressive disorder, recurrent, mild',
                    'Major depressive disorder, recurrent, moderate',
                    'Major depressive disorder, recurrent, severe with psychotic symptoms',
                    'Major depressive disorder, recurrent, unspecified',
                    'Major depressive disorder, single episode, in full remission',
                    'Major depressive disorder, single episode, in partial remission',
                    'Major depressive disorder, single episode, mild',
                    'Major depressive disorder, single episode, moderate',
                    'Major depressive disorder, single episode, severe with psychotic features',
                    'Major depressive disorder, single episode, severe without psychotic features',
                    'Major depressive disorder, single episode, unspecified',
                    'Other depressive episodes',
                    'Other recurrent depressive disorders',
                    'Other specified depressive episodes'
                ],
                'instruction' => "- Take your medication as prescribed.\n\n- Contact your care team if anything changes.\n\n- Our RN will check in with a couple questions every month.\n\n- Watch for medication side effects like changing thought patterns or upset stomach.\n\n- Some medications have recommended lab tests which your doctor will prescribe; discuss this with our telephone RNs to coordinate if needed.\n\n- Make sure to do things that you enjoy (Gardening, going to a move, taking a walk, etc…)\n\n- Reward yourself for small successes.\n\n- Take care of your body. Eat a healthy diet and establish an exercise plan of at least three times a week. Even moderate exercise will help you feel better.\n\n- Avoid alcohol which can make depression worse.\n\n- For additional information, call the National Depressive Assoc. at (800) 826-3632
                "
            ],
            'Dementia' => [
                'problems' => [
                    'Amnestic disorder due to known physiological condition',
                    'Dementia in other diseases classified elsewhere with behavioral disturbance',
                    'Dementia in other diseases classified elsewhere without behavioral disturbance',
                    'Dissociative amnesia',
                    'Unspecified dementia with behavioral disturbance',
                    'Unspecified dementia without behavioral disturbance',
                    'Vascular dementia with behavioral disturbance',
                    'Vascular dementia without behavioral disturbance'
                ],
                'instruction' =>    "- Keep a list of important phone numbers next to every phone.\n\n- Have clocks and calendars around the house so you stay aware of the date and time.\n\n- Label important items.\n\n- Develop habits and routines that are easy to follow.\n\n- Plan activities that improve your thinking, such as puzzles, games, baking, or indoor gardening.\n\n- Keep pictures of friends and family around the house.\n\n- Keep good sleep hygiene: Avoid beeping or noises at night time. Avoid fluids before bed time and don’t watch TV in bed.\n\n- Try to keep shades up during the day and down during the night.\n\n- Exercising 30 minutes three times a week, or more, will improve health.\n\n- Have someone nearby for any tasks that may have a risk of injury.\n\n- For additional information, call the Alzheimer’s Foundation of America at (866) 232-8484
                "
            ],
            'Post-traumatic stress' => [
                'problems' => [
                    'Post-traumatic stress disorder, acute',
                    'Post-traumatic stress disorder, chronic',
                    'Post-traumatic stress disorder, unspecified'
                ],
                'instruction' => "- Join a Peer Support / Self-Help group. They are helpful. To find a group, visit https://www.nami.org/Find-Your-Local-NAMI, and select your state on the right side.\n\n- Get adequate rest at night. Keeping good sleep hygiene can help: Avoid beeping or noises at night time. Avoid fluids and screens like TVs, mobile phones/tablets before and during bed time.\n\n- Try to reduce or avoid caffeine and nicotine, which can worsen anxiety.\n\n- Get physical exercise multiple times per week.\n\n- When you feel anxious, take a brisk walk or jump into a hobby to re-focus.\n\n- Spend time with supportive people like family or friends. You don’t have to talk about your feelings, just being around loved ones helps.\n\n- Keep a diary or journal about your anxiety and emotions. Consider the causes and possible solutions.
                "
            ],
            'Anxiety and Stress' => [
                'problems' => [
                    'Acute stress reaction',
                    'Adjustment disorder with anxiety',
                    'Agoraphobia with panic disorder',
                    'Anxiety disorder due to known physiological condition',
                    'Anxiety disorder, unspecified',
                    'Generalized anxiety disorder',
                    'Other mixed anxiety disorders',
                    'Other phobic anxiety disorders',
                    'Other specified anxiety disorders',
                    'Panic Disorder (episodic paroxysmal anxiety)',
                    'Phobic anxiety disorder, unspecified',
                    'Separation anxiety disorder of childhood',
                    'Social phobia, generalized',
                    'Social phobia, unspecified',
                    'State of emotional shock and stress, unspecified'
                ],
                'instruction' => "- Try to identify the areas of stress in your life: they may not be obvious, like:\n\n\t- Overload: feeling you have too many responsibilities and cannot take care of everything at once\n\t- Helplessness: feeling that you cannot solve your problems\n\t- Daily hassles of life: vehicle trouble, traffic, bills\n\t- Major life challenges: both good and bad\n\n- Eating a healthy diet reduces stress: More vegetables and less sugary or fried foods.\n\n- Exercise at least three times a week. Even moderate exercise will help you feel better. \n\n- Let us know if you have trouble breathing or chest pain, severe headaches, bad thoughts, or irregular heartbeats.
                "
            ],
            'Psychosis & Schizophrenia' => [
                'problems' => [
                    'Brief psychotic disorder',
                    'Catatonic schizophrenia',
                    'Disorganized schizophrenia',
                    'Other schizoaffective disorders',
                    'Other schizophrenia',
                    'Paranoid schizophrenia',
                    'Psychotic disorder with delusions due to known physiological condition',
                    'Psychotic disorder with hallucinations due to known physiological condition',
                    'Residual schizophrenia',
                    'Schizoaffective disorder, bipolar type',
                    'Schizoaffective disorder, depressive type',
                    'Schizoaffective disorder, unspecified',
                    'Schizoid personality disorder',
                    'Schizophrenia, unspecified',
                    'Schizophreniform disorder',
                    'Schizotypal disorder',
                    'Shared psychotic disorder',
                    'Undifferentiated schizophrenia',
                    'Unspecified psychosis not due to a substance or known physiological condition'
                ],
                'instruction' => "- Avoid drugs that are known to cause you trouble.\n\n- Do not stop your medications on your own.\n\n- Avoid narcotics and alcohol.\n\n- Focus on and schedule meaningful activities.\n\n- Spend time and gain support from staff, family and other supportive people\n\n- Eating a healthy diet helps: More vegetables and less sugary or fried foods.\n\n- Exercise at least three times a week. Even moderate exercise will help.\n\n- Join a Peer Support / Self-Help group. They are helpful. To find a group, visit https://www.nami.org/Find-Your-Local-NAMI, and select your state on the right side.
                "
            ],
            'Substance Abuse' => [
                'problems' => [
                    'Cannabis abuse with cannabis-induced anxiety disorder',
                    'Cannabis abuse with intoxication delirium',
                    'Cannabis abuse with intoxication with perceptual disturbance',
                    'Cannabis abuse with other cannabis-induced disorder',
                    'Cannabis abuse with psychotic disorder with delusions',
                    'Cannabis abuse with psychotic disorder with hallucinations',
                    'Cannabis abuse with psychotic disorder, unspecified',
                    'Cannabis abuse with unspecified cannabis-induced disorder',
                    'Cannabis dependence with cannabis-induced anxiety disorder',
                    'Cannabis dependence with intoxication delirium',
                    'Cannabis dependence with intoxication with perceptual disturbance',
                    'Cannabis dependence with other cannabis-induced disorder',
                    'Cannabis dependence with psychotic disorder with delusions',
                    'Cannabis dependence with psychotic disorder with hallucinations',
                    'Cannabis dependence with psychotic disorder, unspecified',
                    'Cannabis dependence with unspecified cannabis-induced disorder',
                    'Cannabis use, unspecified with anxiety disorder',
                    'Cannabis use, unspecified with intoxication delirium',
                    'Cannabis use, unspecified with intoxication with perceptual disturbance',
                    'Cannabis use, unspecified with other cannabis-induced disorder',
                    'Cannabis use, unspecified with psychotic disorder with delusions',
                    'Cannabis use, unspecified with psychotic disorder with hallucinations',
                    'Cannabis use, unspecified with psychotic disorder, unspecified',
                    'Cocaine abuse with cocaine-induced anxiety disorder',
                    'Cocaine abuse with cocaine-induced mood disorder',
                    'Cocaine abuse with cocaine-induced psychotic disorder with delusions',
                    'Cocaine abuse with cocaine-induced psychotic disorder with hallucinations',
                    'Cocaine abuse with cocaine-induced psychotic disorder, unspecified',
                    'Cocaine abuse with cocaine-induced sexual dysfunction',
                    'Cocaine abuse with cocaine-induced sleep disorder',
                    'Cocaine abuse with intoxication with delirium',
                    'Cocaine abuse with intoxication with perceptual disturbance',
                    'Cocaine abuse with other cocaine-induced disorder',
                    'Cocaine abuse with unspecified cocaine-induced disorder',
                    'Cocaine dependence with cocaine-induced anxiety disorder',
                    'Cocaine dependence with cocaine-induced mood disorder',
                    'Cocaine dependence with cocaine-induced psychotic disorder with delusions',
                    'Cocaine dependence with cocaine-induced psychotic disorder with hallucinations',
                    'Cocaine dependence with cocaine-induced psychotic disorder, unspecified',
                    'Cocaine dependence with cocaine-induced sexual dysfunction',
                    'Cocaine dependence with cocaine-induced sleep disorder',
                    'Cocaine dependence with intoxication delirium',
                    'Cocaine dependence with intoxication with perceptual disturbance',
                    'Cocaine dependence with other cocaine-induced disorder',
                    'Cocaine dependence with unspecified cocaine-induced disorder',
                    'Cocaine use, unspecified with cocaine-induced anxiety disorder',
                    'Cocaine use, unspecified with cocaine-induced mood disorder',
                    'Cocaine use, unspecified with cocaine-induced psychotic disorder with delusions',
                    'Cocaine use, unspecified with cocaine-induced psychotic disorder with hallucinations',
                    'Cocaine use, unspecified with cocaine-induced psychotic disorder, unspecified',
                    'Cocaine use, unspecified with cocaine-induced sexual dysfunction',
                    'Cocaine use, unspecified with cocaine-induced sleep disorder',
                    'Cocaine use, unspecified with intoxication delirium',
                    'Cocaine use, unspecified with intoxication with perceptual disturbance',
                    'Cocaine use, unspecified with other cocaine-induced disorder',
                    'Hallucinogen abuse with hallucinogen persisting perception disorder (flashbacks)',
                    'Hallucinogen abuse with hallucinogen-induced anxiety disorder',
                    'Hallucinogen abuse with hallucinogen-induced mood disorder',
                    'Hallucinogen abuse with hallucinogen-induced psychotic disorder with delusions',
                    'Hallucinogen abuse with hallucinogen-induced psychotic disorder with hallucinations',
                    'Hallucinogen abuse with hallucinogen-induced psychotic disorder, unspecified',
                    'Hallucinogen abuse with intoxication with delirium',
                    'Hallucinogen abuse with intoxication with perceptual disturbance',
                    'Hallucinogen abuse with other hallucinogen-induced disorder',
                    'Hallucinogen abuse with unspecified hallucinogen-induced disorder',
                    'Hallucinogen dependence with hallucinogen persisting perception disorder (flashbacks)',
                    'Hallucinogen dependence with hallucinogen-induced anxiety disorder',
                    'Hallucinogen dependence with hallucinogen-induced mood disorder',
                    'Hallucinogen dependence with hallucinogen-induced psychotic disorder with delusions',
                    'Hallucinogen dependence with hallucinogen-induced psychotic disorder with hallucinations',
                    'Hallucinogen dependence with hallucinogen-induced psychotic disorder, unspecified',
                    'Hallucinogen dependence with intoxication with delirium',
                    'Hallucinogen dependence with other hallucinogen-induced disorder',
                    'Hallucinogen dependence with unspecified hallucinogen-induced disorder',
                    'Hallucinogen use, unspecified with hallucinogen persisting perception disorder (flashbacks)',
                    'Hallucinogen use, unspecified with hallucinogen-induced anxiety disorder',
                    'Hallucinogen use, unspecified with hallucinogen-induced mood disorder',
                    'Hallucinogen use, unspecified with hallucinogen-induced psychotic disorder with delusions',
                    'Hallucinogen use, unspecified with hallucinogen-induced psychotic disorder with hallucinations',
                    'Hallucinogen use, unspecified with hallucinogen-induced psychotic disorder, unspecified',
                    'Hallucinogen use, unspecified with intoxication with delirium',
                    'Hallucinogen use, unspecified with other hallucinogen-induced disorder',
                    'Inhalant abuse with inhalant-induced anxiety disorder',
                    'Inhalant abuse with inhalant-induced dementia',
                    'Inhalant abuse with inhalant-induced mood disorder',
                    'Inhalant abuse with inhalant-induced psychotic disorder with delusions',
                    'Inhalant abuse with inhalant-induced psychotic disorder with hallucinations',
                    'Inhalant abuse with inhalant-induced psychotic disorder, unspecified',
                    'Inhalant abuse with intoxication delirium',
                    'Inhalant abuse with other inhalant-induced disorder',
                    'Inhalant abuse with unspecified inhalant-induced disorder',
                    'Inhalant dependence with inhalant-induced anxiety disorder',
                    'Inhalant dependence with inhalant-induced dementia',
                    'Inhalant dependence with inhalant-induced mood disorder',
                    'Inhalant dependence with inhalant-induced psychotic disorder with delusions',
                    'Inhalant dependence with inhalant-induced psychotic disorder with hallucinations',
                    'Inhalant dependence with inhalant-induced psychotic disorder, unspecified',
                    'Inhalant dependence with intoxication delirium',
                    'Inhalant use, unspecified with inhalant-induced anxiety disorder',
                    'Inhalant use, unspecified with inhalant-induced mood disorder',
                    'Inhalant use, unspecified with inhalant-induced persisting dementia',
                    'Inhalant use, unspecified with inhalant-induced psychotic disorder with delusions',
                    'Inhalant use, unspecified with inhalant-induced psychotic disorder with hallucinations',
                    'Inhalant use, unspecified with inhalant-induced psychotic disorder, unspecified',
                    'Inhalant use, unspecified with intoxication with delirium',
                    'Opioid abuse with intoxication delirium',
                    'Opioid abuse with intoxication with perceptual disturbance',
                    'Opioid abuse with opioid-induced mood disorder',
                    'Opioid abuse with opioid-induced psychotic disorder with delusions',
                    'Opioid abuse with opioid-induced psychotic disorder with hallucinations',
                    'Opioid abuse with opioid-induced psychotic disorder, unspecified',
                    'Opioid abuse with opioid-induced sexual dysfunction',
                    'Opioid abuse with opioid-induced sleep disorder',
                    'Opioid abuse with other opioid-induced disorder',
                    'Opioid abuse with unspecified opioid-induced disorder',
                    'Opioid dependence with intoxication delirium',
                    'Opioid dependence with opioid-induced mood disorder',
                    'Opioid dependence with opioid-induced psychotic disorder with delusions',
                    'Opioid dependence with opioid-induced psychotic disorder with hallucinations',
                    'Opioid dependence with opioid-induced psychotic disorder, unspecified',
                    'Opioid dependence with opioid-induced sexual dysfunction',
                    'Opioid dependence with opioid-induced sleep disorder',
                    'Opioid dependence with other opioid-induced disorder',
                    'Opioid dependence with unspecified opioid-induced disorder',
                    'Opioid use, unspecified with intoxication delirium',
                    'Opioid use, unspecified with intoxication with perceptual disturbance',
                    'Opioid use, unspecified with opioid-induced mood disorder',
                    'Opioid use, unspecified with opioid-induced psychotic disorder with delusions',
                    'Opioid use, unspecified with opioid-induced psychotic disorder with hallucinations',
                    'Opioid use, unspecified with opioid-induced psychotic disorder, unspecified',
                    'Opioid use, unspecified with opioid-induced sexual dysfunction',
                    'Opioid use, unspecified with opioid-induced sleep disorder',
                    'Opioid use, unspecified with other opioid-induced disorder',
                    'Other psychoactive substance abuse with intoxication delirium',
                    'Other psychoactive substance abuse with intoxication with perceptual disturbances',
                    'Other psychoactive substance abuse with other psychoactive substance-induced disorder',
                    'Other psychoactive substance abuse with psychoactive substance-induced anxiety disorder',
                    'Other psychoactive substance abuse with psychoactive substance-induced mood disorder',
                    'Other psychoactive substance abuse with psychoactive substance-induced persisting amnestic disorder',
                    'Other psychoactive substance abuse with psychoactive substance-induced persisting dementia',
                    'Other psychoactive substance abuse with psychoactive substance-induced psychotic disorder with delus',
                    'Other psychoactive substance abuse with psychoactive substance-induced psychotic disorder with hallu',
                    'Other psychoactive substance abuse with psychoactive substance-induced psychotic disorder, unspecifi',
                    'Other psychoactive substance abuse with psychoactive substance-induced sexual dysfunction',
                    'Other psychoactive substance abuse with psychoactive substance-induced sleep disorder',
                    'Other psychoactive substance abuse with unspecified psychoactive substance-induced disorder',
                    'Other psychoactive substance dependence with intoxication delirium',
                    'Other psychoactive substance dependence with intoxication with perceptual disturbance',
                    'Other psychoactive substance dependence with other psychoactive substance-induced disorder',
                    'Other psychoactive substance dependence with psychoactive substance-induced anxiety disorder',
                    'Other psychoactive substance dependence with psychoactive substance-induced mood disorder',
                    'Other psychoactive substance dependence with psychoactive substance-induced persisting amnestic diso',
                    'Other psychoactive substance dependence with psychoactive substance-induced persisting dementia',
                    'Other psychoactive substance dependence with psychoactive substance-induced psychotic disorder with',
                    'Other psychoactive substance dependence with psychoactive substance-induced psychotic disorder with',
                    'Other psychoactive substance dependence with psychoactive substance-induced psychotic disorder, unsp',
                    'Other psychoactive substance dependence with psychoactive substance-induced sexual dysfunction',
                    'Other psychoactive substance dependence with psychoactive substance-induced sleep disorder',
                    'Other psychoactive substance dependence with unspecified psychoactive substance-induced disorder',
                    'Other psychoactive substance dependence with withdrawal delirium',
                    'Other psychoactive substance dependence with withdrawal with perceptual disturbance',
                    'Other psychoactive substance dependence with withdrawal, unspecified',
                    'Other psychoactive substance use, unspecified with intoxication with delirium',
                    'Other psychoactive substance use, unspecified with intoxication with perceptual disturbance',
                    'Other psychoactive substance use, unspecified with other psychoactive substance-induced disorder',
                    'Other psychoactive substance use, unspecified with psychoactive substanceinduced anxiety disorder',
                    'Other psychoactive substance use, unspecified with psychoactive substanceinduced mood disorder',
                    'Other psychoactive substance use, unspecified with psychoactive substanceinduced persisting amnesti',
                    'Other psychoactive substance use, unspecified with psychoactive substanceinduced persisting dementi',
                    'Other psychoactive substance use, unspecified with psychoactive substanceinduced psychotic disorder',
                    'Other psychoactive substance use, unspecified with psychoactive substanceinduced psychotic disorder',
                    'Other psychoactive substance use, unspecified with psychoactive substanceinduced psychotic disorder',
                    'Other psychoactive substance use, unspecified with psychoactive substanceinduced sexual dysfunction',
                    'Other psychoactive substance use, unspecified with psychoactive substanceinduced sleep disorder',
                    'Other psychoactive substance use, unspecified with withdrawal delirium',
                    'Other psychoactive substance use, unspecified with withdrawal with perceptual disturbance',
                    'Other stimulant abuse with intoxication delirium',
                    'Other stimulant abuse with intoxication with perceptual disturbance',
                    'Other stimulant abuse with other stimulant-induced disorder',
                    'Other stimulant abuse with stimulant-induced anxiety disorder',
                    'Other stimulant abuse with stimulant-induced mood disorder',
                    'Other stimulant abuse with stimulant-induced psychotic disorder with delusions',
                    'Other stimulant abuse with stimulant-induced psychotic disorder with hallucinations',
                    'Other stimulant abuse with stimulant-induced psychotic disorder, unspecified',
                    'Other stimulant abuse with stimulant-induced sexual dysfunction',
                    'Other stimulant abuse with stimulant-induced sleep disorder',
                    'Other stimulant abuse with unspecified stimulant-induced disorder',
                    'Other stimulant dependence with intoxication delirium',
                    'Other stimulant dependence with intoxication with perceptual disturbance',
                    'Other stimulant dependence with other stimulant-induced disorder',
                    'Other stimulant dependence with stimulant-induced anxiety disorder',
                    'Other stimulant dependence with stimulant-induced mood disorder',
                    'Other stimulant dependence with stimulant-induced psychotic disorder with delusions',
                    'Other stimulant dependence with stimulant-induced psychotic disorder with hallucinations',
                    'Other stimulant dependence with stimulant-induced psychotic disorder, unspecified',
                    'Other stimulant dependence with stimulant-induced sexual dysfunction',
                    'Other stimulant dependence with stimulant-induced sleep disorder',
                    'Other stimulant dependence with unspecified stimulant-induced disorder',
                    'Other stimulant use, unspecified with intoxication delirium',
                    'Other stimulant use, unspecified with intoxication with perceptual disturbance',
                    'Other stimulant use, unspecified with other stimulant-induced disorder',
                    'Other stimulant use, unspecified with stimulant-induced anxiety disorder',
                    'Other stimulant use, unspecified with stimulant-induced mood disorder',
                    'Other stimulant use, unspecified with stimulant-induced psychotic disorder with delusions',
                    'Other stimulant use, unspecified with stimulant-induced psychotic disorder with hallucinations',
                    'Other stimulant use, unspecified with stimulant-induced psychotic disorder,unspecified',
                    'Other stimulant use, unspecified with stimulant-induced sexual dysfunction',
                    'Other stimulant use, unspecified with stimulant-induced sleep disorder',
                    'Panic disorder ºepisodic paroxysmal anxiety» without agoraphobia',
                    'Sedative, hypnotic or anxiolytic abuse with intoxication delirium',
                    'Sedative, hypnotic or anxiolytic abuse with other sedative, hypnotic or anxiolytic-induced disorder',
                    'Sedative, hypnotic or anxiolytic abuse with sedative, hypnotic or anxiolytic-induced anxiety disorder',
                    'Sedative, hypnotic or anxiolytic abuse with sedative, hypnotic or anxiolytic-induced mood disorder',
                    'Sedative, hypnotic or anxiolytic abuse with sedative, hypnotic or anxiolytic-induced psychotic disor',
                    'Sedative, hypnotic or anxiolytic abuse with sedative, hypnotic or anxiolytic-induced psychotic disor',
                    'Sedative, hypnotic or anxiolytic abuse with sedative, hypnotic or anxiolytic-induced psychotic disor',
                    'Sedative, hypnotic or anxiolytic abuse with sedative, hypnotic or anxiolytic-induced sexual dysfunct',
                    'Sedative, hypnotic or anxiolytic abuse with sedative, hypnotic or anxiolytic-induced sleep disorder',
                    'Sedative, hypnotic or anxiolytic abuse with unspecified sedative, hypnotic or anxiolytic-induced dis',
                    'Sedative, hypnotic or anxiolytic dependence with intoxication delirium',
                    'Sedative, hypnotic or anxiolytic dependence with other sedative, hypnotic or anxiolytic-induced diso',
                    'Sedative, hypnotic or anxiolytic dependence with sedative, hypnotic or anxiolytic-induced anxiety di',
                    'Sedative, hypnotic or anxiolytic dependence with sedative, hypnotic or anxiolytic-induced mood disor',
                    'Sedative, hypnotic or anxiolytic dependence with sedative, hypnotic or anxiolytic-induced persisting',
                    'Sedative, hypnotic or anxiolytic dependence with sedative, hypnotic or anxiolytic-induced persisting',
                    'Sedative, hypnotic or anxiolytic dependence with sedative, hypnotic or anxiolytic-induced psychotic',
                    'Sedative, hypnotic or anxiolytic dependence with sedative, hypnotic or anxiolytic-induced psychotic',
                    'Sedative, hypnotic or anxiolytic dependence with sedative, hypnotic or anxiolytic-induced psychotic',
                    'Sedative, hypnotic or anxiolytic dependence with sedative, hypnotic or anxiolytic-induced sexual dys',
                    'Sedative, hypnotic or anxiolytic dependence with sedative, hypnotic or anxiolytic-induced sleep diso',
                    'Sedative, hypnotic or anxiolytic dependence with unspecified sedative, hypnotic or anxiolytic-induced',
                    'Sedative, hypnotic or anxiolytic dependence with withdrawal delirium',
                    'Sedative, hypnotic or anxiolytic dependence with withdrawal with perceptual disturbance',
                    'Sedative, hypnotic or anxiolytic dependence with withdrawal, unspecified',
                    'Sedative, hypnotic or anxiolytic use, unspecified with intoxication delirium',
                    'Sedative, hypnotic or anxiolytic use, unspecified with other sedative, hypnotic or anxiolytic-induce',
                    'Sedative, hypnotic or anxiolytic use, unspecified with sedative, hypnotic or anxiolytic-induced anxi',
                    'Sedative, hypnotic or anxiolytic use, unspecified with sedative, hypnotic or anxiolytic-induced mood',
                    'Sedative, hypnotic or anxiolytic use, unspecified with sedative, hypnotic or anxiolytic-induced pers',
                    'Sedative, hypnotic or anxiolytic use, unspecified with sedative, hypnotic or anxiolytic-induced pers',
                    'Sedative, hypnotic or anxiolytic use, unspecified with sedative, hypnotic or anxiolytic-induced psyc',
                    'Sedative, hypnotic or anxiolytic use, unspecified with sedative, hypnotic or anxiolytic-induced psyc',
                    'Sedative, hypnotic or anxiolytic use, unspecified with sedative, hypnotic or anxiolytic-induced psyc',
                    'Sedative, hypnotic or anxiolytic use, unspecified with sedative, hypnotic or anxiolytic-induced sexu',
                    'Sedative, hypnotic or anxiolytic use, unspecified with sedative, hypnotic or anxiolytic-induced slee',
                    'Sedative, hypnotic or anxiolytic use, unspecified with withdrawal delirium',
                    'Sedative, hypnotic or anxiolytic use, unspecified with withdrawal with perceptual disturbances',
                ],
                'instruction' => "- Develop alternative behaviours to drug use, such as exercise and scheduling meaningful activities you enjoy.\n\n- Eating a healthy diet helps: More vegetables and less sugary or fried foods.\n\n- Exercise at least three times a week. Even moderate exercise will help you feel better.\n\n- Join a Peer Support / Self-Help group. They are helpful. To find a group, visit https://www.nami.org/Find-Your-Local-NAMI, and select your state on the right side.\n\n- Help is just a phone call away: 1-800-662-HELP
                "
            ],
            'Alcoholism' => [
                'problems' => [
                    'Alcohol abuse with alcohol-induced anxiety disorder',
                    'Alcohol abuse with alcohol-induced mood disorder',
                    'Alcohol abuse with alcohol-induced psychotic disorder with delusions',
                    'Alcohol abuse with alcohol-induced psychotic disorder with hallucinations',
                    'Alcohol abuse with alcohol-induced psychotic disorder, unspecified',
                    'Alcohol abuse with alcohol-induced sexual dysfunction',
                    'Alcohol abuse with alcohol-induced sleep disorder',
                    'Alcohol abuse with intoxication delirium',
                    'Alcohol abuse with other alcohol-induced disorder',
                    'Alcohol abuse with unspecified alcohol-induced disorder',
                    'Alcohol dependence with alcohol-induced anxiety disorder',
                    'Alcohol dependence with alcohol-induced mood disorder',
                    'Alcohol dependence with alcohol-induced persisting amnestic disorder',
                    'Alcohol dependence with alcohol-induced persisting dementia',
                    'Alcohol dependence with alcohol-induced psychotic disorder with delusions',
                    'Alcohol dependence with alcohol-induced psychotic disorder with hallucinations',
                    'Alcohol dependence with alcohol-induced psychotic disorder, unspecified',
                    'Alcohol dependence with alcohol-induced sexual dysfunction',
                    'Alcohol dependence with alcohol-induced sleep disorder',
                    'Alcohol dependence with intoxication delirium',
                    'Alcohol dependence with other alcohol-induced disorder',
                    'Alcohol dependence with unspecified alcohol-induced disorder',
                    'Alcohol dependence with withdrawal delirium',
                    'Alcohol dependence with withdrawal with perceptual disturbance',
                    'Alcohol dependence with withdrawal, unspecified',
                    'Alcohol use, unspecified with alcohol-induced anxiety disorder',
                    'Alcohol use, unspecified with alcohol-induced mood disorder',
                    'Alcohol use, unspecified with alcohol-induced persisting amnestic disorder',
                    'Alcohol use, unspecified with alcohol-induced persisting dementia',
                    'Alcohol use, unspecified with alcohol-induced psychotic disorder with delusions',
                    'Alcohol use, unspecified with alcohol-induced psychotic disorder with hallucinations',
                    'Alcohol use, unspecified with alcohol-induced psychotic disorder, unspecified',
                    'Alcohol use, unspecified with alcohol-induced sexual dysfunction',
                    'Alcohol use, unspecified with alcohol-induced sleep disorder',
                    'Alcohol use, unspecified with intoxication delirium',
                    'Alcohol use, unspecified with other alcohol-induced disorder'
                ],
                'instruction' => "- Sobriety is achievable.\n\n- Develop alternative behaviours to alcohol use, such as exercise and scheduling meaningful activities you enjoy.\n\n- Eating a healthy diet helps: More vegetables and less sugary or fried foods.\n\n- Exercise at least three times a week. Even moderate exercise will help you feel better.\n\n- Join a Peer Support / Self-Help group. They are helpful. To find a group, visit https://www.nami.org/Find-Your-Local-NAMI, and select your state on the right side.\n\n- Seek immediate medical assistance if you have abdominal pain, fever over 100.5, repeated vomiting or vomiting blood.
                "
            ],
            'Bipolar' => [
                'problems' => [
                    'Bipolar II disorder',
                    'Bipolar disorder, current episode depressed, mild',
                    'Bipolar disorder, current episode depressed, mild or moderate severity, unspecified',
                    'Bipolar disorder, current episode depressed, moderate',
                    'Bipolar disorder, current episode depressed, severe, with psychotic features',
                    'Bipolar disorder, current episode depressed, severe, without psychotic features',
                    'Bipolar disorder, current episode hypomanic',
                    'Bipolar disorder, current episode manic severe with psychotic features',
                    'Bipolar disorder, current episode manic without psychotic features, mild',
                    'Bipolar disorder, current episode manic without psychotic features, moderate',
                    'Bipolar disorder, current episode manic without psychotic features, severe',
                    'Bipolar disorder, current episode manic without psychotic features, unspecified',
                    'Bipolar disorder, current episode mixed, mild',
                    'Bipolar disorder, current episode mixed, moderate',
                    'Bipolar disorder, current episode mixed, severe, with psychotic features',
                    'Bipolar disorder, current episode mixed, severe, without psychotic features',
                    'Bipolar disorder, current episode mixed, unspecified',
                    'Bipolar disorder, currently in remission, most recent episode unspecified',
                    'Bipolar disorder, in full remission, most recent episode depressed',
                    'Bipolar disorder, in full remission, most recent episode hypomanic',
                    'Bipolar disorder, in full remission, most recent episode manic',
                    'Bipolar disorder, in full remission, most recent episode mixed',
                    'Bipolar disorder, in partial remission, most recent episode depressed',
                    'Bipolar disorder, in partial remission, most recent episode hypomanic',
                    'Bipolar disorder, in partial remission, most recent episode manic',
                    'Bipolar disorder, in partial remission, most recent episode mixed',
                    'Bipolar disorder, unspecified',
                    'Manic episode in full remission',
                    'Manic episode in partial remission',
                    'Manic episode without psychotic symptoms, mild',
                    'Manic episode without psychotic symptoms, moderate',
                    'Manic episode without psychotic symptoms, unspecified',
                    'Manic episode, severe with psychotic symptoms',
                    'Manic episode, severe, without psychotic symptoms',
                    'Manic episode, unspecified',
                    'Other bipolar disorder'
                ],
                'instruction' => "- Bipolar is one of most treatable conditions.\n\n- Do NOT discontinue medications when you feel better, always consult with your doctor before changing your care plan.\n\n- Eating a healthy diet helps: More vegetables and less sugary or fried foods.\n\n- Exercise at least three times a week. Even moderate exercise will help you feel better.\n\n
                "
            ]
        ];
    }
}
