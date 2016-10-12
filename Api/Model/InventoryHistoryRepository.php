<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 11.10.16
 * Time: 13.08
 */

namespace RetailOps\Api\Model;


use Magento\Framework\Exception\LocalizedException;
use RetailOps\Api\Api\InventoryHistoryInterface;
use RetailOps\Api\Api\Data\InventoryHistoryInterface as InventoryHistoryDataInterface;

class InventoryHistoryRepository implements InventoryHistoryInterface
{
    use \RetailOps\Api\Model\Api\Traits\SearchResult;
    /**
     * @var Resource\InventoryHistory
     */
    protected $resource;
    /**
     * @var Resource\Collection\InventoryHistory\CollectionFactory
     */
    protected $collectionFactory;
    /**
     * @var InventoryHistoryFactory
     */
    protected $inventoryHistoryFactory;

    /**
     * @var \RetailOps\Api\Api\Data\InventoryHistorySearchInterfaceFactory
     */
    protected $searchResultFactory;
    /**
     * @param int $historyId
     */
    public function getById($historyId)
    {
        $inventoryHistory = $this->inventoryHistoryFactory->create();
        $inventoryHistory = $this->resource->load($inventoryHistory, $historyId);
        if(!$inventoryHistory->getId()) {
            throw new LocalizedException(__('no this id in database'));
        }
        return $inventoryHistory;
    }

    /**
     * @param InventoryHistoryDataInterface $history
     * @return InventoryHistoryDataInterface
     */
    public function save(InventoryHistoryDataInterface $history)
    {
        $history = $this->resource->save($history);
        return $history;
    }

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return InventoryHistoryDataInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->collectionFactory->create();
        $searchData = $this->searchResultFactory->create();
        $this->prepareSearchData($searchCriteria, $searchData, $collection);
        return $searchData;
    }

    /**
     * @param $historyId
     * @return $this
     */
    public function load($historyId)
    {
        return $this->getById($historyId);
    }

    public function __construct(
        \RetailOps\Api\Model\Resource\InventoryHistory $resource,
        \RetailOps\Api\Model\Resource\Collection\InventoryHistory\CollectionFactory $collectionFactory,
        \RetailOps\Api\Model\InventoryHistoryFactory $inventoryHistoryFactory,
        \RetailOps\Api\Api\Data\InventoryHistorySearchInterfaceFactory $searchResult
    )
    {
        $this->resource = $resource;
        $this->collectionFactory = $collectionFactory;
        $this->inventoryHistoryFactory = $inventoryHistoryFactory;
        $this->searchResultFactory = $searchResult;

    }


}