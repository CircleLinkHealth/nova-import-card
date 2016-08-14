<?php

use Illuminate\Database\Seeder;
use App\Models\CCD\CcdInsurancePolicy;

class ReImportVCReport extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $demographigLogs = \App\CLH\CCD\ItemLogger\CcdDemographicsLog::distinct()->get();

        foreach ($demographigLogs as $ccdDemogrLog)
        {
            $ccda = \App\Models\CCD\Ccda::find($ccdDemogrLog->ccda_id);

            if (empty($ccda)) continue;

            $jsonCcda = json_decode( $ccda->json );

            if (empty($jsonCcda)) continue;

            //Save Ethnicity
            $ccdDemogrLog->ethnicity = $jsonCcda->demographics->ethnicity;
            $ccdDemogrLog->race = $jsonCcda->demographics->race;
            $ccdDemogrLog->save();

            //Save Insurance
            if (!empty($jsonCcda->payers)) {
                foreach ($jsonCcda->payers as $payer) {

                    if (empty($payer->insurance)) continue;

                    //make sure we don't have any insurance info
                    $insurance = CcdInsurancePolicy::whereCcdaId($ccda->id)->first();
                    if ($insurance) continue;

                    CcdInsurancePolicy::create([
                        'ccda_id' => $ccda->id,
                        'patient_id' => $ccda->patient_id,
                        'name' => $payer->insurance,
                        'type' => $payer->policy_type,
                        'policy_id' => $payer->policy_id,
                        'relation' => $payer->relation,
                        'subscriber' => $payer->subscriber,
                        'approved' => false,
                    ]);
                }
            }
            
            $this->command->info("ReImported CCD with ID {$ccda->id}");
        }

        $this->command->info('CCDs re-imported');
    }
}
