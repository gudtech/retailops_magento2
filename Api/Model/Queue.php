<?php
namespace RetailOps\Api\Model;

use RetailOps\Api\Model\QueueInterface;
class Queue extends \Magento\Framework\Model\AbstractModel implements QueueInterface, \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'retailops_api_queue';
    protected $_idFieldName = self::ID;

    protected function _construct()
    {
        $this->_init('RetailOps\Api\Model\ResourceModel\Queue');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @param string $message
     * @return $this
     */
    public function setMessage($message)
    {
        return parent::setData(self::MESSAGE, $message);
    }

    /**
     * @param bool $active
     * @return $this
     */
    public function setIsActive(bool $active)
    {
        return parent::setData(self::ACTIVE, $active);
    }

    /**
     * @param integer $type
     * @return mixed
     */
    public function setQueueType($type = QueueInterface::CANCEL_TYPE)
    {
        return parent::setData(self::QUEUE_TYPE, $type);
    }

    /**
     * @return string|null
     */
    public function getMessage()
    {
        return parent::getData(self::MESSAGE);
    }

    /**
     * @return bool
     */
    public function getIsActive()
    {
        return parent::getData(self::ACTIVE);
    }

    public function setOrderId($orderInc)
    {
        return parent::setData(self::ORDER_Id, $orderInc);
    }

    public function getOrderId()
    {
        return parent::getData(self::ORDER_Id);
    }

    /**
     * @return integer
     */
    public function getQueueType()
    {
        return parent::getData(self::QUEUE_TYPE);
    }
}
