<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 8.9.16
 * Time: 17.43
 */

namespace RetailOps\Api\Model;


class Inventory implements \RetailOps\Api\Api\InventoryInterface
{
    protected $sku;

    protected $count;

    public function setUPC($sku)
    {
        $this->sku = $sku;
    }

    public function setCount($count)
    {
        $this->count = $count;
    }

    public function getUPC()
    {
        return $this->sku;
    }

    public function getCount()
    {
        return $this->count;
    }

}