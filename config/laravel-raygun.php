<?php

return [
    'apiKey'    => env('RAYGUN_API_KEY', ''),
    //Do not enable because it Raygun's SDK will use PHP `exec()`, which is disabled on Production servers
    'async'     => false,
    'debugMode' => false,
];