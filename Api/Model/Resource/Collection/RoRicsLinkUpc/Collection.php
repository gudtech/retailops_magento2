<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 21.10.16
 * Time: 10.04
 */

namespace RetailOps\Api\Model\Resource\Collection\RoRicsLinkUpc;


class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            '\RetailOps\Api\Model\RoRicsLinkUpc',
            '\RetailOps\Api\Model\Resource\RoRicsLinkUpc'
        );
    }
}