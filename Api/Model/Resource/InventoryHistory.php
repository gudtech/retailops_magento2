<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 11.10.16
 * Time: 13.55
 */

namespace RetailOps\Api\Model\Resource;


class InventoryHistory extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('retailops/inventory_history', 'id');
    }
}