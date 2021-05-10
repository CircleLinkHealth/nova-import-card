<?php


namespace CircleLinkHealth\LargePayloadSqsQueue;


class Constants
{
    /**
     * The maximum size that SQS can accept.
     */
    const MAX_SQS_SIZE_KB = 256;
    
    /**
     * The name of the custom SQS queue driver
     */
    const DRIVER    = 'sqs-large-payload';
    const S3_BUCKET = 'media';
}