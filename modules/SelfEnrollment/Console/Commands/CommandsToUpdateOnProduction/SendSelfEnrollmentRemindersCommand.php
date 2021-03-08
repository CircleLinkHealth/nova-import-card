<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SelfEnrollment\Console\Commands\CommandsToUpdateOnProduction;

use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\SelfEnrollment\Entities\User;
use CircleLinkHealth\SelfEnrollment\Jobs\SendReminder;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Console\Command;

class SendSelfEnrollmentRemindersCommand extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Self Enrollment reminders for given ids and practice:commonwealth';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:self-enrollment-reminders-commonwealth';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function getIds()
    {
        return [
            371277,
            371278,
            371283,
            371285,
            371297,
            371300,
            371301,
            371305,
            371307,
            371311,
            371318,
            371320,
            371324,
            371334,
            371335,
            371340,
            371342,
            371346,
            371354,
            371361,
            371372,
            371377,
            371380,
            371382,
            371385,
            371386,
            371393,
            371394,
            371396,
            371398,
            371405,
            371410,
            371411,
            371418,
            371419,
            371420,
            371421,
            371433,
            371437,
            371445,
            371453,
            371454,
            371458,
            371460,
            371467,
            371468,
            371477,
            371478,
            371479,
            371480,
            371485,
            371491,
            371494,
            371495,
            371496,
            371497,
            371501,
            371502,
            371504,
            371505,
            371506,
            371511,
            371513,
            371514,
            371515,
            371518,
            371523,
            371524,
            371527,
            371529,
            371532,
            371533,
            371553,
            371555,
            371561,
            371562,
            371566,
            371567,
            371568,
            371571,
            371575,
            371588,
            371589,
            371593,
            371596,
            371597,
            371599,
            371604,
            371606,
            371609,
            371619,
            371622,
            371626,
            371628,
            371629,
            371630,
            371631,
            371635,
            371645,
            371648,
            371649,
            371650,
            371652,
            371658,
            371665,
            371667,
            371669,
            371676,
            371677,
            371680,
            371683,
            371687,
            371688,
            371689,
            371690,
            371692,
            371693,
            371695,
            371697,
            371698,
            371701,
            371709,
            371710,
            371713,
            371720,
            371721,
            371735,
            371737,
            371740,
            371743,
            371750,
            371753,
            371762,
            371763,
            371764,
            371765,
            371766,
            371769,
            371770,
            371772,
            371773,
            371775,
            371778,
            371780,
            371781,
            371782,
            371862,
            371882,
            371900,
            371919,
            371939,
            371941,
            371942,
            371959,
            371971,
            371998,
            372021,
            372097,
            372100,
            372114,
            372117,
            372123,
            372125,
            372147,
            372150,
            372176,
            372181,
            372187,
            372213,
            372221,
            372235,
            372249,
            372276,
            372279,
            372281,
            372285,
            372291,
            372317,
            372324,
            372336,
            372341,
            372344,
            372345,
            372358,
            372368,
            372371,
            372375,
            372377,
            372400,
            372441,
            372464,
            372472,
            372487,
            372493,
            372494,
            372495,
            372506,
            372513,
            372517,
            372528,
            372540,
            372549,
            372550,
            372554,
            372572,
            372576,
            372577,
            372585,
            372586,
            372589,
            372595,
            372597,
            372608,
            372612,
            372626,
            372637,
            372638,
            372648,
            372660,
            372667,
            372682,
            372714,
            372724,
            372732,
            372738,
            372752,
            372764,
            372768,
            372800,
            372808,
            372809,
            372813,
            372828,
            372832,
            372861,
            372863,
            372867,
            372869,
            372891,
            372896,
            372898,
            372916,
            372917,
            372922,
            372963,
            373030,
            373031,
            373040,
            373046,
            373053,
            373069,
            373075,
            373089,
            373102,
            373120,
            373123,
            373140,
            373141,
            373153,
            373155,
            373165,
            373166,
            373173,
            373179,
            373180,
            373185,
            373194,
            373205,
            373212,
            373222,
            373280,
            373284,
            373292,
            373301,
            373303,
            373307,
            373308,
            373322,
            373330,
            373344,
            373350,
            373364,
            373366,
            373369,
            373380,
            373382,
            373392,
            373394,
            373395,
            373431,
            373449,
            373468,
            373484,
            373499,
            373500,
            373505,
            373520,
            373531,
            373541,
            373558,
            373562,
            373575,
            373584,
            373591,
            373596,
            373610,
            373663,
            373685,
            373686,
            373733,
            373744,
            373746,
            373758,
            373765,
            373771,
            373774,
            373780,
            373789,
            373791,
            373793,
            373795,
            373797,
            373802,
            373804,
            373816,
            373830,
            373833,
            373877,
            373878,
            373889,
            373891,
            373900,
            373904,
            373923,
            373927,
            373939,
            373944,
            373947,
            373958,
            373967,
            373976,
            373977,
            373978,
            373988,
            373992,
            374000,
            374003,
            374008,
            374025,
            374032,
            374050,
            374056,
            374079,
            374087,
            374092,
            374093,
            374096,
            374102,
            374106,
            374112,
            374122,
            374124,
            374137,
            374143,
            374145,
            374156,
            374161,
            374162,
            374165,
            374185,
            374207,
            374216,
            374223,
            374226,
            374230,
            374234,
            374236,
            374237,
            374240,
            374247,
            374248,
            374256,
            374265,
            374280,
            374289,
            374293,
            374298,
            374308,
            374319,
            374325,
            374336,
            374381,
            374385,
            374395,
            374403,
            374412,
            374417,
            374425,
            374428,
            374436,
            374438,
            374441,
            374442,
            374448,
            374459,
        ];
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Enrollee::with('user.patientInfo')
            ->where('practice_id', 232)
            ->whereIn('id', $this->getIds())
            ->whereHas(
                'user.patientInfo',
                function ($patient) {
                    $patient->where('ccm_status', Patient::UNREACHABLE);
                }
            )
            ->whereNotNull('user_id')
            ->chunk(
                50,
                function ($enrollees) {
                    Enrollee::whereIn(
                        'id',
                        $enrollees->pluck('id')
                                ->all()
                    )
                        ->update(
                            [
                                'status' => Enrollee::QUEUE_AUTO_ENROLLMENT,
                            ]
                        );

                    foreach ($enrollees as $enrollee) {
                        SendReminder::dispatch(new User($enrollee->user->toArray()));
                        $this->info("SendReminder JOB queued for Enrollee $enrollee->id");
                    }
                }
            );
    }
}