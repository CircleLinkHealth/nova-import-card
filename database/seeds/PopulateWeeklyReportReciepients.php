<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\Customer\Entities\Practice;
use Illuminate\Database\Seeder;

class PopulateWeeklyReportReciepients extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $practicesToSendTo = [
            'carolina-medical-associates' => 'joereddy@icloud.com, jillmclain@yahoo.com',

            'clinicalosangeles' => 'archambw@gmail.com, deysideiglesias@yahoo.com',
            'elmwood'           => 'tim@totalofficeconsultingllc.com, Justgeorge.ad@gmail.com, officemgr@tabernaclefp.com, jdpatel@elmwoodfp.com, ppatel@elmwoodfp.com, hshah@elmwoodfp.com',

            'tabernacle' => 'tim@totalofficeconsultingllc.com, Justgeorge.ad@gmail.com, officemgr@tabernaclefp.com, jdpatel@elmwoodfp.com, ppatel@elmwoodfp.com, hshah@elmwoodfp.com',

            'envision'                            => 'cretherford@envisionmedicalgroup.com',
            'mazhar'                              => 'tim@totalofficeconsultingllc.com, spatel@drsalmamazhar.com',
            'middletownmedical'                   => 'lorim@middletownmedical.com, trish.h@middletownmedical.com',
            'montgomery'                          => 'montmed@gmail.com',
            'nestor'                              => 'archambw@gmail.com, gnestor@tampabay.rr.com',
            'rocky-mountain-health-centers-south' => 'tina@rmhcsouth.com',
            'upg'                                 => 'srhyman@gmail.com, StuartBaugher@outlook.com, jhyman@northwell.edu, jhyman@upg.com, jmignola@upg.com',
            'urgent-medical-care-pc'              => //'Urgent Medical Care P.C. (Neuman)'

            'jgordon@northshoremd.com, jonline87@gmail.com, jolexa92@aol.com',
            'quest-medical-care-pc' => //'Quest (NY)

            'marilyn@questmedicalcenter.com',
            'premier-heart-and-vein-care' => //premier vein

            'hmorin@premierheartandveincare.com',
            'river-city' => 'hmorin@premierheartandveincare.com',
        ];

        foreach ($practicesToSendTo as $key => $value) {
            $practice = Practice::whereName($key)->first();

            if (null != $practice) {
                $practice->weekly_report_recipients = $value;
                $practice->save();
            }
        }
    }
}
