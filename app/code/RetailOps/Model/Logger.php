<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 26.9.16
 * Time: 12.57
 */

namespace Shiekhdev\RetailOps\Model;


class Logger  extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init('\Shiekhdev\RetailOps\Model\Resource\Logger');
    }
}