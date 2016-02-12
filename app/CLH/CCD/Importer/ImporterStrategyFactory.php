<?php

namespace App\CLH\CCD\Importer;


use App\CLH\Contracts\CCD\ParsingStrategy;
use App\CLH\Contracts\CCD\StorageStrategy;
use App\CLH\Contracts\CCD\ValidationStrategy;
use App\ParsedCCD;

class ImporterStrategyFactory
{
    public static function make($ccd, $validationStrategy, $parsingStrategy, $storageStrategy, $blogId, $userId)
    {
        $validator = null;
        $parser = null;
        $storage = null;
        $items = null;

        if (empty($parsingStrategy || $storageStrategy)) abort('400', 'Parsing Strategy and Storage Strategy are required');

        if ( class_exists( $validationStrategy ) ) {
            $validator = new $validationStrategy();
        }
        if ( class_exists( $parsingStrategy ) ) {
            $parser = new $parsingStrategy();
            $items = $parser->parse( $ccd, $validator );
        }
        if ( class_exists( $storageStrategy ) ) {
            $storage = new $storageStrategy( $blogId, $userId );
            $storage->import( $items );
        }


    }

}