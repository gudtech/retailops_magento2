<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 23.9.16
 * Time: 17.26
 */

namespace Shiekhdev\RetailOps\Model\Collection\Order\Status\History;


class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            'Shiekhdev\RetailOps\Model\Order\Status\History',
            'Shiekhdev\RetailOps\Model\Resource\Order\Status\History'
        );
    }
}