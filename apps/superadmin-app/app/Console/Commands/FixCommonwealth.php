<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use CircleLinkHealth\Eligibility\CcdaImporter\CcdaImporterWrapper;
use CircleLinkHealth\Eligibility\Factories\AthenaEligibilityCheckableFactory;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Console\Command;

class FixCommonwealth extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Some Commonwealth patients do not have the correct providers';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:commonwealth';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Enrollee::wherePracticeId(232)
            ->whereIn('id', [
                372202,
                372203,
                372207,
                372209,
                372220,
                372240,
                372244,
                372251,
                372252,
                372256,
                372257,
                372258,
                372263,
                372268,
                372277,
                372300,
                372311,
                372316,
                372332,
                372335,
                372343,
                372349,
                372354,
                372356,
                372357,
                372364,
                372366,
                372369,
                372370,
                372378,
                372379,
                372381,
                372382,
                372388,
                372390,
                372391,
                372393,
                372399,
                372402,
                372411,
                372412,
                372415,
                372416,
                372417,
                372424,
                372426,
                372427,
                372431,
                372434,
                372437,
                372444,
                372446,
                372447,
                372452,
                372454,
                372458,
                372463,
                372465,
                372473,
                372474,
                372478,
                372486,
                372489,
                372499,
                372504,
                372507,
                372510,
                372511,
                372516,
                372520,
                372521,
                372526,
                372527,
                372529,
                372531,
                372539,
                372543,
                372552,
                372553,
                372567,
                372568,
                372569,
                372579,
                372588,
                372590,
                372593,
                372594,
                372604,
                372607,
                372610,
                372624,
                372631,
                372636,
                372650,
                372651,
                372659,
                372661,
                372670,
                372674,
                372677,
                372678,
                372680,
                372684,
                372685,
                372687,
                372688,
                372692,
                372695,
                372704,
                372705,
                372706,
                372708,
                372712,
                372716,
                372721,
                372727,
                372728,
                372735,
                372740,
                372743,
                372744,
                372747,
                372750,
                372753,
                372757,
                372758,
                372759,
                372766,
                372771,
                372774,
                372777,
                372791,
                372792,
                372793,
                372795,
                372796,
                372801,
                372805,
                372806,
                372810,
                372818,
                372819,
                372820,
                372823,
                372826,
                372830,
                372835,
                372838,
                372840,
                372841,
                372842,
                372844,
                372848,
                372858,
                372860,
                372876,
                372882,
                372888,
                372897,
                372899,
                372901,
                372904,
                372939,
                372945,
                372948,
                372964,
                372976,
                372987,
                372993,
                373001,
                373006,
                373024,
                373026,
                373052,
                373054,
                373056,
                373057,
                373070,
                373078,
                373079,
                373104,
                373105,
                373112,
                373113,
                373116,
                373118,
                373134,
                373139,
                373142,
                373146,
                373150,
                373159,
                373168,
                373169,
                373170,
                373177,
                373178,
                373182,
                373186,
                373192,
                373199,
                373201,
                373202,
                373204,
                373210,
                373211,
                373213,
                373214,
                373224,
                373227,
                373228,
                373229,
                373231,
                373247,
                373248,
                373250,
                373251,
                373264,
                373265,
                373276,
                373279,
                373290,
                373297,
                373306,
                373320,
                373331,
                373335,
                373357,
                373360,
                373370,
                373373,
                373381,
                373383,
                373390,
                373408,
                373415,
                373416,
                373430,
                373432,
                373437,
                373440,
                373445,
                373448,
                373461,
                373467,
                373478,
                373479,
                373480,
                373481,
                373482,
                373485,
                373487,
                373488,
                373493,
                373501,
                373506,
                373511,
                373519,
                373523,
                373532,
                373544,
                373545,
                373548,
                373550,
                373551,
                373560,
                373566,
                373567,
                373568,
                373572,
                373579,
                373585,
                373588,
                373593,
                373602,
                373614,
                373628,
                373633,
                373635,
                373636,
                373638,
                373648,
                373654,
                373658,
                373666,
                373667,
                373668,
                373670,
                373673,
                373677,
                373682,
                373684,
                373697,
                373700,
                373710,
                373712,
                373724,
                373726,
                373727,
                373742,
                373745,
                373748,
                373749,
                373750,
                373757,
                373766,
                373769,
                373773,
                373781,
                373782,
                373783,
                373792,
                373794,
                373796,
                373798,
                373800,
                373811,
                373813,
                373815,
                373825,
                373837,
                373839,
                373841,
                373844,
                373845,
                373846,
                373847,
                373853,
                373854,
                373855,
                373862,
                373863,
                373864,
                373867,
                373880,
                373884,
                373885,
                373894,
                373895,
                373896,
                373910,
                373914,
                373918,
                373921,
                373924,
                373941,
                373946,
                373952,
                373953,
                373954,
                373955,
                373956,
                373963,
                373968,
                373969,
                373972,
                373974,
                373982,
                373986,
                373987,
                373989,
                373994,
                374002,
                374013,
                374026,
                374027,
                374033,
                374038,
                374040,
                374042,
                374043,
                374045,
                374048,
                374058,
                374064,
                374069,
                374081,
                374095,
                374097,
                374101,
                374103,
                374121,
                374125,
                374127,
                374130,
                374133,
                374138,
                374142,
                374144,
                374150,
                374152,
                374155,
                374168,
                374172,
                374174,
                374186,
                374190,
                374194,
                374195,
                374199,
                374203,
                374204,
                374213,
                374215,
                374221,
                374235,
                374241,
                374250,
                374251,
                374255,
                374260,
                374264,
                374267,
                374272,
                374275,
                374276,
                374277,
                374285,
                374292,
                374295,
                374297,
                374301,
                374302,
                374303,
                374306,
                374310,
                374316,
                374322,
                374329,
                374340,
                374346,
                374353,
                374354,
                374355,
                374369,
                374370,
                374373,
                374375,
                374377,
                374378,
                374379,
                374380,
                374384,
                374389,
                374390,
                374392,
                374393,
                374397,
                374399,
                374402,
                374405,
                374406,
                374419,
                374424,
                374426,
                374430,
                374434,
                374435,
                374447,
                374450,
                374453,
                374460,
                374462,
                374468,
                374470,
                374473,
                374485,
                374490,
                374499,
                374505,
                374507,
                374508,
                374515,
                374516,
                374530,
                374532,
                374537,
                374541,
                374545,
                374548,
                374557,
                374566,
                374567,
                374571,
                374581,
                374583,
                374585,
                374589,
                374592,
                374612,
                374617,
                374621,
                374625,
                374629,
                374630,
                374631,
                374635,
                374637,
                374638,
                374640,
                374649,
                374651,
                374652,
                374655,
                374667,
                374668,
                374670,
                374678,
                374689,
                374694,
                374695,
                374698,
                374701,
                374708,
                374711,
                374713,
                374724,
                374736,
                374738,
                374739,
                374752,
                374757,
                374759,
                374760,
                374762,
                374765,
                374776,
                374781,
                374782,
                374787,
                374794,
                374797,
                374799,
                374802,
                374808,
                374810,
                374811,
                374830,
                374837,
                374839,
                374845,
                374849,
                374864,
                374866,
                374879,
                374881,
                374888,
                374897,
                374898,
                374899,
                374902,
                374905,
                374906,
                374908,
                374918,
                374921,
                374925,
                374947,
                374948,
                374954,
                374955,
                374956,
                374957,
                374959,
                374963,
                374967,
                374969,
                374974,
                374977,
                374989,
                374992,
                375001,
                375003,
                375029,
                375067,
                375629,
                375646,
                375662,
                375765,
                375768,
                375783,
                375788,
                375792,
                375795,
                375801,
                375804,
                375810,
                375812,
                375815,
                375816,
                375819,
                375823,
                375826,
                375835,
                375853,
                375856,
                375857,
                375867,
                375871,
                375875,
                375880,
                375881,
                375882,
                375883,
                375887,
                375891,
                375893,
                375898,
                375899,
                375900,
                375908,
                375911,
                375921,
                375934,
                375937,
                375938,
                375941,
                375942,
                375952,
                375954,
                375964,
                375968,
                375974,
                375979,
                375981,
                375986,
                375996,
                375998,
                375999,
                376003,
                376007,
                376017,
                376026,
                376028,
                376034,
                376039,
                376040,
                376044,
                376045,
                376050,
                376053,
                376057,
                376061,
                376064,
                376065,
                376070,
                376071,
                376073,
                376083,
                376086,
                376088,
                376094,
                376104,
                376105,
                376110,
                376113,
                376123,
                376130,
                376132,
                376133,
                376138,
                376145,
                376147,
                376150,
                376157,
                376166,
                376172,
                376173,
                376178,
                376181,
                376182,
                376190,
                376203,
                376211,
                376213,
                376218,
                376219,
                376220,
                376225,
                376226,
                376227,
                376228,
                376235,
                376241,
                376245,
                376271,
                376272,
                376286,
                376290,
                376293,
                376303,
                376304,
                376312,
                376321,
                376335,
                376338,
                376340,
                376346,
                376352,
                376358,
                376362,
                376376,
                376379,
                376382,
                376383,
                376396,
                376401,
                376432,
                376435,
                376438,
                376446,
                376447,
                376449,
                376469,
                376479,
                376482,
                376487,
                376488,
                376494,
                376498,
                376502,
                376503,
                376505,
                376514,
                376521,
                376527,
                376530,
                376546,
                376551,
                376563,
                376569,
                376571,
                376572,
                376573,
                376587,
                376593,
                376595,
                376596,
                376604,
                376612,
                376614,
                376621,
                376622,
                376628,
                376631,
                376642,
                376650,
                376655,
                376662,
                376674,
                376677,
                376682,
                376684,
                376686,
                376692,
                376693,
                376696,
                376697,
                376698,
                376700,
                376718,
                376719,
                376726,
                376729,
                376733,
                376737,
                376749,
                376750,
                376762,
                376764,
                376765,
                376766,
                376767,
                376768,
                376772,
                376774,
                376780,
                376785,
                376786,
                376807,
                376812,
                376813,
                376816,
                376829,
                376832,
                376839,
                376842,
                376851,
                376852,
                376854,
                376855,
                376865,
                376880,
                376882,
                376884,
                376900,
                376904,
                376906,
                376909,
                376919,
                376920,
                376926,
                376927,
                376929,
                376930,
                376936,
                376942,
                376944,
                376952,
                376962,
                376967,
                376978,
                376981,
                376995,
                376998,
                377002,
                377008,
                377021,
                377022,
                377024,
                377026,
                377037,
                377040,
                377042,
                377046,
                377050,
                377069,
                377072,
                377081,
                377086,
                377091,
                377094,
                377097,
                377107,
                377128,
                377131,
                377135,
                377139,
                377140,
                377142,
                377143,
                377149,
                377158,
                377159,
                377168,
                377175,
                377179,
                377180,
                377182,
                377185,
                377190,
                377192,
                377200,
                377203,
                377205,
                377216,
                377229,
                377231,
                377244,
                377246,
                377255,
                377261,
                377269,
                377276,
                377279,
                377281,
                377293,
                377308,
                377315,
                377321,
                377324,
                377326,
                377330,
                377335,
                377338,
                377344,
                377347,
                377349,
                377350,
                377361,
                377364,
                377365,
                377375,
                377381,
                377382,
                377393,
                377396,
                377399,
                377404,
                377405,
                377414,
                377416,
                377419,
                377420,
                377432,
                377439,
                377440,
                377445,
                377447,
                377459,
                377463,
                377465,
                377467,
                377471,
                377474,
                377477,
                377487,
                377489,
                377504,
                377516,
                377524,
                377525,
                377528,
                377533,
                377537,
                377563,
                377565,
                377573,
                377582,
                377586,
                377588,
                377593,
                377598,
                377607,
                377612,
                377618,
                377622,
                377623,
                377625,
                377635,
                377638,
                377650,
                377657,
                377661,
                377666,
                377671,
                377674,
                377676,
                377683,
                377690,
                377691,
                377694,
                377703,
                377705,
                377715,
                377719,
                377726,
                377735,
                377736,
                377738,
                377751,
                377760,
                377763,
                377766,
                377773,
                377775,
                377782,
                377794,
                377803,
                377804,
                377814,
                377817,
                377818,
                377820,
                377823,
                377824,
                377827,
                377836,
                377838,
                377844,
                377850,
                377856,
                377866,
                377872,
                377882,
                377885,
                377903,
                377907,
                377909,
                377913,
                377920,
                377923,
                377933,
                377938,
                377955,
                377960,
                377975,
                377979,
                377989,
                377991,
                378011,
                378016,
                378020,
                378028,
                378038,
                378039,
                378049,
                378064,
                378077,
                378085,
                378093,
                378094,
                378097,
                378105,
                378111,
                378117,
                378127,
                378133,
                378139,
                378141,
                378144,
                378154,
                378158,
                378167,
                378178,
                378181,
                378184,
                378193,
                378195,
                378199,
                378200,
                378217,
                378221,
                378231,
                378238,
                378248,
                378252,
                378255,
                378257,
                378262,
                378263,
                378264,
                378271,
                378276,
                378297,
                378308,
                378313,
                378335,
                378339,
                378341,
                378349,
                378354,
                378356,
                378361,
                378367,
                378369,
                378375,
                378380,
                378390,
                378394,
                378400,
                378427,
                378431,
                378432,
                378435,
                378436,
                378438,
                378445,
                378453,
                378454,
                378457,
                378465,
                378466,
                378471,
                378474,
            ])
            ->with('eligibilityJob.targetPatient.ccda')
            ->with('user')
            ->without('user.roles.perms')
            ->orderByDesc('id')
            ->each(
                function ($enrollee) {
                    $this->warn("Start Enrollee[$enrollee->id]");
                    $tP = $enrollee->eligibilityJob->targetPatient;
                    $checkable = app(AthenaEligibilityCheckableFactory::class)->makeAthenaEligibilityCheckable(
                        $tP
                    );
                    $eJ = $checkable->createAndProcessEligibilityJobFromMedicalRecord();
                    $ccd = $checkable->getMedicalRecord();
                    $providerName = $enrollee->referring_provider_name = $ccd->referring_provider_name = $eJ->data['referring_provider_name'];
                    $provider = CcdaImporterWrapper::mysqlMatchProvider($providerName, $enrollee->practice_id);

                    if ( ! $provider) {
                        return;
                    }

                    $ccd->billing_provider_id = $enrollee->provider_id = $provider->id;

                    if ($enrollee->user) {
                        $enrollee->user->setBillingProviderId($provider->id);
                    }

                    if ($ccd->isDirty()) {
                        $ccd->save();
                    }
                    if ($eJ->isDirty()) {
                        $eJ->save();
                    }
                    if ($enrollee->isDirty()) {
                        $enrollee->save();
                        $this->line("Saving Enrollee[$enrollee->id]");
                    }
                },
                50
            );
    }
}
