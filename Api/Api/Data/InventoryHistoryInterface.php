<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 11.10.16
 * Time: 13.12
 */

namespace RetailOps\Api\Api\Data;


/**
 * Interface InventoryHistoryInterface
 * @package RetailOps\Api\Api\Data
 */
interface InventoryHistoryInterface
{
    const ID = 'id';
    const PRODUCT_ID = 'product_id';
    const INVENTORY_ARRIVED = 'inventory_arrived';
    const INVENTORY_IN_SHOP = 'inventory_in_shop';
    const INVENTORY_ADD = 'inventory_add';
    const OPERATOR = 'operator';
    const DATE_CRETE = 'date_create';
    /**
     * @return int|string|null
     */
    public function getId();

    /**
     * @return int|string|null
     */
    public function getProductId();

    /**
     * @return int|string|null
     */
    public function getInventoryArrived();

    /**
     * @return int|string|null
     */
    public function getInventoryInShop();

    /**
     * @return string
     */
    public function getOperator();

    /**
     * @return int|string|null
     */
    public function getInventoryAdd();

    /**
     * @return int|string|null
     */
    public function getDateCreate();

    /**
     * @param $id
     * @return void
     */
    public function setId($id);

    /**
     * @param $inventory
     * @return void
     */
    public function setInventoryAdd($inventory);

    /**
     * @param $productId
     * @return void
     */
    public function setProductId($productId);

    /**
     * @param $inventoryArrived
     * @return void
     */
    public function setInventoryArrived($inventoryArrived);

    /**
     * @param $inventoryInShop
     * @return void
     */
    public function setInventoryInShop($inventoryInShop);

    /**
     * @param $operator
     * @return void
     */
    public function setOperator($operator);

}