<?php

/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 4/27/16
 * Time: 5:16 PM
 */
class CpmBiometricsSeeder
{
    public function run()
    {
        $entities[] = [
            'name' => 'Weight',
            'type' => \App\Models\CPM\Biometrics\CpmWeight::class,
        ];

        $entities[] = [
            'name' => 'Blood Pressure',
            'type' => \App\Models\CPM\Biometrics\CpmBloodPressure::class,
        ];

        $entities[] = [
            'name' => 'Blood Sugar',
            'type' => \App\Models\CPM\Biometrics\CpmBloodSugar::class,
        ];

        $entities[] = [
            'name' => 'Smoking (# per day)',
            'type' => \App\Models\CPM\Biometrics\CpmSmoking::class,
        ];

        foreach ($entities as $entity) {
            \App\Models\CPM\CpmBiometrics::updateOrCreate($entity);
        }
    }
}