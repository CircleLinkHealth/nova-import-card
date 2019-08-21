<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

require __DIR__.'/../vendor/autoload.php';

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;

$hostname = gethostname();

//staging
//@todo: clean this up
if ('ip-10-0-1-17' === $hostname) {
    //keys need to be stored here
    //sample unix shell command to create this file:
    //if [ ! -d "/cryptdata/var/deploy/.cpm" ];then mkdir /cryptdata/var/deploy/.cpm; fi && echo '{"version":"latest","credentials":{"key":"","secret":"+K/v"},"region":"","bucket":""}' > /cryptdata/var/deploy/.cpm/cpm-staging-server-s3-credentials && chmod 600 /cryptdata/var/deploy/.cpm/cpm-staging-server-s3-credentials
    $keys = [
        'emr-sandbox-conc-keys.pem' => __DIR__.'/../resources/certificates/emr-sandbox-conc-keys.pem',
        'EMRDirectTestCA.pem'       => __DIR__.'/../resources/certificates/EMRDirectTestCA.pem',
        $hostname.'.env'            => __DIR__.'/../.env',
    ];

    //keys need to be stored here
    //sample unix shell command to create this file:
    //if [ ! -d "/cryptdata/var/deploy/.cpm" ];then mkdir /cryptdata/var/deploy/.cpm; fi && echo '{"version":"latest","credentials":{"key":"","secret":""},"region":"","bucket":""}' > /cryptdata/var/deploy/.cpm/cpm-production-server-s3-credentials && chmod 600 /cryptdata/var/deploy/.cpm/cpm-production-server-s3-credentials
    $filePath = '/cryptdata/var/deploy/.cpm/cpm-staging-server-s3-credentials';
} else {
    //Structure here is:
    //s3_file_key => local_path_to_save_file_to
    //this could be moved outside this script, and be passed in as an argument
    $keys = [
        'emr-direct-production-conc-key.pem' => __DIR__.'/../resources/certificates/emr-production-conc-keys.pem',
        'phiCertDirectRootCA.pem'            => __DIR__.'/../resources/certificates/phiCertDirectRootCA.pem',
        $hostname.'.env'                     => __DIR__.'/../.env',
    ];

    //keys need to be stored here
    //sample unix shell command to create this file:
    //if [ ! -d "/cryptdata/var/deploy/.cpm" ];then mkdir /cryptdata/var/deploy/.cpm; fi && echo '{"version":"latest","credentials":{"key":"","secret":""},"region":"","bucket":""}' > /cryptdata/var/deploy/.cpm/cpm-production-server-s3-credentials && chmod 600 /cryptdata/var/deploy/.cpm/cpm-production-server-s3-credentials
    $filePath = '/cryptdata/var/deploy/.cpm/cpm-production-server-s3-credentials';
}

$usage = "\n Please create a file with json credentials to run this script.
\n Store the file at `$filePath`";

if ( ! file_exists($filePath)) {
    throw new \Exception($usage);
}

$args = json_decode(file_get_contents($filePath), true);

try {
    $s3Client = new S3Client($args);

    foreach ($keys as $s3Key => $localPath) {
        echo "Putting $s3Key in $localPath";
        $result = $s3Client->getObject([
            'Bucket' => $args['bucket'],
            'Key'    => $s3Key,
            'SaveAs' => $localPath,
        ]);
    }

    echo 'Assets copied from S3.';
} catch (S3Exception $e) {
    echo $e->getMessage()."\n";
}
