<?php
namespace RetailOps\Api\Model;
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 11.10.16
 * Time: 14.16
 */
use \Magento\Framework\Model\AbstractModel;
use \RetailOps\Api\Api\Data\InventoryHistoryInterface;

class InventoryHistory extends AbstractModel implements InventoryHistoryInterface
{
    public function getProductId()
    {
        return parent::getData(self::PRODUCT_ID);
    }

    public function getInventoryArrived()
    {
        return parent::getData(self::INVENTORY_ARRIVED);
    }

    public function getInventoryInShop()
    {
        return parent::getData(self::INVENTORY_IN_SHOP);
    }

    public function getOperator()
    {
        return parent::getData(self::OPERATOR);
    }

    public function getInventoryAdd()
    {
        return parent::getData(self::INVENTORY_ADD);
    }

    public function getDateCreate()
    {
        return parent::getData(self::DATE_CRETE);
    }

    public function setInventoryAdd($inventory)
    {
       return parent::setData(self::INVENTORY_ADD, $inventory);
    }

    public function setProductId($productId)
    {
        return parent::setData(self::PRODUCT_ID, $productId);
    }

    public function setInventoryArrived($inventoryArrived)
    {
        return parent::setData(self::INVENTORY_ARRIVED, $inventoryArrived);
    }

    public function setInventoryInShop($inventoryInShop)
    {
        return parent::setData(self::INVENTORY_IN_SHOP, $inventoryInShop);
    }

    public function setOperator($operator)
    {
        return parent::setData(self::OPERATOR, $operator);
    }

    /**
     * _construct
     */
    protected function _construct()
    {
        $this->_init('RetailOps\Api\Model\Resource\InventoryHistory');
    }

    public function setRealCount($realCount)
    {
        return parent::setData(self::REAL_COUNT, $realCount);
    }

    public function getRealCount()
    {
        return parent::getData(self::REAL_COUNT);
    }

    public function setReserveCount($reserveCount)
    {
        return parent::setData(self::RESERVE_COUNT, $reserveCount);
    }

    public function getReserveCount()
    {
        return parent::getData(self::RESERVE_COUNT);
    }

}