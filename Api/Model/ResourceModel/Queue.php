<?php
namespace RetailOps\Api\Model\ResourceModel;
class Queue extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('retailops_api_queue','retailops_api_queue_id');
    }
}
