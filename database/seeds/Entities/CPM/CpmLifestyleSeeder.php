<?php

/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 4/21/16
 * Time: 11:44 AM
 */
class CpmLifestyleSeeder extends \Illuminate\Database\Seeder
{
    public function run()
    {
        $entities[] = [
            'name' => 'Healthy Diet',
        ];

        $entities[] = [
            'name' => 'Low Salt Diet',
        ];

        $entities[] = [
            'name' => 'Diabetic Diet',
        ];

        $entities[] = [
            'name' => 'Exercise',
        ];

        foreach ($entities as $entity) {
            \App\Entities\CPM\CpmLifestyle::updateOrCreate($entity);
        }
    }

}