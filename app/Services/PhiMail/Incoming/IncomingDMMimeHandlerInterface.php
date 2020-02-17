<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 2/16/20
 * Time: 11:14 PM
 */

namespace App\Services\PhiMail\Incoming;


use App\DirectMailMessage;
use App\Services\PhiMail\ShowResult;

interface IncomingDMMimeHandlerInterface
{
    public function __construct(DirectMailMessage &$dm, ShowResult $showRes);
    
    public function handle();
}