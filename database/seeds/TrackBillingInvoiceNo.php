<?php

use Illuminate\Database\Seeder;

class TrackBillingInvoiceNo extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        \App\AppConfig::create([

            'config_key' => 'billing_invoice_count',
            'config_value' => 1163

        ]);
    }
}
