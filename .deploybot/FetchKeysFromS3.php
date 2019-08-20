<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

require __DIR__.'/../vendor/autoload.php';

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;

//Structure here is:
//s3_file_key => local_path_to_save_file_to
//this could be moved outside this script, and be passed in as an argument
$keys = [
    'emr-direct-production-conc-key.pem' => __DIR__.'/../resources/certificates/emr-production-conc-keys.pem',
    'phiCertDirectRootCA.pem'            => __DIR__.'/../resources/certificates/phiCertDirectRootCA.pem',
];

//keys need to be stored here
//sample unix shell command to create this file:
//if [ ! -d "/cryptdata/var/deploy/.cpm" ];then mkdir /cryptdata/var/deploy/.cpm; fi && echo '{"version":"latest","credentials":{"key":"","secret":""},"region":"","bucket":""}' > /cryptdata/var/deploy/.cpm/cpm-production-server-s3-credentials && chmod 600 /cryptdata/var/deploy/.cpm/cpm-production-server-s3-credentials
$filePath = '/cryptdata/var/deploy/.cpm/cpm-production-server-s3-credentials';

$usage = "\n Please create a file with json credentials to run this script.
\n Store the file at `$filePath`";

if ( ! file_exists($filePath)) {
    throw new \Exception($usage);
}

$args = json_decode(file_get_contents($filePath), true);

try {
    $s3Client = new S3Client($args);

    foreach ($keys as $s3Key => $localPath) {
        $result = $s3Client->getObject([
            'Bucket' => $args['bucket'],
            'Key'    => $s3Key,
            'SaveAs' => $localPath,
        ]);
    }
} catch (S3Exception $e) {
    echo $e->getMessage()."\n";
}
