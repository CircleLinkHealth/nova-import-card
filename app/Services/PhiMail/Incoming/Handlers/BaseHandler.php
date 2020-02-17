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

abstract class BaseHandler implements IncomingDMMimeHandlerInterface
{
    /**
     * @var DirectMailMessage
     */
    protected $dm;
    /**
     * @var string
     */
    protected $attachmentData;
    
    public function __construct(DirectMailMessage &$dm, string $attachmentData)
    {
        $this->dm             = $dm;
        $this->attachmentData = $attachmentData;
    }
}