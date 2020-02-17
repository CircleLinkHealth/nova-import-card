<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 2/16/20
 * Time: 11:19 PM
 */

namespace App\Services\PhiMail\Incoming\Handlers;

use App\DirectMailMessage;
use App\Services\PhiMail\Incoming\IncomingDMMimeHandlerInterface;
use App\Services\PhiMail\ShowResult;

abstract class BaseHandler implements IncomingDMMimeHandlerInterface
{
    /**
     * @var DirectMailMessage
     */
    protected $dm;
    /**
     * @var ShowResult
     */
    protected $showRes;
    
    public function __construct(DirectMailMessage &$dm, ShowResult $showRes)
    {
        $this->dm = $dm;
        $this->showRes = $showRes;
    }
}