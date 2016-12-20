<?php
namespace RetailOps\Api\Model\ResourceModel\Queue;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'retailops_api_queue_id';
    protected function _construct()
    {
        $this->_init('RetailOps\Api\Model\Queue','RetailOps\Api\Model\ResourceModel\Queue');
    }
}
