<?php

namespace Worldpay\Payments\Logger;
 
use Monolog\Logger as MonoLogger;
 
class Handler extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * Logging level
     * @var int
     */
    protected $loggerType = MonoLogger::DEBUG;
 
    /**
     * File name
     * @var string
     */
    protected $fileName = '/var/log/worldpay.log';
}