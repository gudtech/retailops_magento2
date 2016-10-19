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
     * @param $orders
     */
    public function setOrderIdByIncrementId(&$orders)
    {
        $resource = ObjectManager::getInstance()->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $select = $connection->select()->from('sales_order',['entity_id', 'increment_id'])
            ->where('increment_id IN (:increment_ids)');
        $bind = ['increment_ids' => join(array_keys($orders),',')];
        $result = $connection->fetchAll($select, $bind);
        if (count($result)) {
            foreach ($result as $row) {
                foreach ($orders as $key=>&$order) {
                    if ((string)$key === (string)$row['increment_id']) {
                        $orders[$row['entity_id']] = $order;
                        unset($orders[$key]);
                    }
                }
            }
        }
        return $orders;

    }
}