<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 11.10.16
 * Time: 15.14
 */

namespace RetailOps\Api\Model\Resource\Collection\InventoryHistory;


class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            '\RetailOps\Api\Model\InventoryHistory',
            '\RetailOps\Api\Model\Resource\InventoryHistory'
        );
    }
}