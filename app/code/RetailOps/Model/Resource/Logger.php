<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 26.9.16
 * Time: 13.01
 */

namespace Shiekhdev\RetailOps\Model\Resource;


class Logger extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
         $this->_init('retailops/order_logger', 'id');
    }
}