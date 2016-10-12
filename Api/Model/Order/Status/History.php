<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 23.9.16
 * Time: 17.17
 */

namespace RetailOps\Model\Order\Status;


class History extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init('\\RetailOps\Model\Resource\Order\Status\History');
    }
}