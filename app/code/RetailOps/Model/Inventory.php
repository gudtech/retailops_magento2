<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 8.9.16
 * Time: 17.43
 */

namespace Shiekhdev\RetailOps\Model;


class Inventory implements \Shiekhdev\RetailOps\Api\InventoryInterface
{
    protected $sku;

    protected $count;

    public function setSKU($sku)
    {
        $this->sku = $sku;
    }

    public function setCount($count)
    {
        $this->count = $count;
    }

    public function getSKU()
    {
        return $this->sku;
    }

    public function getCount()
    {
        return $this->count;
    }

}