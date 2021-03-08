<?php


namespace CircleLinkHealth\SelfEnrollment\Console\Commands;


class CommandHelpers
{
    public static function getEnrolleeIds(?array $enrolleeIds):array
    {
        if ($enrolleeIds){
            return explode(',',collect($enrolleeIds)->first());
        }

        return [];
    }
}