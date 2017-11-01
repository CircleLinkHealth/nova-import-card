<?php

namespace App\CLH\CCD\ImportRoutine;

trait ExecutesImportRoutine
{
    public static function import($ccd, $validationStrategy, $parsingStrategy, $storageStrategy = null, $blogId = null, $userId = null)
    {
        $validator = null;
        $parser = null;
        $storage = null;
        $items = null;

        if (! isset($parsingStrategy) || ! isset($storageStrategy)) {
            abort('400', 'Parsing Strategy and Storage Strategy are required');
        }

        if (class_exists($validationStrategy)) {
            $validator = new $validationStrategy();
        }
        if (class_exists($parsingStrategy)) {
            $parser = new $parsingStrategy();
            $items = $parser->parse($ccd, $validator);
        }
        if (class_exists($storageStrategy)) {
            $storage = new $storageStrategy( $blogId, $userId );
            $storage->import($items);
        }
    }
}
