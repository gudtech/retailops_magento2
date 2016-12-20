<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 23.9.16
 * Time: 12.36
 */

namespace RetailOps\Api\Model\Api\Traits;


use Magento\Framework\App\ObjectManager;
trait Filter
{
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \RetailOps\Api\Logger\Logger
     */
    protected $logger;

    /**
     * @var \Magento\Framework\Api\SearchCriteria
     */
    protected $searchCriteria;

    /**
     * @var \Magento\Framework\Api\Filter
     */
    protected $filter;

    /**
     * @var \Magento\Framework\Api\Search\FilterGroup
     */
    protected $filterGroup;
    /**
     * @param array $filters
     * @return mixed
     */
    public function createFilterGroups( array $filters)
    {
        /**
         * @var  \Magento\Framework\Api\Search\FilterGroup
         */
        $filterGroup = $this->filterGroup->create();
        $filterGroup->setFilters($filters);
        return $filterGroup;
    }

    /**
     * @param $field
     * @param $operator
     * @param $value
     * @return \Magento\Framework\Api\Filter
     */
    public function createFilter($field, $operator, $value)
    {
        $filter = $this->filter->create();
        $filter->setField($field)
            ->setConditionType($operator)
            ->setValue($value);
        return $filter;
    }

    /**
     * @param string|int $order
     * @return string|null
     */
    public function getOrderIdByIncrement($orderInc)
    {
        $orders[$orderInc] = 1;
        $ordersId = array_keys($this->setOrderIdByIncrementId($orders));
        if(!is_array($ordersId) || !count($ordersId)) {
            throw new \LogicException(__('This increment id doesn\'t exists'));
        }
        $orderId = reset($ordersId);
        return $orderId;
    }

    /**
     * @param $orders
     */
    public function setOrderIdByIncrementId($orders)
    {
        $resource = ObjectManager::getInstance()->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $existsOrders = [];
        $template = 'increment_id IN (%s)';
        $orderKeys = array_keys($orders);
        array_walk($orderKeys, [$this,'addQuote']);
        $bind = join($orderKeys,',');
        $where = sprintf($template, $bind);
        $select = $connection->select()->from('sales_order',['entity_id', 'increment_id'])
            ->where($where);


        $result = $connection->fetchAll($select, []);
        if (count($result)) {
            foreach ($result as $row) {
                foreach ($orders as $key=>$order) {
                    if ((string)$key === (string)$row['increment_id']) {
                        $existsOrders[$row['entity_id']] = $order;
                    }
                }
            }
        }
        return $existsOrders;

    }

    public function addQuote($item)
    {
        return '`'.$item.'`';
    }
}