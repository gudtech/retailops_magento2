<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 9.9.16
 * Time: 7.57
 */

namespace RetailOps\Api\Logger;


class Base extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * Logging level
     * @var int
     */
    protected $loggerType = \Monolog\Logger::INFO;

    /**
     * File name
     * @var string
     */
    protected $fileName = '/var/log/retailops.log';
}