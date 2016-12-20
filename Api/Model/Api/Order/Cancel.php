<?php


namespace RetailOps\Api\Model\Api\Order;


class Cancel
{
    use \RetailOps\Api\Model\Api\Traits\Filter;

    /**
     * @var \RetailOps\Api\Api\Services\CreditMemo\CreditMemoHelperInterface
     */
    protected $creditMemoHelper;
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
                $this->cancelOrder($order);
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
            return $this->getOrderIdByIncrement($orderInfo['channel_order_refnum']);
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
                                \RetailOps\Api\Model\Order\Status\History $historyRetail,
                                \RetailOps\Api\Api\Services\CreditMemo\CreditMemoHelperInterface $creditMemoHelper
    )
    {
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
        $this->searchCriteria = $searchCriteria;
        $this->filter = $filter;
        $this->filterGroup = $filterGroup;
        $this->historyRetail = $historyRetail;
        $this->creditMemoHelper = $creditMemoHelper;
    }

    /**
     * cancels an order
     *
     * @param   \Magento\Sales\Api\Data\OrderInterface $order
     * @returns \bool
     * @throws  \Magento\Framework\Exception\LocalizedException
     */
    private function cancelOrder(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        if (!$order->canCancel()) {
//            throw new \LogicException(__('Order cannot be Canceled'));
           return  $this->allRefund($order);
        }

        $order->cancel();
        $order->save();
    }

    private function allRefund(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        $shippingRefund = $order->getShippingAmount() - $order->getShippingDiscountAmount();
        $this->creditMemoHelper->setShippingAmount($shippingRefund);
        $this->creditMemoHelper->create($order, []);
    }
}