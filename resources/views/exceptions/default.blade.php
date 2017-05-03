<?php

echo $exception->getMessage() . PHP_EOL . PHP_EOL;

//Get the route
if ($url) {
    echo "URL: {$method}@{$url}" . PHP_EOL . PHP_EOL;
}

//Get the User
if ($user) {
    echo "User: #{$user->id}" . PHP_EOL . PHP_EOL;
}

//Exception
echo get_class($exception) . ":{$exception->getFile()}:{$exception->getLine()}" . PHP_EOL . PHP_EOL;

//Input
if (!empty($input)) {
    echo "Data: " . json_encode($input) . PHP_EOL . PHP_EOL;
}

//Trace
echo PHP_EOL . "Trace: {$exception->getTraceAsString()}";
