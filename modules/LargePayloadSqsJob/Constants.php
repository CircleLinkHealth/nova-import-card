<?php


namespace CircleLinkHealth\LargePayloadSqsJob;


class Constants
{
    /**
     * The maximum size that SQS can accept in bytes (256 * 1024).
     */
    const MAX_SQS_SIZE_BYTES = 200000;
    
    /**
     * The name of the custom SQS queue driver
     */
    const DRIVER    = 'sqs-large-payload';
    const S3_BUCKET = 'media';
}