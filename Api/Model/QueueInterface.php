<?php
namespace RetailOps\Api\Model;
interface QueueInterface 
{
    const ID = 'retailops_api_queue_id';
    const MESSAGE = 'message';
    const ACTIVE = 'is_active';
    const QUEUE_TYPE = 'queue_type';
    const ORDER_Id = 'order_increment_id';
    const CANCEL_TYPE = 1;
    /**
     * @param string $message
     * @return $this
     */
    public function setMessage($message);

    /**
     * @param bool $active
     * @return $this
     */
    public function setIsActive(bool $active);

    /**
     * @param integer $type
     * @return mixed
     */
    public function setQueueType($type);

    /**
     * @return string|null
     */
    public function getMessage();

    /**
     * @return bool
     */
    public function getIsActive();

    /**
     * @return integer
     */
    public function getQueueType();

    /**
     * @param string $orderInc
     * @return $this
     */
    public function setOrderId($orderInc);

    /**
     * @return string
     */
    public function getOrderId();
}