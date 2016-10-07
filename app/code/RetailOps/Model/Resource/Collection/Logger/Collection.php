<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 26.9.16
 * Time: 13.03
 */

namespace Shiekhdev\RetailOps\Model\Resource\Collection\Logger;


class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
{
    $this->_init(
        'Shiekhdev\RetailOps\Model\Logger',
        'Shiekhdev\RetailOps\Model\Resource\Logger'
    );
}
}