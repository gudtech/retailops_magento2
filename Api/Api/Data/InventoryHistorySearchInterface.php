<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 11.10.16
 * Time: 12.57
 */

namespace RetailOps\Api\Api\Data;


interface InventoryHistorySearchInterface
{
    /**
     * Get pages list.
     *
     * @return \RetailOps\Api\Api\InventoryHistoryInterface[]
     */
    public function getItems();

    /**
     * Set pages list.
     *
     * @param \RetailOps\Api\Api\InventoryHistoryInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}