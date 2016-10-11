<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 23.9.16
 * Time: 17.26
 */

namespace \RetailOps\Api\Model\Collection\Order\Status\History;


class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            '\RetailOps\Api\Model\Order\Status\History',
            '\RetailOps\Api\Model\Resource\Order\Status\History'
        );
    }
}