<?php

use Illuminate\Database\Seeder;
use App\Models\CPM\CpmProblem;
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
            'Depression' => "- Take your medication as prescribed.\n\n- Contact your care team if anything changes.\n\n- Our nurses will check in with a couple questions every month
            ",
            'Dementia' => "- Keep a list of important phone numbers next to every phone.\n\n- Have clocks and calendars around the house so you stay aware of the date and what time it is.\n\n- Label important items.\n\n- Develop habits and routines that are easy to follow.\n\n- Plan activities that improve your thinking, such as puzzles, games, baking, or indoor gardening. \n\n- Have someone nearby for any tasks that may have a risk of injury.
            ",
            'Smoking' => "- Each cigarette you smoke damages your lungs, your blood vessels, and cells throughout your body. Even occasional smoking is harmful.\n\n- Quitting is hard. There are nicotine replacements such as nicorette to help you transition. Ask your care coach or doctor for more information! It takes commitment and effort to quit smoking.\n\n- Take quitting one day at a time, even one minute at a time—whatever you need to succeed.\n\n- Many people worry about gaining weight when they quit smoking.  Focus on stopping smoking, which is much worse for your health than gaining a few pounds. \n\n- When you have the urge to smoke, do something active instead. Walk around the block. Head to the gym. Garden.  Do housework. Walk the dog. Play with the kids.\n\n- Ask friends and family members who are smokers not to smoke around you, and try to avoid situations that remind you of smoking.\n\n- Let people help you quit!  Call 1-877-44U-QUIT (1-877-448-7848).  The National Cancer Institute's trained counselors are available to provide information and help with quitting in English or Spanish, Monday through Friday, 8:00 a.m. to 8:00 p.m. Eastern Time.  You can also call your state quitline: 1-800-QUIT-NOW (1-800-784-8669) (hours vary).
            "
        ];
    }
}
