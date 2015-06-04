<?php namespace database\seeds;

use App\Location;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class LocationTableSeeder extends Seeder {

    public function run()
    {
        DB::table('locations')->delete();

        $locationSeedData = [
            0 => [
                'name' => 'SACFAMILY',
                'phone' => '2036747135',
                'address_line_1' => '1 main st',
                'city' => 'Stamford',
                'postal_code' => '06905',
                'billing_code' => '1000',
                'location_code' => '1000',

                'children' => [
                    [
                        'name' => 'Location01',
                        'phone' => '2035390765',
                        'address_line_1' => '1234 Summer Street, 6th FL',
                        'address_line_2' => 'MedAdherence',
                        'city' => 'Stamford',
                        'postal_code' => '01234',
                        'billing_code' => '1001',
                        'location_code' => '1001',
                    ],
                    [
                        'name' => 'Location02',
                        'phone' => '2035390765',
                        'address_line_1' => '1234 Summer Street, 6th FL',
                        'address_line_2' => 'MedAdherence',
                        'city' => 'Stamford',
                        'postal_code' => '01234',
                        'billing_code' => '10032',
                        'location_code' => '1002',
                    ],
                ]
            ],
            1 => [
                'name' => 'Crisfield',
                'phone' => '2035390765',
                'address_line_1' => '1234 Summer Street, 6th FL',
                'address_line_2' => 'MedAdherence',
                'city' => 'Stamford',
                'postal_code' => '01234',
                'billing_code' => '1000',
                'location_code' => '1000',
            ],
            2 => [
                'name' => 'Yale',
                'phone' => '2035390765',
                'address_line_1' => '1234 Summer Street, 6th FL',
                'address_line_2' => 'MedAdherence',
                'city' => 'Stamford',
                'postal_code' => '01234',
                'billing_code' => '1000',
                'location_code' => '1000',
                'children' => [
                    [
                        'name' => 'Yale Cancer',
                        'phone' => '2035390765',
                        'address_line_1' => '1234 Summer Street, 6th FL',
                        'address_line_2' => 'MedAdherence',
                        'city' => 'Stamford',
                        'postal_code' => '01234',
                        'billing_code' => '1001',
                        'location_code' => '1001',
                    ],

                    [
                        'name' => 'Yale Health',
                        'phone' => '2035390765',
                        'address_line_1' => '1234 Summer Street, 6th FL',
                        'address_line_2' => 'MedAdherence',
                        'city' => 'Stamford',
                        'postal_code' => '01234',
                        'billing_code' => '1002',
                        'location_code' => '1002',
                        'children' => [
                            [
                                'name' => 'Yale Health NW',
                                'phone' => '2035390765',
                                'address_line_1' => '1234 Summer Street, 6th FL',
                                'address_line_2' => 'MedAdherence',
                                'city' => 'Stamford',
                                'postal_code' => '01234',
                                'billing_code' => '1003',
                                'location_code' => '1003',
                            ],
                            [
                                'name' => 'Yale Health NE',
                                'phone' => '2035390765',
                                'address_line_1' => '1234 Summer Street, 6th FL',
                                'address_line_2' => 'MedAdherence',
                                'city' => 'Stamford',
                                'postal_code' => '01234',
                                'billing_code' => '1004',
                                'location_code' => '1004',
                            ],
                        ]
                    ],
                ]
            ]
        ];

        Location::createFromArray($locationSeedData);

    }

}