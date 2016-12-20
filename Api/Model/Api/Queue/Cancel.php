<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 6.12.16
 * Time: 11.22
 */

namespace RetailOps\Api\Model\Api\Queue;

use RetailOps\Api\Api\Queue\QueueInterface;
use RetailOps\Api\Model\QueueInterface as Queue;
class Cancel implements QueueInterface
{
    use \RetailOps\Api\Model\Api\Traits\FullFilter;

    protected $response;
    protected $status = 'success';
    protected $events = [];
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \RetailOps\Api\Model\QueueFactory
     */
    protected $queueFactory;

    /**
     * @var \RetailOps\Api\Model\QueueRepository
     */
    protected $queueRepository;
    /**
     * @return Queue
     */
    public function setToQueue($message, \Magento\Sales\Api\Data\OrderInterface $order, $type=Queue::CANCEL_TYPE)
    {
        /**
         * @var \RetailOps\Api\Model\Queue $queue
         */
        $queue = $this->queueFactory->create();
        $queue->setMessage($message);
        $queue->setQueueType($type);
        $queue->setOrderId($order->getIncrementId());
         return $this->queueRepository->save($queue);

    }

    /**
     * @param $id
     * @param $type
     * @return mixed
     */
    public function getFromQueue($id)
    {
       return $this->queueRepository->getById($id);
    }


    public function cancel($data)
    {
        $orderId = $this->getOrderIdByIncrement($data['channel_order_refnum']);
        /**
         * @var \Magento\Sales\Api\Data\OrderInterface $order
         */
        $order = $this->orderRepository->get($orderId);

        if ($order->getId()) {
            $message = $this->getMessage($order);
            $this->setToQueue($message,$order);
        }
        $response['status'] = $this->status;
        $response['events'] = $this->events;
        return $this->response = $response;

    }

    public function getMessage( \Magento\Sales\Api\Data\OrderInterface $order)
    {
        $incrementId = $order->getIncrementId();
        return sprintf(__('Cancel order number: %s'), $incrementId);
    }

    public function __construct(\Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
                                \RetailOps\Api\Model\QueueFactory $queue,
                                \RetailOps\Api\Model\QueueRepository $queueRepository)
    {
        $this->orderRepository = $orderRepository;
        $this->queueFactory = $queue;
        $this->queueRepository = $queueRepository;
    }

}