<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define(App\User::class, function (Faker\Generator $faker) {
    return [
        'display_name' => $faker->name,
        'user_email' => $faker->safeEmail,
        'password' => bcrypt(str_random(10)),
    ];
});

$factory->define(\App\Models\CPM\Biometrics\CpmWeight::class, function (Faker\Generator $faker) {
    
    $starting = rand(300, 450);
    
    return [
        'monitor_changes_for_chf' => $faker->boolean(),
//        'patient_id' => '', this has to be passed in when calling the factory
        'starting' => $starting,
        'target' => $starting - rand(100, 150),
    ];
});


$factory->define(\App\Models\CPM\Biometrics\CpmBloodPressure::class, function (Faker\Generator $faker) {

    $systolicStarting = rand(110, 140);
    $diastolicStarting = rand(60, 70);

    $systolicTarget = $systolicStarting - rand(10, 20);
    $diastolicTarget = $diastolicStarting - rand(15, 20);

    return [
//        'patient_id' => '', this has to be passed in when calling the factory
        'starting' => "$systolicStarting/$diastolicStarting",
        'target' => "$systolicTarget/$diastolicTarget",
    ];
});

$factory->define(\App\Models\CPM\Biometrics\CpmBloodSugar::class, function (Faker\Generator $faker) {

    return [
//        'patient_id' => '', this has to be passed in when calling the factory
        'starting' => rand(140, 300),
        'starting_a1c' => rand('6.7', '13.8'),
    ];
});