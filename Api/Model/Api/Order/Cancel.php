<?php


namespace RetailOps\Api\Model\Api\Order;


class Cancel
{
    use \RetailOps\Api\Model\Api\Traits\Filter;

    protected $response;
    protected $status = 'success';
    protected $events = [];
    /**
     * @var \\RetailOps\Api\Model\Order\Status\History
     */
    protected $historyRetail;

    public function cancel($orderInfo)
    {
        try {
        $orderId = $this->getOrderId($orderInfo);
        $order = $this->orderRepository->get($orderId);
        if ($order->getId()) {
            if ($order->canUnhold()) {
                $order->unhold();
            }
            try{
                $this->historyRetail->setParentId($order->getId());
                $this->cancelOrder($order);
            } catch (\Exception $e) {
                $this->status = 'fail';
                $this->setEventsInfo($e);
                $this->historyRetail->setComment($e->getMessage());
            }finally{
                $this->historyRetail->setStatus($this->status);
                $this->historyRetail->setCreatedAt( \date('Y-m-d H:i:s'));
                $this->historyRetail->save();
            }
        }
        } catch (\Exception $e) {
            $this->status = 'fail';
            $this->setEventsInfo($e);

        }finally{
            $response = [];
            $response['status'] = $this->status;
            $response['events'] = $this->events;
            $this->response = $response;
            return $this->response;
        }
    }

    protected function setEventsInfo($e)
    {
        $event = [];
        $event['event_type'] = 'error';
        $event['code'] = (string)$e->getCode();
        $event['message'] = $e->getMessage();
        $event['diagnostic_data'] = $e->getFile();
        if (isset($orderId)) {
            $event['associations'] = [
                'identifier_type' => 'order_refnum',
                'identifier' => (string)$orderId];

                }
        $this->events[] = $event;
    }

    /**
     * @param $orderId
     */
    protected function getOrderId($orderInfo)
    {
        if (isset($orderInfo['channel_order_refnum'])) {
            return $orderInfo['channel_order_refnum'];
        } else {
            $this->logger->addError('Invalid map', (array)$orderInfo);
            throw new \LogicException(__('invalid map'));

        }
    }

    /**
     * Cancel constructor.
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \\RetailOps\Api\Logger\Logger $logger
     * @param \Magento\Framework\Api\SearchCriteria $searchCriteria
     * @param \Magento\Framework\Api\FilterFactory $filter
     * @param \Magento\Framework\Api\Search\FilterGroupFactory $filterGroup
     */
    public function __construct(\Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
                                \RetailOps\Api\Logger\Logger $logger,
                                \Magento\Framework\Api\SearchCriteria $searchCriteria,
                                \Magento\Framework\Api\FilterFactory $filter,
                                \Magento\Framework\Api\Search\FilterGroupFactory $filterGroup,
                                \RetailOps\Api\Model\Order\Status\History $historyRetail
    )
    {
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
        $this->searchCriteria = $searchCriteria;
        $this->filter = $filter;
        $this->filterGroup = $filterGroup;
        $this->historyRetail = $historyRetail;
    }

    /**
     * cancels an order
     *
     * @param   \Magento\Sales\Api\Data\OrderInterface $order
     * @returns \bool
     * @throws  \Magento\Framework\Exception\LocalizedException
     */
    private function cancelOrder($order)
    {
        if (!$order->canCancel()) {
            throw new LocalizedException(__('Order cannot be Canceled'));
        }

        $order->cancel();
        $order->save();
    }
}