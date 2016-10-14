<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 11.10.16
 * Time: 12.34
 */

namespace RetailOps\Api\Api;


interface InventoryHistoryInterface
{
    /**
     * Save history.
     *
     * @param InventoryHistoryInterface $history
     * @return Data\InventoryHistoryInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\RetailOps\Api\Api\Data\InventoryHistoryInterface $history);

    /**
     * Get history.
     *
     * @param int $historyId
     * @return \RetailOps\Api\Api\InventoryHistoryInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($historyId);

    /**
     * Retrieve pages matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \RetailOps\Api\Api\Data\InventoryHistorySearchInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * @param $historyId
     * @return $this
     */
    public function load($historyId);

}