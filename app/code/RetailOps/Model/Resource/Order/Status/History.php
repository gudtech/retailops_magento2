<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 23.9.16
 * Time: 17.19
 */

namespace Shiekhdev\RetailOps\Model\Resource\Order\Status;


class History extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('retailops/order_status_history', 'id');
    }
}