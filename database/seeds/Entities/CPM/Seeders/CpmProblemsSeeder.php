<?php

/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 5/11/16
 * Time: 5:36 PM
 */
class CpmProblemsSeeder extends \Illuminate\Database\Seeder
{
    public function run()
    {
        //Add care_item_id to problems
        foreach (\App\Models\CPM\CpmProblem::all() as $problem) {
            $careItem = \App\CareItem::whereName($problem->care_item_name)->first();

            $cpmProblem = \App\Models\CPM\CpmProblem::updateOrCreate(['care_item_name' => $problem->care_item_name], [
                'care_item_id' => $careItem->id,
            ]);

            //get the details
            $detailsId = \App\CareItem::whereParentId($careItem->id)->whereDisplayName('Details')->first()->id;

            $instruction = \App\Models\CPM\CpmInstruction::create([
                'name' => $this->instructions[$problem->care_item_name],
            ]);

            $cpmProblem->cpmInstructions()->attach($instruction);

            $careItem->type = \App\Models\CPM\CpmProblem::class;
            $careItem->type_id = $cpmProblem->id;
            $careItem->save();
        }
        $this->command->info('Added care_item_id to problems');
    }

    public $instructions = [
        'diabetes' => '- Measure your Blood Sugar daily as agreed 

- Take your medications 

- Have a yearly eye exam 

- Check your feet regularly for dryness, cracking, calluses and sores 

- Keep all your appointments 

- Get all tests as recommended',


        'hypertension' => '- Learn to take your own blood pressure.

- Take your blood pressure medication exactly as directed. Do not skip doses. Missing doses can cause your blood pressure to get out of control.

- Avoid medications that contain heart stimulants, including over-the-counter drugs. Check for warnings about high blood pressure on the label.

- Check with your provider before taking a decongestant. Some decongestants can worsen high blood pressure.

- Cut back on salt.

- Follow the DASH (Dietary Approaches to Stop Hypertension) eating plan. This plan recommends vegetables, fruits, whole gains, and other heart healthy foods.

- Begin an exercise program. Ask your provider how to get started. The American Heart Association recommends aerobic exercise 3 to 4 times a week for an average of 40 minutes at a time, with provider approval.',


        'afib' => '- Tell your care team about any changes in the medication you take, including prescription and over-the-counter as well as any supplements. They interfere with some medications given for atrial fibrillation. 

- Limit the consumption of alcohol. Sometimes alcohol needs to be avoided to better treat atrial fibrillation. If you are taking blood-thinner medications, alcohol may interfere with them by increasing their effect. 

- Never take stimulants such as amphetamines or cocaine. These drugs can speed up your heart rate and trigger atrial fibrillation.',


        'cad' => '- Do not start or stop any medicines unless your provider tells you to. Many medicines cannot be used with blood thinners. 

- Tell your provider right away if you forget to take the medicine, or if you take too much. 

- Do not get a flu vaccine before speaking to your care team.',

        'depression' => '- Take your medication as prescribed. 

- Contact your care team if anything changes.',

        'chf' => '- Weigh yourself every day at the same time.   

- Call the care team if you have a sudden, unexpected increase in your weight. 

- Take your medicines exactly as prescribed. Do not skip doses. If you miss a dose of your medicine, take it as soon as you remember -- unless it is almost time for your next dose. In that case, just wait and take your next dose at the normal time. Do not take a double dose. If you are unsure, call your care team.',

        'high-cholesterol' => '- Take your cholesterol meds as directed.',

        'kidney-disease' => '- Eat 1500 mg or less of sodium daily 

- Eat less than 1500 milligrams to 2700 milligrams of potassium daily. 

- As instructed, limit protein in your diet. 

- Move around and bend your legs to avoid getting blood clots when you rest for a long period of time.',


        'dementia' => '- Keeping a list of important phone numbers next to every phone. 

- Having clocks and calendars around the house so you stay aware of the date and what time it is. 

- Label important items. 

- Develop habits and routines that are easy to follow. 

- Plan activities that improve your thinking, such as puzzles, games, baking, or indoor gardening.  

- Have someone nearby for any tasks that may have a risk of injury.',


        'asthmacopd' => '- Tobacco smoke -- including secondhand smoke -- is unhealthy for everyone, especially people with asthma and/or COPD. 

- Keep active to build up strength: 

- Build your strength even when you are sitting, by using small weights or rubber tubing to make your arms and shoulders stronger, standing up and sit down, or holding your legs straight out in front of you, then put them down. Repeat these movements several times. 

- Know how and when to take your COPD drugs. 

- Take your quick-relief inhaler when you feel short of breath and need help fast. 

- Take your long-term inhaler every day. 

- Eat smaller meals more often -- 6 smaller meals a day. It might be easier to breathe when your stomach is not full. Do not drink a lot of liquid before eating, or with your meals. 

- Get a flu shot every year but check with your provider first. Ask your provider if you should get a pneumococcal (pneumonia) vaccine.',

        'cf-sol-smo-10-smoking' => '- Do not smoke any cigarettes. Each cigarette you smoke damages your lungs, your blood vessels, and cells throughout your body. Even occasional smoking is harmful.

- Quitting is hard.  It takes commitment and effort to quit smoking. Nearly all smokers have some withdrawal symptoms, such as bad moods and wanting to smoke.

- Take quitting one day at a time, even one minute at a time -- whatever you need to succeed.

- Many people worry about gaining weight when they quit smoking. Focus on stopping smoking, which is much worse for your health than gaining a few pounds.

- When you have the urge to smoke, do something active instead. Walk around the block. Head to the gym. Garden.  Do housework. Walk the dog. Play with the kids. 

- Ask friends and family members who are smokers not to smoke around you, and try to avoid situations that remind you of smoking. 

- Let people help you quit!  Call 1-877-44U-QUIT (1-877-448-7848).  The National Cancer Institute has trained counselors available to provide information and help with quitting in English or Spanish, Monday through Friday, 8:00 a.m. to 8:00 p.m. Eastern Time.  You can also call your state quitline: 1-800-QUIT-NOW (1-800-784-8669) (hours vary).',
    ];
}