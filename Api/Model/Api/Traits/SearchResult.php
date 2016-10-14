<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 11.10.16
 * Time: 16.10
 */

namespace RetailOps\Api\Model\Api\Traits;

use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
trait SearchResult
{
    /**
     * Adds a specified filter group to the specified quote collection.
     *
     * @param FilterGroup $filterGroup The filter group.
     * @param AbstractCollection $collection The quote collection.
     * @return void
     * @throws InputException The specified filter group or quote collection does not exist.
     */
    protected function addFilterGroupToCollection(FilterGroup $filterGroup, AbstractCollection $collection)
    {
        $fields = [];
        $conditions = [];
        foreach ($filterGroup->getFilters() as $filter) {
            $fields[] = $filter->getField();
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $conditions[] = [$condition => $filter->getValue()];
        }
        if ($fields) {
            $collection->addFieldToFilter($fields, $conditions);
        }
    }

    protected function prepareSearchData(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria, $searchData, AbstractCollection $collection)
    {
        $searchData->setSearchCriteria($searchCriteria);
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $collection);
        }
        $searchData->setTotalCount($collection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();
        if ($sortOrders) {
            /** @var SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    $sortOrder->getDirection() == SortOrder::SORT_ASC ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());

        $searchData->setItems($collection->getItems());
        return $searchData;
    }

}