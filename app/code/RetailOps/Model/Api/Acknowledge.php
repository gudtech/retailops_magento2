<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 22.9.16
 * Time: 14.27
 */

namespace Shiekhdev\RetailOps\Model\Api;


class Acknowledge
{
    use Traits\Filter;
    /**
     * @var array
     */
    protected $orderIds =[];

    /**
     * In this array we save data, such as
     * ['magento_order_id'=>retailops_order_id]
     *
     * @var array
     */
    protected $linkOrderRetail;

    /**
     * Array where set information about error for retailOps response
     *
     * @var array
     */
    protected $events = [];

    /**
     * Acknowledge constructor.
     *
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Shiekhdev\RetailOps\Logger\Logger $logger
     * @param \Magento\Framework\Api\SearchCriteria $searchCriteria
     */
    public function __construct(\Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Shiekhdev\RetailOps\Logger\Logger $logger,
        \Magento\Framework\Api\SearchCriteria $searchCriteria,
        \Magento\Framework\Api\FilterFactory $filter,
        \Magento\Framework\Api\Search\FilterGroupFactory $filterGroup
    )
    {
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
        $this->searchCriteria = $searchCriteria;
        $this->filter = $filter;
        $this->filterGroup = $filterGroup;
    }

    /**
     * @param array $orders
     * @return array
     * @throws \LogicException
     */
    public function setOrderNumbers($orders)
    {
        try {
            $orderIds = $this->getOrderIds($orders);
            if (!count($orderIds)) {
                throw new \LogicException(__('Don\'t have any numbers of orders'));
            }
            $filter = $this->createFilter('entity_id', 'in', array_keys($orderIds));
            $this->searchCriteria->setFilterGroups([$this->createFilterGroups([$filter])]);
            $result = $this->orderRepository->getList($this->searchCriteria);
            if ($result) {
                foreach ($result as $order) {
                    if (isset($this->orderIds[$order->getId()])) {
                        $order->setData('retailops_send_status', 1);
                        if (isset($this->linkOrderRetail[$order->getId()])) {
                            $order->setData('retailops_order_id', $this->linkOrderRetail[$order->getId()]);
                        }
                        $order->save();
                        unset($this->orderIds[$order->getId()]);
                    }

                }
                //if stay order_id, seems we don't have this orders in our system
                if(count($this->orderIds)) {
                    $this->setEvent('warning', null, __('Seems we don\'t have this orders'),
                        'order_refnum', array_keys($this->orderIds));
                }
            }
        }catch (\Exception $e){
            $event = [];
            $event['event_type'] = 'error';
            $event['code'] = $e->getCode();
            $event['message'] = $e->getMessage();
            $event['diagnostic_data'] = $e->getTrace();
            if (isset($order)) {
                $event['associations'] = [
                    'identifier_type' => 'order_refnum',
                    'identifier' => $order->getId()
                ];
            }
            $this->logger->addError('Error in acknowledge retailops', $event);
            $this->events = $event;
        }
        finally{
            return count($this->events) ? $this->events : (object) null;
        }
    }

    /**
     * @param string $eventType
     * @param string|null $code
     * @param string|null $message
     * @param string $identifierType
     * @param array|null $identifiers
     */
    protected function setEvent($eventType, $code, $message, $identifierType, $identifiers)
    {
        $event = [];
        $event['event_type'] = $eventType;
        $event['code'] = $code;
        $event['message'] = $message;
        if($identifierType !== null && is_array($identifiers)) {
            $event['associations'] = [];
            foreach ( $identifiers as $identifier) {
                $event['associations'][] = [
                    'identifier_type' => $identifierType,
                    'identifier' => $identifier
                ];
            }
        }
        $this->events = $event;
    }

    /**
     * @param  array $orders
     * @return array
     */
    protected function getOrderIds($orders)
    {
        if (!is_array($orders) || !count($orders)) {
            throw new \LogicException(__('Don\'t have any order\'s id in resquest'));
        }
        foreach ($orders as $order) {
            if (isset($order['channel_order_refnum'])) {
                $this->orderIds[$order['channel_order_refnum']] = 1;
                if (isset($order['retailops_order_id'])) {
                    $this->linkOrderRetail[$order['channel_order_refnum']] = $order['retailops_order_id'];
                }
            }
        }
        return $this->orderIds;
    }
}