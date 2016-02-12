<?php

return [
    'sections' => [
//        [
//            'name' => '',
//            'validatorsIds' => [],
//            'parsersIds' => [],
//            'storageIds' => [],
//        ],
        [
            'name' => 'Allergies List',
            'parsersIds' => [0],
            'storageIds' => [0],
        ],
        [
            'name' => 'Problems List',
            'parsersIds' => [1],
            'storageIds' => [2],
        ],
        [
            'name' => 'Problems To Monitor',
            'parsersIds' => [2],
            'storageIds' => [3],
        ],
        [
            'name' => 'Medications List',
            'parsersIds' => [3, 4],
            'storageIds' => [1],
        ],
    ],
];