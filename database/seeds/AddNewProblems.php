<?php

use App\CLH\CCD\Importer\SnomedToCpmIcdMap;
use App\Models\CPM\CpmProblem;
use Illuminate\Database\Seeder;

class AddNewProblems extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->removeProblems();

        $this->addProblems();

        $this->updateProblemsToBHI();
    }

    public function updateProblemsToBHI() {
        $problems = [
            'Depression',
            'Dementia',
            'Drug Use Disorder'
        ];

        foreach ($problems as $name) {
            $problem = CpmProblem::where([
                'name' => $name
            ])->first();
            if ($problem) {
                $problem->is_behavioral = true;
                $problem->save();

                $this->command->info("- $name has been updated as BHI");
            }
            else {
                $this->command->warn("- $name not found");
            }
        }
    }

    public function addProblems() {
        $defaultCarePlan = getDefaultCarePlanTemplate();

        foreach ($this->problems() as $name => $codes) {
            //Does a CPMProblem exist?
            $cpmProblem = CpmProblem::firstOrCreate([
                'name' => $name,
                'default_icd_10_code' => $codes['icd10'][0] ?? null,
                'is_behavioral' => true
            ]);

            if ( ! in_array($cpmProblem->id, $defaultCarePlan->cpmProblems->pluck('id')->all())) {
                $defaultCarePlan->cpmProblems()->attach($cpmProblem, [
                    'has_instruction' => true,
                    'page'            => 1,
                ]);
            }

            //ICD9 Check
            foreach ($codes['icd9'] as $icd9) {
                $map = SnomedToCpmIcdMap::updateOrCreate([
                    'icd_9_code' => $icd9,
                ], [
                    'cpm_problem_id' => $cpmProblem->id,
                    'icd_9_name'     => $cpmProblem->name,
                ]);
            }

            //ICD10 Check
            foreach ($codes['icd10'] as $icd10) {
                $map = SnomedToCpmIcdMap::updateOrCreate([
                    'icd_10_code' => $icd10,
                ], [
                    'cpm_problem_id' => $cpmProblem->id,
                    'icd_10_name'    => $cpmProblem->name,
                ]);
            }

            $this->command->info("$name has been added");
        }
    }

    public function removeProblems() {
        foreach ($this->problemsForRemoval() as $code => $name) {
            $problems = CpmProblem::where([
                'name' => $name,
                'is_behavioral' => true
            ]);
            $problems->get()->map(function ($problem) {
                $problem->problemImports()->delete();
            });
            $problems->delete();
            
            $this->command->warn("$name has been deleted");
        }
    }


    /**
     * The array of problems to be added
     *
     * @return array
     */
    public function problems(): array
    {
        $problems = [];

        //Template
        //Copy the below template to add new problems
//        $problems['Problem Name Here'] = [
//            'icd9'  => [
//
//            ],
//            'icd10' => [
//
//            ],
//        ];

        foreach ($this->importedProblems() as $code => $name) {
            $problems[$name] = [
                'icd9'  => [

                ],
                'icd10' => [
                    $code
                ],
            ];
        }

        return $problems;
    }

    public function problemsForRemoval(): array {
        $problemsForRemoval['F40.241'] = 'Acrophobia';
        $problemsForRemoval['F50.82']  = 'Avoidant/restrictive food intake disorder';
        $problemsForRemoval['F51.02']  = 'Adjustment insomnia';
        $problemsForRemoval['F98.5']   = 'Adult onset fluency disorder';
        $problemsForRemoval['F50.02']  = 'Anorexia nervosa, binge eating/purging type';
        $problemsForRemoval['F50.01']  = 'Anorexia nervosa, restricting type';
        $problemsForRemoval['F50.00']  = 'Anorexia nervosa, unspecified';
        $problemsForRemoval['F40.210'] = 'Arachnophobia';
        $problemsForRemoval['F84.5']   = "Asperger's syndrome";
        $problemsForRemoval['F90.2']   = 'Attention-deficit hyperactivity disorder, combined type';
        $problemsForRemoval['F90.8']   = 'Attention-deficit hyperactivity disorder, other type';
        $problemsForRemoval['F90.1']   = 'Attention-deficit hyperactivity disorder, predominantly hyperactive type';
        $problemsForRemoval['F90.0']   = 'Attention-deficit hyperactivity disorder, predominantly inattentive type';
        $problemsForRemoval['F90.9']   = 'Attention-deficit hyperactivity disorder, unspecified type';
        $problemsForRemoval['F84.0']   = 'Autistic disorder';
        $problemsForRemoval['F60.6']   = 'Avoidant personality disorder';
        $problemsForRemoval['F50.81']  = 'Binge eating disorder';
        $problemsForRemoval['F45.22']  = 'Body dysmorphic disorder';
        $problemsForRemoval['F50.2']   = 'Bulimia nervosa';
        $problemsForRemoval['F93.9']   = 'Childhood emotional disorder, unspecified';
        $problemsForRemoval['F80.81']  = 'Childhood onset fluency disorder';
        $problemsForRemoval['F95.1']   = 'Chronic motor or vocal tic disorder';
        $problemsForRemoval['F40.240'] = 'Claustrophobia';
        $problemsForRemoval['F91.0']   = 'Conduct disorder confined to family context';
        $problemsForRemoval['F91.2']   = 'Conduct disorder, adolescent-onset type';
        $problemsForRemoval['F91.1']   = 'Conduct disorder, childhood-onset type';
        $problemsForRemoval['F91.9']   = 'Conduct disorder, unspecified';
        $problemsForRemoval['F44.7']   = 'Conversion disorder with mixed symptom presentation';
        $problemsForRemoval['F44.4']   = 'Conversion disorder with motor symptom or deficit';
        $problemsForRemoval['F44.5']   = 'Conversion disorder with seizures or convulsions';
        $problemsForRemoval['F44.6']   = 'Conversion disorder with sensory symptom or deficit';
        $problemsForRemoval['F05']     = 'Delirium due to known physiological condition';
        $problemsForRemoval['F22']     = 'Delusional disorders';
        $problemsForRemoval['F48.1']   = 'Depersonalization-derealization syndrome';
        $problemsForRemoval['F94.2']   = 'Disinhibited attachment disorder of childhood';
        $problemsForRemoval['F44.9']   = 'Dissociative and conversion disorder, unspecified';
        $problemsForRemoval['F44.1']   = 'Dissociative fugue';
        $problemsForRemoval['F44.81']  = 'Dissociative identity disorder';
        $problemsForRemoval['F44.2']   = 'Dissociative stupor';
        $problemsForRemoval['F52.6']   = 'Dyspareunia not due to a substance or known physiological condition';
        $problemsForRemoval['F34.1']   = 'Dysthymic disorder';
        $problemsForRemoval['F50.9']   = 'Eating disorder, unspecified';
        $problemsForRemoval['F98.1']   = 'Encopresis not due to a substance or known physiological condition';
        $problemsForRemoval['F98.0']   = 'Enuresis not due to a substance or known physiological condition';
        $problemsForRemoval['F42.4']   = 'Excoriation (skin-picking) disorder';
        $problemsForRemoval['F65.2']   = 'Exhibitionism';
        $problemsForRemoval['F68.13']  = 'Factitious disorder with combined psychological and physical signs and symptoms';
        $problemsForRemoval['F68.12']  = 'Factitious disorder with predominantly physical signs and symptoms';
        $problemsForRemoval['F68.11']  = 'Factitious disorder with predominantly psychological signs and symptoms';
        $problemsForRemoval['F68.10']  = 'Factitious disorder, unspecified';
        $problemsForRemoval['F40.230'] = 'Fear of blood';
        $problemsForRemoval['F40.242'] = 'Fear of bridges';
        $problemsForRemoval['F40.243'] = 'Fear of flying';
        $problemsForRemoval['F40.231'] = 'Fear of injections and transfusions';
        $problemsForRemoval['F40.233'] = 'Fear of injury';
        $problemsForRemoval['F40.232'] = 'Fear of other medical care';
        $problemsForRemoval['F40.220'] = 'Fear of thunderstorms';
        $problemsForRemoval['F52.31']  = 'Female orgasmic disorder';
        $problemsForRemoval['F52.22']  = 'Female sexual arousal disorder';
        $problemsForRemoval['F65.0']   = 'Fetishism';
        $problemsForRemoval['F65.81']  = 'Frotteurism';
        $problemsForRemoval['F64.1']   = 'Gender identity disorder in adolescence and adulthood';
        $problemsForRemoval['F64.2']   = 'Gender identity disorder of childhood';
        $problemsForRemoval['F64.9']   = 'Gender identity disorder, unspecified';
        $problemsForRemoval['F40.291'] = 'Gynephobia';
        $problemsForRemoval['F42.3']   = 'Hoarding disorder';
        $problemsForRemoval['F52.0']   = 'Hypoactive sexual desire disorder';
        $problemsForRemoval['F45.20']  = 'Hypochondriacal disorder, unspecified';
        $problemsForRemoval['F45.21']  = 'Hypochondriasis';
        $problemsForRemoval['F63.9']   = 'Impulse disorder, unspecified';
        $problemsForRemoval['F51.05']  = 'Insomnia due to other mental disorder';
        $problemsForRemoval['F51.12']  = 'Insufficient sleep syndrome';
        $problemsForRemoval['F63.81']  = 'Intermittent explosive disorder';
        $problemsForRemoval['F63.2']   = 'Kleptomania';
        $problemsForRemoval['F52.21']  = 'Male erectile disorder';
        $problemsForRemoval['F52.32']  = 'Male orgasmic disorder';
        $problemsForRemoval['F42.2']   = 'Mixed obsessional thoughts and acts';
        $problemsForRemoval['F06.31']  = 'Mood disorder due to known physiological condition with depressive features';
        $problemsForRemoval['F06.32']  = 'Mood disorder due to known physiological condition with major depressive-like episode';
        $problemsForRemoval['F06.33']  = 'Mood disorder due to known physiological condition with manic features';
        $problemsForRemoval['F06.34']  = 'Mood disorder due to known physiological condition with mixed features';
        $problemsForRemoval['F06.30']  = 'Mood disorder due to known physiological condition, unspecified';
        $problemsForRemoval['F60.81']  = 'Narcissistic personality disorder';
        $problemsForRemoval['F51.5']   = 'Nightmare disorder';
        $problemsForRemoval['F48.9']   = 'Nonpsychotic mental disorder, unspecified';
        $problemsForRemoval['F42']     = 'Obsessive-compulsive disorder';
        $problemsForRemoval['F60.5']   = 'Obsessive-compulsive personality disorder';
        $problemsForRemoval['F91.3']   = 'Oppositional defiant disorder';
        $problemsForRemoval['F40.218'] = 'Other animal type phobia';
        $problemsForRemoval['F84.3']   = 'Other childhood disintegrative disorder';
        $problemsForRemoval['F94.8']   = 'Other childhood disorders of social functioning';
        $problemsForRemoval['F93.8']   = 'Other childhood emotional disorders';
        $problemsForRemoval['F91.8']   = 'Other conduct disorders';
        $problemsForRemoval['F80.89']  = 'Other developmental disorders of speech and language';
        $problemsForRemoval['F88']     = 'Other disorders of psychological development';
        $problemsForRemoval['F50.8']   = 'Other eating disorders';
        $problemsForRemoval['F98.29']  = 'Other feeding disorders of infancy and early childhood';
        $problemsForRemoval['F64.8']   = 'Other gender identity disorders';
        $problemsForRemoval['F45.29']  = 'Other hypochondriacal disorders';
        $problemsForRemoval['F63.8']   = 'Other impulse disorders';
        $problemsForRemoval['F51.09']  = 'Other insomnia not due to a substance or known physiological condition';
        $problemsForRemoval['F42.8']   = 'Other obsessive compulsive disorder';
        $problemsForRemoval['F40.228'] = 'Other natural environment type phobia';
        $problemsForRemoval['F65.89']  = 'Other paraphilias';
        $problemsForRemoval['F34.8']   = 'Other persistent mood ºaffective» disorders';
        $problemsForRemoval['F07.89']  = 'Other personality and behavioral disorders due to known physiological condition';
        $problemsForRemoval['F84.8']   = 'Other pervasive developmental disorders';
        $problemsForRemoval['F43.8']   = 'Other reactions to severe stress';
        $problemsForRemoval['F66']     = 'Other sexual disorders';
        $problemsForRemoval['F52.8']   = 'Other sexual dysfunction not due to a substance or known physiological condition';
        $problemsForRemoval['F40.248'] = 'Other situational type phobia';
        $problemsForRemoval['F51.8']   = 'Other sleep disorders not due to a substance or known physiological condition';
        $problemsForRemoval['F45.8']   = 'Other somatoform disorders';
        $problemsForRemoval['F50.89']  = 'Other specified eating disorder';
        $problemsForRemoval['F34.89']  = 'Other specified persistent mood disorders';
        $problemsForRemoval['F60.89']  = 'Other specific personality disorders';
        $problemsForRemoval['F98.8']   = 'Other specified behavioral and emotional disorders with onset usually occurring in childhood and ado';
        $problemsForRemoval['F68.8']   = 'Other specified disorders of adult personality and behavior';
        $problemsForRemoval['F06.8']   = 'Other specified mental disorders due to known physiological condition';
        $problemsForRemoval['F40.298'] = 'Other specified phobia';
        $problemsForRemoval['F95.8']   = 'Other tic disorders';
        $problemsForRemoval['F45.41']  = 'Pain disorder exclusively related to psychological factors';
        $problemsForRemoval['F45.42']  = 'Pain disorder with related psychological factors';
        $problemsForRemoval['F51.03']  = 'Paradoxical insomnia';
        $problemsForRemoval['F60.0']   = 'Paranoid personality disorder';
        $problemsForRemoval['F65.9']   = 'Paraphilia, unspecified';
        $problemsForRemoval['F63.0']   = 'Pathological gambling';
        $problemsForRemoval['F65.4']   = 'Pedophilia';
        $problemsForRemoval['F34.9']   = 'Persistent mood ºaffective» disorder, unspecified';
        $problemsForRemoval['F07.0']   = 'Personality change due to known physiological condition';
        $problemsForRemoval['F60.9']   = 'Personality disorder, unspecified';
        $problemsForRemoval['F84.9']   = 'Pervasive developmental disorder, unspecified';
        $problemsForRemoval['F98.3']   = 'Pica of infancy and childhood';
        $problemsForRemoval['F07.81']  = 'Postconcussional syndrome';
        $problemsForRemoval['F52.4']   = 'Premature ejaculation';
        $problemsForRemoval['F32.81']  = 'Premenstrual dysphonic disorder';
        $problemsForRemoval['F51.11']  = 'Primary hypersomnia';
        $problemsForRemoval['F51.01']  = 'Primary insomnia';
        $problemsForRemoval['F48.2']   = 'Pseudobulbar affect';
        $problemsForRemoval['F53']     = 'Puerperal psychosis';
        $problemsForRemoval['F63.1']   = 'Pyromania';
        $problemsForRemoval['F43.9']   = 'Reaction to severe stress, unspecified';
        $problemsForRemoval['F94.1']   = 'Reactive attachment disorder of childhood';
        $problemsForRemoval['F98.21']  = 'Rumination disorder of infancy';
        $problemsForRemoval['F65.50']  = 'Sadomasochism, unspecified';
        $problemsForRemoval['F94.0']   = 'Selective mutism';
        $problemsForRemoval['F52.1']   = 'Sexual aversion disorder';
        $problemsForRemoval['F65.51']  = 'Sexual masochism';
        $problemsForRemoval['F65.52']  = 'Sexual sadism';
        $problemsForRemoval['F51.9']   = 'Sleep disorder not due to a substance or known physiological condition, unspecified';
        $problemsForRemoval['F51.4']   = 'Sleep terrors [night terrors]';
        $problemsForRemoval['F51.3']   = 'Sleepwalking [somnambulism]';
        $problemsForRemoval['F80.82']  = 'Social pragmatic communication disorder';
        $problemsForRemoval['F45.0']   = 'Somatization disorder';
        $problemsForRemoval['F45.9']   = 'Somatoform disorder, unspecified';
        $problemsForRemoval['F98.4']   = 'Stereotyped movement disorders';
        $problemsForRemoval['F95.9']   = 'Tic disorder, unspecified';
        $problemsForRemoval['F95.2']   = "Tourette's disorder";
        $problemsForRemoval['F95.0']   = 'Transient tic disorder';
        $problemsForRemoval['F64.0']   = 'Transsexualism';
        $problemsForRemoval['F65.1']   = 'Transvestic fetishism';
        $problemsForRemoval['F63.3']   = 'Trichotillomania';
        $problemsForRemoval['F45.1']  = 'Undifferentiated somatoform disorder';
        $problemsForRemoval['F98.9']  = 'Unspecified behavioral and emotional disorders with onset usually occurring in childhood and adolesc';
        $problemsForRemoval['F59']    = 'Unspecified behavioral syndromes associated with physiological disturbances and physical factors';
        $problemsForRemoval['F89']    = 'Unspecified disorder of psychological development';
        $problemsForRemoval['F09']    = 'Unspecified mental disorder due to known physiological condition';
        $problemsForRemoval['F39']    = 'Unspecified mood ºaffective» disorder';
        $problemsForRemoval['F52.9']  = 'Unspecified sexual dysfunction not due to a substance or known physiological condition';
        $problemsForRemoval['F52.5']  = 'Vaginismus not due to a substance or known physiological condition';
        $problemsForRemoval['F65.3']  = 'Voyeurism';

        return $problemsForRemoval;
    }

    public function importedProblems(): array
    {

        //description text at the end cut-off from pdf in some rows
//page 1
        $importedProblems['F41.0']   = 'Panic Disorder (episodic paroxysmal anxiety)';
        $importedProblems['F43.0']   = 'Acute stress reaction';
        $importedProblems['F43.22']  = 'Adjustment disorder with anxiety';
        $importedProblems['F43.21']  = 'Adjustment disorder with depressed mood';
        $importedProblems['F43.24']  = 'Adjustment disorder with disturbance of conduct';
        $importedProblems['F43.23']  = 'Adjustment disorder with mixed anxiety and depressed mood';
        $importedProblems['F43.25']  = 'Adjustment disorder with mixed disturbance of emotions and conduct';
        $importedProblems['F43.29']  = 'Adjustment disorder with other symptoms';
        $importedProblems['F43.20']  = 'Adjustment disorder, unspecified';
        $importedProblems['F40.01']  = 'Agoraphobia with panic disorder';
        $importedProblems['F40.02']  = 'Agoraphobia without panic disorder';
        $importedProblems['F40.00']  = 'Agoraphobia, unspecified';
        $importedProblems['F10.180'] = 'Alcohol abuse with alcohol-induced anxiety disorder';
        $importedProblems['F10.14']  = 'Alcohol abuse with alcohol-induced mood disorder';
        $importedProblems['F10.150'] = 'Alcohol abuse with alcohol-induced psychotic disorder with delusions';
        $importedProblems['F10.151'] = 'Alcohol abuse with alcohol-induced psychotic disorder with hallucinations';
        $importedProblems['F10.159'] = 'Alcohol abuse with alcohol-induced psychotic disorder, unspecified';
        $importedProblems['F10.181'] = 'Alcohol abuse with alcohol-induced sexual dysfunction';
        $importedProblems['F10.182'] = 'Alcohol abuse with alcohol-induced sleep disorder';
        $importedProblems['F10.121'] = 'Alcohol abuse with intoxication delirium';
        $importedProblems['F10.188'] = 'Alcohol abuse with other alcohol-induced disorder';
        $importedProblems['F10.19']  = 'Alcohol abuse with unspecified alcohol-induced disorder';
        $importedProblems['F10.280'] = 'Alcohol dependence with alcohol-induced anxiety disorder';
        $importedProblems['F10.24']  = 'Alcohol dependence with alcohol-induced mood disorder';
        $importedProblems['F10.26']  = 'Alcohol dependence with alcohol-induced persisting amnestic disorder';
        $importedProblems['F10.27']  = 'Alcohol dependence with alcohol-induced persisting dementia';
        $importedProblems['F10.250'] = 'Alcohol dependence with alcohol-induced psychotic disorder with delusions';
        $importedProblems['F10.251'] = 'Alcohol dependence with alcohol-induced psychotic disorder with hallucinations';
        $importedProblems['F10.259'] = 'Alcohol dependence with alcohol-induced psychotic disorder, unspecified';
        $importedProblems['F10.281'] = 'Alcohol dependence with alcohol-induced sexual dysfunction';
        $importedProblems['F10.282'] = 'Alcohol dependence with alcohol-induced sleep disorder';
        $importedProblems['F10.221'] = 'Alcohol dependence with intoxication delirium';
//page 2

        $importedProblems['F10.288'] = 'Alcohol dependence with other alcohol-induced disorder';
        $importedProblems['F10.29']  = 'Alcohol dependence with unspecified alcohol-induced disorder';
        $importedProblems['F10.231'] = 'Alcohol dependence with withdrawal delirium';
        $importedProblems['F10.232'] = 'Alcohol dependence with withdrawal with perceptual disturbance';
        $importedProblems['F10.239'] = 'Alcohol dependence with withdrawal, unspecified';
        $importedProblems['F10.980'] = 'Alcohol use, unspecified with alcohol-induced anxiety disorder';
        $importedProblems['F10.94']  = 'Alcohol use, unspecified with alcohol-induced mood disorder';
        $importedProblems['F10.96']  = 'Alcohol use, unspecified with alcohol-induced persisting amnestic disorder';
        $importedProblems['F10.97']  = 'Alcohol use, unspecified with alcohol-induced persisting dementia';
        $importedProblems['F10.950'] = 'Alcohol use, unspecified with alcohol-induced psychotic disorder with delusions';
        $importedProblems['F10.951'] = 'Alcohol use, unspecified with alcohol-induced psychotic disorder with hallucinations';
        $importedProblems['F10.959'] = 'Alcohol use, unspecified with alcohol-induced psychotic disorder, unspecified';
        $importedProblems['F10.981'] = 'Alcohol use, unspecified with alcohol-induced sexual dysfunction';
        $importedProblems['F10.982'] = 'Alcohol use, unspecified with alcohol-induced sleep disorder';
        $importedProblems['F10.921'] = 'Alcohol use, unspecified with intoxication delirium';
        $importedProblems['F10.988'] = 'Alcohol use, unspecified with other alcohol-induced disorder';
        $importedProblems['F04']     = 'Amnestic disorder due to known physiological condition';
        $importedProblems['F40.290'] = 'Androphobia';
        $importedProblems['F60.2']   = 'Antisocial personality disorder';
        $importedProblems['F06.4']   = 'Anxiety disorder due to known physiological condition';
        $importedProblems['F41.9']   = 'Anxiety disorder, unspecified';
        $importedProblems['F31.81']  = 'Bipolar II disorder';
        $importedProblems['F31.31']  = 'Bipolar disorder, current episode depressed, mild';
        $importedProblems['F31.30']  = 'Bipolar disorder, current episode depressed, mild or moderate severity, unspecified';
        $importedProblems['F31.32']  = 'Bipolar disorder, current episode depressed, moderate';
        //page 3

        $importedProblems['F31.5']   = 'Bipolar disorder, current episode depressed, severe, with psychotic features';
        $importedProblems['F31.4']   = 'Bipolar disorder, current episode depressed, severe, without psychotic features';
        $importedProblems['F31.0']   = 'Bipolar disorder, current episode hypomanic';
        $importedProblems['F31.2']   = 'Bipolar disorder, current episode manic severe with psychotic features';
        $importedProblems['F31.11']  = 'Bipolar disorder, current episode manic without psychotic features, mild';
        $importedProblems['F31.12']  = 'Bipolar disorder, current episode manic without psychotic features, moderate';
        $importedProblems['F31.13']  = 'Bipolar disorder, current episode manic without psychotic features, severe';
        $importedProblems['F31.10']  = 'Bipolar disorder, current episode manic without psychotic features, unspecified';
        $importedProblems['F31.61']  = 'Bipolar disorder, current episode mixed, mild';
        $importedProblems['F31.62']  = 'Bipolar disorder, current episode mixed, moderate';
        $importedProblems['F31.64']  = 'Bipolar disorder, current episode mixed, severe, with psychotic features';
        $importedProblems['F31.63']  = 'Bipolar disorder, current episode mixed, severe, without psychotic features';
        $importedProblems['F31.60']  = 'Bipolar disorder, current episode mixed, unspecified';
        $importedProblems['F31.70']  = 'Bipolar disorder, currently in remission, most recent episode unspecified';
        $importedProblems['F31.76']  = 'Bipolar disorder, in full remission, most recent episode depressed';
        $importedProblems['F31.72']  = 'Bipolar disorder, in full remission, most recent episode hypomanic';
        $importedProblems['F31.74']  = 'Bipolar disorder, in full remission, most recent episode manic';
        $importedProblems['F31.78']  = 'Bipolar disorder, in full remission, most recent episode mixed';
        $importedProblems['F31.75']  = 'Bipolar disorder, in partial remission, most recent episode depressed';
        $importedProblems['F31.71']  = 'Bipolar disorder, in partial remission, most recent episode hypomanic';
        $importedProblems['F31.73']  = 'Bipolar disorder, in partial remission, most recent episode manic';
        $importedProblems['F31.77']  = 'Bipolar disorder, in partial remission, most recent episode mixed';
        $importedProblems['F31.9']   = 'Bipolar disorder, unspecified';
        $importedProblems['F60.3']   = 'Borderline personality disorder';
        $importedProblems['F23']     = 'Brief psychotic disorder';
        $importedProblems['F12.180'] = 'Cannabis abuse with cannabis-induced anxiety disorder';
        $importedProblems['F12.121'] = 'Cannabis abuse with intoxication delirium';
        $importedProblems['F12.122'] = 'Cannabis abuse with intoxication with perceptual disturbance';
        $importedProblems['F12.188'] = 'Cannabis abuse with other cannabis-induced disorder';
        $importedProblems['F12.150'] = 'Cannabis abuse with psychotic disorder with delusions';
        $importedProblems['F12.151'] = 'Cannabis abuse with psychotic disorder with hallucinations';
        $importedProblems['F12.159'] = 'Cannabis abuse with psychotic disorder, unspecified';
        $importedProblems['F12.19']  = 'Cannabis abuse with unspecified cannabis-induced disorder';
        $importedProblems['F12.280'] = 'Cannabis dependence with cannabis-induced anxiety disorder';
        $importedProblems['F12.221'] = 'Cannabis dependence with intoxication delirium';
        $importedProblems['F12.222'] = 'Cannabis dependence with intoxication with perceptual disturbance';
        $importedProblems['F12.288'] = 'Cannabis dependence with other cannabis-induced disorder';

        //page 4
        $importedProblems['F12.250'] = 'Cannabis dependence with psychotic disorder with delusions';
        $importedProblems['F12.251'] = 'Cannabis dependence with psychotic disorder with hallucinations';
        $importedProblems['F12.259'] = 'Cannabis dependence with psychotic disorder, unspecified';
        $importedProblems['F12.29']  = 'Cannabis dependence with unspecified cannabis-induced disorder';
        $importedProblems['F12.980'] = 'Cannabis use, unspecified with anxiety disorder';
        $importedProblems['F12.921'] = 'Cannabis use, unspecified with intoxication delirium';
        $importedProblems['F12.922'] = 'Cannabis use, unspecified with intoxication with perceptual disturbance';
        $importedProblems['F12.988'] = 'Cannabis use, unspecified with other cannabis-induced disorder';
        $importedProblems['F12.950'] = 'Cannabis use, unspecified with psychotic disorder with delusions';
        $importedProblems['F12.951'] = 'Cannabis use, unspecified with psychotic disorder with hallucinations';
        $importedProblems['F12.959'] = 'Cannabis use, unspecified with psychotic disorder, unspecified';
        $importedProblems['F06.1']   = 'Catatonic disorder due to known physiological condition';
        $importedProblems['F20.2']   = 'Catatonic schizophrenia';
        $importedProblems['F14.180'] = 'Cocaine abuse with cocaine-induced anxiety disorder';
        $importedProblems['F14.14']  = 'Cocaine abuse with cocaine-induced mood disorder';
        $importedProblems['F14.150'] = 'Cocaine abuse with cocaine-induced psychotic disorder with delusions';
        $importedProblems['F14.151'] = 'Cocaine abuse with cocaine-induced psychotic disorder with hallucinations';
        $importedProblems['F14.159'] = 'Cocaine abuse with cocaine-induced psychotic disorder, unspecified';
        $importedProblems['F14.181'] = 'Cocaine abuse with cocaine-induced sexual dysfunction';
        $importedProblems['F14.182'] = 'Cocaine abuse with cocaine-induced sleep disorder';
        $importedProblems['F14.121'] = 'Cocaine abuse with intoxication with delirium';
        $importedProblems['F14.122'] = 'Cocaine abuse with intoxication with perceptual disturbance';
        $importedProblems['F14.188'] = 'Cocaine abuse with other cocaine-induced disorder';
        $importedProblems['F14.19']  = 'Cocaine abuse with unspecified cocaine-induced disorder';
        $importedProblems['F14.280'] = 'Cocaine dependence with cocaine-induced anxiety disorder';
        $importedProblems['F14.24']  = 'Cocaine dependence with cocaine-induced mood disorder';
        $importedProblems['F14.250'] = 'Cocaine dependence with cocaine-induced psychotic disorder with delusions';
        $importedProblems['F14.251'] = 'Cocaine dependence with cocaine-induced psychotic disorder with hallucinations';
        $importedProblems['F14.259'] = 'Cocaine dependence with cocaine-induced psychotic disorder, unspecified';
        $importedProblems['F14.281'] = 'Cocaine dependence with cocaine-induced sexual dysfunction';
        $importedProblems['F14.282'] = 'Cocaine dependence with cocaine-induced sleep disorder';
        $importedProblems['F14.221'] = 'Cocaine dependence with intoxication delirium';
        $importedProblems['F14.222'] = 'Cocaine dependence with intoxication with perceptual disturbance';
        $importedProblems['F14.288'] = 'Cocaine dependence with other cocaine-induced disorder';
        $importedProblems['F14.29']  = 'Cocaine dependence with unspecified cocaine-induced disorder';

        //page 5
        $importedProblems['F14.980'] = 'Cocaine use, unspecified with cocaine-induced anxiety disorder';
        $importedProblems['F14.94']  = 'Cocaine use, unspecified with cocaine-induced mood disorder';
        $importedProblems['F14.950'] = 'Cocaine use, unspecified with cocaine-induced psychotic disorder with delusions';
        $importedProblems['F14.951'] = 'Cocaine use, unspecified with cocaine-induced psychotic disorder with hallucinations';
        $importedProblems['F14.959'] = 'Cocaine use, unspecified with cocaine-induced psychotic disorder, unspecified';
        $importedProblems['F14.981'] = 'Cocaine use, unspecified with cocaine-induced sexual dysfunction';
        $importedProblems['F14.982'] = 'Cocaine use, unspecified with cocaine-induced sleep disorder';
        $importedProblems['F14.921'] = 'Cocaine use, unspecified with intoxication delirium';
        $importedProblems['F14.922'] = 'Cocaine use, unspecified with intoxication with perceptual disturbance';
        $importedProblems['F14.988'] = 'Cocaine use, unspecified with other cocaine-induced disorder';
        $importedProblems['F34.0']   = 'Cyclothymic disorder';
        $importedProblems['F02.81']  = 'Dementia in other diseases classified elsewhere with behavioral disturbance';
        $importedProblems['F02.80']  = 'Dementia in other diseases classified elsewhere without behavioral disturbance';
        $importedProblems['F60.7']   = 'Dependent personality disorder';
        $importedProblems['F20.1']   = 'Disorganized schizophrenia';
        $importedProblems['F34.81']  = 'Dispruptive mood dysregulation disorder';
        $importedProblems['F44.0']   = 'Dissociative amnesia';

        //page 6


        $importedProblems['F41.1']   = 'Generalized anxiety disorder';
        $importedProblems['F16.183'] = 'Hallucinogen abuse with hallucinogen persisting perception disorder (flashbacks)';
        $importedProblems['F16.180'] = 'Hallucinogen abuse with hallucinogen-induced anxiety disorder';
        $importedProblems['F16.14']  = 'Hallucinogen abuse with hallucinogen-induced mood disorder';
        $importedProblems['F16.150'] = 'Hallucinogen abuse with hallucinogen-induced psychotic disorder with delusions';
        $importedProblems['F16.151'] = 'Hallucinogen abuse with hallucinogen-induced psychotic disorder with hallucinations';
        $importedProblems['F16.159'] = 'Hallucinogen abuse with hallucinogen-induced psychotic disorder, unspecified';
        $importedProblems['F16.121'] = 'Hallucinogen abuse with intoxication with delirium';
        $importedProblems['F16.122'] = 'Hallucinogen abuse with intoxication with perceptual disturbance';
        $importedProblems['F16.188'] = 'Hallucinogen abuse with other hallucinogen-induced disorder';
        $importedProblems['F16.19']  = 'Hallucinogen abuse with unspecified hallucinogen-induced disorder';
        $importedProblems['F16.283'] = 'Hallucinogen dependence with hallucinogen persisting perception disorder (flashbacks)';
        $importedProblems['F16.280'] = 'Hallucinogen dependence with hallucinogen-induced anxiety disorder';
        $importedProblems['F16.24']  = 'Hallucinogen dependence with hallucinogen-induced mood disorder';
        $importedProblems['F16.250'] = 'Hallucinogen dependence with hallucinogen-induced psychotic disorder with delusions';
        $importedProblems['F16.251'] = 'Hallucinogen dependence with hallucinogen-induced psychotic disorder with hallucinations';

        //page 7
        $importedProblems['F16.259'] = 'Hallucinogen dependence with hallucinogen-induced psychotic disorder, unspecified';
        $importedProblems['F16.221'] = 'Hallucinogen dependence with intoxication with delirium';
        $importedProblems['F16.288'] = 'Hallucinogen dependence with other hallucinogen-induced disorder';
        $importedProblems['F16.29']  = 'Hallucinogen dependence with unspecified hallucinogen-induced disorder';
        $importedProblems['F16.983'] = 'Hallucinogen use, unspecified with hallucinogen persisting perception disorder (flashbacks)';
        $importedProblems['F16.980'] = 'Hallucinogen use, unspecified with hallucinogen-induced anxiety disorder';
        $importedProblems['F16.94']  = 'Hallucinogen use, unspecified with hallucinogen-induced mood disorder';
        $importedProblems['F16.950'] = 'Hallucinogen use, unspecified with hallucinogen-induced psychotic disorder with delusions';
        $importedProblems['F16.951'] = 'Hallucinogen use, unspecified with hallucinogen-induced psychotic disorder with hallucinations';
        $importedProblems['F16.959'] = 'Hallucinogen use, unspecified with hallucinogen-induced psychotic disorder, unspecified';
        $importedProblems['F16.921'] = 'Hallucinogen use, unspecified with intoxication with delirium';
        $importedProblems['F16.988'] = 'Hallucinogen use, unspecified with other hallucinogen-induced disorder';
        $importedProblems['F60.4']   = 'Histrionic personality disorder';
        $importedProblems['F18.180'] = 'Inhalant abuse with inhalant-induced anxiety disorder';
        $importedProblems['F18.17']  = 'Inhalant abuse with inhalant-induced dementia';
        $importedProblems['F18.14']  = 'Inhalant abuse with inhalant-induced mood disorder';
        $importedProblems['F18.150'] = 'Inhalant abuse with inhalant-induced psychotic disorder with delusions';
        $importedProblems['F18.151'] = 'Inhalant abuse with inhalant-induced psychotic disorder with hallucinations';
        $importedProblems['F18.159'] = 'Inhalant abuse with inhalant-induced psychotic disorder, unspecified';
        $importedProblems['F18.121'] = 'Inhalant abuse with intoxication delirium';
        $importedProblems['F18.188'] = 'Inhalant abuse with other inhalant-induced disorder';
        $importedProblems['F18.19']  = 'Inhalant abuse with unspecified inhalant-induced disorder';
        $importedProblems['F18.280'] = 'Inhalant dependence with inhalant-induced anxiety disorder';
        $importedProblems['F18.27']  = 'Inhalant dependence with inhalant-induced dementia';
        $importedProblems['F18.24']  = 'Inhalant dependence with inhalant-induced mood disorder';
        $importedProblems['F18.250'] = 'Inhalant dependence with inhalant-induced psychotic disorder with delusions';
        $importedProblems['F18.251'] = 'Inhalant dependence with inhalant-induced psychotic disorder with hallucinations';
        $importedProblems['F18.259'] = 'Inhalant dependence with inhalant-induced psychotic disorder, unspecified';
        $importedProblems['F18.221'] = 'Inhalant dependence with intoxication delirium';
        $importedProblems['F18.980'] = 'Inhalant use, unspecified with inhalant-induced anxiety disorder';
        $importedProblems['F18.94']  = 'Inhalant use, unspecified with inhalant-induced mood disorder';

        //page 8
        $importedProblems['F18.97']  = 'Inhalant use, unspecified with inhalant-induced persisting dementia';
        $importedProblems['F18.950'] = 'Inhalant use, unspecified with inhalant-induced psychotic disorder with delusions';
        $importedProblems['F18.951'] = 'Inhalant use, unspecified with inhalant-induced psychotic disorder with hallucinations';
        $importedProblems['F18.959'] = 'Inhalant use, unspecified with inhalant-induced psychotic disorder, unspecified';
        $importedProblems['F18.921'] = 'Inhalant use, unspecified with intoxication with delirium';
        $importedProblems['F33.2']   = 'Major depressive disorder, recurrent severe without psychotic features';
        $importedProblems['F33.42']  = 'Major depressive disorder, recurrent, in full remission';
        $importedProblems['F33.41']  = 'Major depressive disorder, recurrent, in partial remission';
        $importedProblems['F33.40']  = 'Major depressive disorder, recurrent, in remission, unspecified';
        $importedProblems['F33.0']   = 'Major depressive disorder, recurrent, mild';
        $importedProblems['F33.1']   = 'Major depressive disorder, recurrent, moderate';
        $importedProblems['F33.3']   = 'Major depressive disorder, recurrent, severe with psychotic symptoms';
        $importedProblems['F33.9']   = 'Major depressive disorder, recurrent, unspecified';
        $importedProblems['F32.5']   = 'Major depressive disorder, single episode, in full remission';
        $importedProblems['F32.4']   = 'Major depressive disorder, single episode, in partial remission';
        $importedProblems['F32.0']   = 'Major depressive disorder, single episode, mild';
        $importedProblems['F32.1']   = 'Major depressive disorder, single episode, moderate';
        $importedProblems['F32.3']   = 'Major depressive disorder, single episode, severe with psychotic features';
        $importedProblems['F32.2']   = 'Major depressive disorder, single episode, severe without psychotic features';
        $importedProblems['F32.9']   = 'Major depressive disorder, single episode, unspecified';
        $importedProblems['F30.4']   = 'Manic episode in full remission';
        $importedProblems['F30.3']   = 'Manic episode in partial remission';
        $importedProblems['F30.11']  = 'Manic episode without psychotic symptoms, mild';
        $importedProblems['F30.12']  = 'Manic episode without psychotic symptoms, moderate';
        $importedProblems['F30.10']  = 'Manic episode without psychotic symptoms, unspecified';
        $importedProblems['F30.2']   = 'Manic episode, severe with psychotic symptoms';
        $importedProblems['F30.13']  = 'Manic episode, severe, without psychotic symptoms';
        $importedProblems['F30.9']   = 'Manic episode, unspecified';
        $importedProblems['F11.121'] = 'Opioid abuse with intoxication delirium';
        $importedProblems['F11.122'] = 'Opioid abuse with intoxication with perceptual disturbance';
        $importedProblems['F11.14']  = 'Opioid abuse with opioid-induced mood disorder';
        $importedProblems['F11.150'] = 'Opioid abuse with opioid-induced psychotic disorder with delusions';
        $importedProblems['F11.151'] = 'Opioid abuse with opioid-induced psychotic disorder with hallucinations';
        $importedProblems['F11.159'] = 'Opioid abuse with opioid-induced psychotic disorder, unspecified';
        $importedProblems['F11.181'] = 'Opioid abuse with opioid-induced sexual dysfunction';
        $importedProblems['F11.182'] = 'Opioid abuse with opioid-induced sleep disorder';
        $importedProblems['F11.188'] = 'Opioid abuse with other opioid-induced disorder';
        $importedProblems['F11.19']  = 'Opioid abuse with unspecified opioid-induced disorder';
        $importedProblems['F11.221'] = 'Opioid dependence with intoxication delirium';
        $importedProblems['F11.24']  = 'Opioid dependence with opioid-induced mood disorder';
        $importedProblems['F11.250'] = 'Opioid dependence with opioid-induced psychotic disorder with delusions';
        $importedProblems['F11.251'] = 'Opioid dependence with opioid-induced psychotic disorder with hallucinations';
        $importedProblems['F11.259'] = 'Opioid dependence with opioid-induced psychotic disorder, unspecified';
        $importedProblems['F11.281'] = 'Opioid dependence with opioid-induced sexual dysfunction';
        $importedProblems['F11.282'] = 'Opioid dependence with opioid-induced sleep disorder';
        $importedProblems['F11.288'] = 'Opioid dependence with other opioid-induced disorder';
        $importedProblems['F11.29']  = 'Opioid dependence with unspecified opioid-induced disorder';
        $importedProblems['F11.921'] = 'Opioid use, unspecified with intoxication delirium';
        $importedProblems['F11.922'] = 'Opioid use, unspecified with intoxication with perceptual disturbance';
        $importedProblems['F11.94']  = 'Opioid use, unspecified with opioid-induced mood disorder';
        $importedProblems['F11.950'] = 'Opioid use, unspecified with opioid-induced psychotic disorder with delusions';
        $importedProblems['F11.951'] = 'Opioid use, unspecified with opioid-induced psychotic disorder with hallucinations';
        $importedProblems['F11.959'] = 'Opioid use, unspecified with opioid-induced psychotic disorder, unspecified';
        $importedProblems['F11.981'] = 'Opioid use, unspecified with opioid-induced sexual dysfunction';
        $importedProblems['F11.982'] = 'Opioid use, unspecified with opioid-induced sleep disorder';
        $importedProblems['F11.988'] = 'Opioid use, unspecified with other opioid-induced disorder';
        $importedProblems['F31.89']  = 'Other bipolar disorder';

        //page 10
        $importedProblems['F32.8']   = 'Other depressive episodes';
        $importedProblems['F30.8']   = 'Other manic episodes';
        $importedProblems['F41.3']   = 'Other mixed anxiety disorders';
        $importedProblems['F40.8']   = 'Other phobic anxiety disorders';
        $importedProblems['F19.121'] = 'Other psychoactive substance abuse with intoxication delirium';
        $importedProblems['F19.122'] = 'Other psychoactive substance abuse with intoxication with perceptual disturbances';
        $importedProblems['F19.188'] = 'Other psychoactive substance abuse with other psychoactive substance-induced disorder';
        $importedProblems['F19.180'] = 'Other psychoactive substance abuse with psychoactive substance-induced anxiety disorder';
        $importedProblems['F19.14']  = 'Other psychoactive substance abuse with psychoactive substance-induced mood disorder';
        $importedProblems['F19.16']  = 'Other psychoactive substance abuse with psychoactive substance-induced persisting amnestic disorder';
        $importedProblems['F19.17']  = 'Other psychoactive substance abuse with psychoactive substance-induced persisting dementia';
        $importedProblems['F19.150'] = 'Other psychoactive substance abuse with psychoactive substance-induced psychotic disorder with delus';
        $importedProblems['F19.151'] = 'Other psychoactive substance abuse with psychoactive substance-induced psychotic disorder with hallu';
        $importedProblems['F19.159'] = 'Other psychoactive substance abuse with psychoactive substance-induced psychotic disorder, unspecifi';
        $importedProblems['F19.181'] = 'Other psychoactive substance abuse with psychoactive substance-induced sexual dysfunction';

        //page 11
        $importedProblems['F19.182'] = 'Other psychoactive substance abuse with psychoactive substance-induced sleep disorder';
        $importedProblems['F19.19']  = 'Other psychoactive substance abuse with unspecified psychoactive substance-induced disorder';
        $importedProblems['F19.221'] = 'Other psychoactive substance dependence with intoxication delirium';
        $importedProblems['F19.222'] = 'Other psychoactive substance dependence with intoxication with perceptual disturbance';
        $importedProblems['F19.288'] = 'Other psychoactive substance dependence with other psychoactive substance-induced disorder';
        $importedProblems['F19.280'] = 'Other psychoactive substance dependence with psychoactive substance-induced anxiety disorder';
        $importedProblems['F19.24']  = 'Other psychoactive substance dependence with psychoactive substance-induced mood disorder';
        $importedProblems['F19.26']  = 'Other psychoactive substance dependence with psychoactive substance-induced persisting amnestic diso';
        $importedProblems['F19.27']  = 'Other psychoactive substance dependence with psychoactive substance-induced persisting dementia';
        $importedProblems['F19.250'] = 'Other psychoactive substance dependence with psychoactive substance-induced psychotic disorder with';
        $importedProblems['F19.251'] = 'Other psychoactive substance dependence with psychoactive substance-induced psychotic disorder with';
        $importedProblems['F19.259'] = 'Other psychoactive substance dependence with psychoactive substance-induced psychotic disorder, unsp';
        $importedProblems['F19.281'] = 'Other psychoactive substance dependence with psychoactive substance-induced sexual dysfunction';
        $importedProblems['F19.282'] = 'Other psychoactive substance dependence with psychoactive substance-induced sleep disorder';
        $importedProblems['F19.29']  = 'Other psychoactive substance dependence with unspecified psychoactive substance-induced disorder';
        $importedProblems['F19.231'] = 'Other psychoactive substance dependence with withdrawal delirium';
        $importedProblems['F19.232'] = 'Other psychoactive substance dependence with withdrawal with perceptual disturbance';
        $importedProblems['F19.239'] = 'Other psychoactive substance dependence with withdrawal, unspecified';
        $importedProblems['F19.921'] = 'Other psychoactive substance use, unspecified with intoxication with delirium';
        $importedProblems['F19.922'] = 'Other psychoactive substance use, unspecified with intoxication with perceptual disturbance';
        $importedProblems['F19.988'] = 'Other psychoactive substance use, unspecified with other psychoactive substance-induced disorder';
        $importedProblems['F19.980'] = 'Other psychoactive substance use, unspecified with psychoactive substanceinduced anxiety disorder';
        $importedProblems['F19.94']  = 'Other psychoactive substance use, unspecified with psychoactive substanceinduced mood disorder';
        $importedProblems['F19.96']  = 'Other psychoactive substance use, unspecified with psychoactive substanceinduced persisting amnesti';

        //page 12
        $importedProblems['F19.97']  = 'Other psychoactive substance use, unspecified with psychoactive substanceinduced persisting dementi';
        $importedProblems['F19.950'] = 'Other psychoactive substance use, unspecified with psychoactive substanceinduced psychotic disorder';
        $importedProblems['F19.951'] = 'Other psychoactive substance use, unspecified with psychoactive substanceinduced psychotic disorder';
        $importedProblems['F19.959'] = 'Other psychoactive substance use, unspecified with psychoactive substanceinduced psychotic disorder';
        $importedProblems['F19.981'] = 'Other psychoactive substance use, unspecified with psychoactive substanceinduced sexual dysfunction';
        $importedProblems['F19.982'] = 'Other psychoactive substance use, unspecified with psychoactive substanceinduced sleep disorder';
        $importedProblems['F19.931'] = 'Other psychoactive substance use, unspecified with withdrawal delirium';
        $importedProblems['F19.932'] = 'Other psychoactive substance use, unspecified with withdrawal with perceptual disturbance';
        $importedProblems['F28']     = 'Other psychotic disorder not due to a substance or known physiological condition';
        $importedProblems['F33.8']   = 'Other recurrent depressive disorders';
        $importedProblems['F25.8']   = 'Other schizoaffective disorders';
        $importedProblems['F20.89']  = 'Other schizophrenia';
        $importedProblems['F32.89']  = 'Other specified depressive episodes';
        $importedProblems['F41.8']   = 'Other specified anxiety disorders';
        $importedProblems['F15.121'] = 'Other stimulant abuse with intoxication delirium';
        $importedProblems['F15.122'] = 'Other stimulant abuse with intoxication with perceptual disturbance';
        $importedProblems['F15.188'] = 'Other stimulant abuse with other stimulant-induced disorder';
        $importedProblems['F15.180'] = 'Other stimulant abuse with stimulant-induced anxiety disorder';
        $importedProblems['F15.14']  = 'Other stimulant abuse with stimulant-induced mood disorder';
        $importedProblems['F15.150'] = 'Other stimulant abuse with stimulant-induced psychotic disorder with delusions';

        //page 13
        $importedProblems['F15.151'] = 'Other stimulant abuse with stimulant-induced psychotic disorder with hallucinations';
        $importedProblems['F15.159'] = 'Other stimulant abuse with stimulant-induced psychotic disorder, unspecified';
        $importedProblems['F15.181'] = 'Other stimulant abuse with stimulant-induced sexual dysfunction';
        $importedProblems['F15.182'] = 'Other stimulant abuse with stimulant-induced sleep disorder';
        $importedProblems['F15.19']  = 'Other stimulant abuse with unspecified stimulant-induced disorder';
        $importedProblems['F15.221'] = 'Other stimulant dependence with intoxication delirium';
        $importedProblems['F15.222'] = 'Other stimulant dependence with intoxication with perceptual disturbance';
        $importedProblems['F15.288'] = 'Other stimulant dependence with other stimulant-induced disorder';
        $importedProblems['F15.280'] = 'Other stimulant dependence with stimulant-induced anxiety disorder';
        $importedProblems['F15.24']  = 'Other stimulant dependence with stimulant-induced mood disorder';
        $importedProblems['F15.250'] = 'Other stimulant dependence with stimulant-induced psychotic disorder with delusions';
        $importedProblems['F15.251'] = 'Other stimulant dependence with stimulant-induced psychotic disorder with hallucinations';
        $importedProblems['F15.259'] = 'Other stimulant dependence with stimulant-induced psychotic disorder, unspecified';
        $importedProblems['F15.281'] = 'Other stimulant dependence with stimulant-induced sexual dysfunction';
        $importedProblems['F15.282'] = 'Other stimulant dependence with stimulant-induced sleep disorder';
        $importedProblems['F15.29']  = 'Other stimulant dependence with unspecified stimulant-induced disorder';
        $importedProblems['F15.921'] = 'Other stimulant use, unspecified with intoxication delirium';
        $importedProblems['F15.922'] = 'Other stimulant use, unspecified with intoxication with perceptual disturbance';
        $importedProblems['F15.988'] = 'Other stimulant use, unspecified with other stimulant-induced disorder';
        $importedProblems['F15.980'] = 'Other stimulant use, unspecified with stimulant-induced anxiety disorder';
        $importedProblems['F15.94']  = 'Other stimulant use, unspecified with stimulant-induced mood disorder';
        $importedProblems['F15.950'] = 'Other stimulant use, unspecified with stimulant-induced psychotic disorder with delusions';
        $importedProblems['F15.951'] = 'Other stimulant use, unspecified with stimulant-induced psychotic disorder with hallucinations';
        $importedProblems['F15.959'] = 'Other stimulant use, unspecified with stimulant-induced psychotic disorder,  unspecified';
        $importedProblems['F15.981'] = 'Other stimulant use, unspecified with stimulant-induced sexual dysfunction';
        $importedProblems['F15.982'] = 'Other stimulant use, unspecified with stimulant-induced sleep disorder';
        $importedProblems['F41.0']   = 'Panic disorder ºepisodic paroxysmal anxiety» without agoraphobia';
        $importedProblems['F20.0']   = 'Paranoid schizophrenia';

        //page 14
        $importedProblems['F40.9']   = 'Phobic anxiety disorder, unspecified';
        $importedProblems['F43.11']  = 'Post-traumatic stress disorder, acute';
        $importedProblems['F43.12']  = 'Post-traumatic stress disorder, chronic';
        $importedProblems['F43.10']  = 'Post-traumatic stress disorder, unspecified';
        $importedProblems['F06.2']   = 'Psychotic disorder with delusions due to known physiological condition';
        $importedProblems['F06.0']   = 'Psychotic disorder with hallucinations due to known physiological condition';
        $importedProblems['F20.5']   = 'Residual schizophrenia';
        $importedProblems['F25.0']   = 'Schizoaffective disorder, bipolar type';
        $importedProblems['F25.1']   = 'Schizoaffective disorder, depressive type';
        $importedProblems['F25.9']   = 'Schizoaffective disorder, unspecified';
        $importedProblems['F60.1']   = 'Schizoid personality disorder';
        $importedProblems['F20.9']   = 'Schizophrenia, unspecified';
        $importedProblems['F20.81']  = 'Schizophreniform disorder';
        $importedProblems['F21']     = 'Schizotypal disorder';
        $importedProblems['F13.121'] = 'Sedative, hypnotic or anxiolytic abuse with intoxication delirium';
        $importedProblems['F13.188'] = 'Sedative, hypnotic or anxiolytic abuse with other sedative, hypnotic or anxiolytic-induced disorder';
        $importedProblems['F13.180'] = 'Sedative, hypnotic or anxiolytic abuse with sedative, hypnotic or anxiolytic-induced anxiety disorder';
        $importedProblems['F13.14']  = 'Sedative, hypnotic or anxiolytic abuse with sedative, hypnotic or anxiolytic-induced mood disorder';

        //page 15
        $importedProblems['F13.151'] = 'Sedative, hypnotic or anxiolytic abuse with sedative, hypnotic or anxiolytic-induced psychotic disor';
        $importedProblems['F13.159'] = 'Sedative, hypnotic or anxiolytic abuse with sedative, hypnotic or anxiolytic-induced psychotic disor';
        $importedProblems['F13.150'] = 'Sedative, hypnotic or anxiolytic abuse with sedative, hypnotic or anxiolytic-induced psychotic disor';
        $importedProblems['F13.181'] = 'Sedative, hypnotic or anxiolytic abuse with sedative, hypnotic or anxiolytic-induced sexual dysfunct';
        $importedProblems['F13.182'] = 'Sedative, hypnotic or anxiolytic abuse with sedative, hypnotic or anxiolytic-induced sleep disorder';
        $importedProblems['F13.19']  = 'Sedative, hypnotic or anxiolytic abuse with unspecified sedative, hypnotic or anxiolytic-induced dis';
        $importedProblems['F13.221'] = 'Sedative, hypnotic or anxiolytic dependence with intoxication delirium';
        $importedProblems['F13.288'] = 'Sedative, hypnotic or anxiolytic dependence with other sedative, hypnotic or anxiolytic-induced diso';
        $importedProblems['F13.280'] = 'Sedative, hypnotic or anxiolytic dependence with sedative, hypnotic or anxiolytic-induced anxiety di';
        $importedProblems['F13.24']  = 'Sedative, hypnotic or anxiolytic dependence with sedative, hypnotic or anxiolytic-induced mood disor';
        $importedProblems['F13.27']  = 'Sedative, hypnotic or anxiolytic dependence with sedative, hypnotic or anxiolytic-induced persisting';
        $importedProblems['F13.26']  = 'Sedative, hypnotic or anxiolytic dependence with sedative, hypnotic or anxiolytic-induced persisting';
        $importedProblems['F13.259'] = 'Sedative, hypnotic or anxiolytic dependence with sedative, hypnotic or anxiolytic-induced psychotic';
        $importedProblems['F13.251'] = 'Sedative, hypnotic or anxiolytic dependence with sedative, hypnotic or anxiolytic-induced psychotic';
        $importedProblems['F13.250'] = 'Sedative, hypnotic or anxiolytic dependence with sedative, hypnotic or anxiolytic-induced psychotic';
        $importedProblems['F13.281'] = 'Sedative, hypnotic or anxiolytic dependence with sedative, hypnotic or anxiolytic-induced sexual dys';
        $importedProblems['F13.282'] = 'Sedative, hypnotic or anxiolytic dependence with sedative, hypnotic or anxiolytic-induced sleep diso';
        $importedProblems['F13.29']  = 'Sedative, hypnotic or anxiolytic dependence with unspecified sedative, hypnotic or anxiolytic-induced';
        $importedProblems['F13.231'] = 'Sedative, hypnotic or anxiolytic dependence with withdrawal delirium';
        $importedProblems['F13.232'] = 'Sedative, hypnotic or anxiolytic dependence with withdrawal with perceptual disturbance';
        $importedProblems['F13.239'] = 'Sedative, hypnotic or anxiolytic dependence with withdrawal, unspecified';
        $importedProblems['F13.921'] = 'Sedative, hypnotic or anxiolytic use, unspecified with intoxication delirium';
        $importedProblems['F13.988'] = 'Sedative, hypnotic or anxiolytic use, unspecified with other sedative, hypnotic or anxiolytic-induce';
        $importedProblems['F13.980'] = 'Sedative, hypnotic or anxiolytic use, unspecified with sedative, hypnotic or anxiolytic-induced anxi';

        //page 16
        $importedProblems['F13.94']  = 'Sedative, hypnotic or anxiolytic use, unspecified with sedative, hypnotic or anxiolytic-induced mood';
        $importedProblems['F13.97']  = 'Sedative, hypnotic or anxiolytic use, unspecified with sedative, hypnotic or anxiolytic-induced pers';
        $importedProblems['F13.96']  = 'Sedative, hypnotic or anxiolytic use, unspecified with sedative, hypnotic or anxiolytic-induced pers';
        $importedProblems['F13.950'] = 'Sedative, hypnotic or anxiolytic use, unspecified with sedative, hypnotic or anxiolytic-induced psyc';
        $importedProblems['F13.959'] = 'Sedative, hypnotic or anxiolytic use, unspecified with sedative, hypnotic or anxiolytic-induced psyc';
        $importedProblems['F13.951'] = 'Sedative, hypnotic or anxiolytic use, unspecified with sedative, hypnotic or anxiolytic-induced psyc';
        $importedProblems['F13.981'] = 'Sedative, hypnotic or anxiolytic use, unspecified with sedative, hypnotic or anxiolytic-induced sexu';
        $importedProblems['F13.982'] = 'Sedative, hypnotic or anxiolytic use, unspecified with sedative, hypnotic or anxiolytic-induced slee';
        $importedProblems['F13.931'] = 'Sedative, hypnotic or anxiolytic use, unspecified with withdrawal delirium';
        $importedProblems['F13.932'] = 'Sedative, hypnotic or anxiolytic use, unspecified with withdrawal with perceptual disturbances';
        $importedProblems['F93.0']   = 'Separation anxiety disorder of childhood';
        $importedProblems['F24']     = 'Shared psychotic disorder';
        $importedProblems['F40.11']  = 'Social phobia, generalized';
        $importedProblems['F40.10']  = 'Social phobia, unspecified';
        $importedProblems['R45.7']   = 'State of emotional shock and stress, unspecified';

        //page 17
        $importedProblems['F20.3']  = 'Undifferentiated schizophrenia';
        $importedProblems['F03.91'] = 'Unspecified dementia with behavioral disturbance';
        $importedProblems['F03.90'] = 'Unspecified dementia without behavioral disturbance';
        $importedProblems['F07.9']  = 'Unspecified personality and behavioral disorder due to known physiological condition';
        $importedProblems['F29']    = 'Unspecified psychosis not due to a substance or known physiological condition';
        $importedProblems['F01.51'] = 'Vascular dementia with behavioral disturbance';
        $importedProblems['F01.50'] = 'Vascular dementia without behavioral disturbance';

        return $importedProblems;

    }
}
