<?php

use Illuminate\Database\Seeder;

class AddsCLHPPPMToPractices extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            'carolina-medical'           => '22',
            'clinicalosangeles'          => '22',
            'elmwood'                    => '21',
            'tabernacle'                 => '21',
            'envision'                   => '23',
            'mazhar'                     => '23',
            'middletownmedical'          => '24',
            'monheit'                    => '26',
            'montgomery'                 => '28',
            'nestor'                     => '23',
            'rockymountainhealthcenters' => '26',
            'upg'                        => '19',
        ];

        foreach ($data as $key => $value) {

            $p = \App\Practice::whereName($key)->first();
            $p->clh_pppm = $value;
            $p->save();

        }
    }
}
