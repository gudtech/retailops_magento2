<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 10.9.16
 * Time: 9.30
 */

namespace RetailOps\Api\Model\Product;


class Collection extends \Magento\Catalog\Model\ResourceModel\Collection\AbstractCollection
{
    protected function _construct()
    {
            $this->_init('Magento\Catalog\Model\Product', 'Magento\Catalog\Model\ResourceModel\Product');
    }
}