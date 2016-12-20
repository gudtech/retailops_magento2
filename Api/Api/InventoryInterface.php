<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 8.9.16
 * Time: 17.39
 */

namespace RetailOps\Api\Api;


interface InventoryInterface
{
    /**
     * @param string|integer $productId
     * @return null
     */
    public function setUPC($sku);

    /**
     * @param string|integer $count
     * @return null
     */
    public function setCount($count);


    /**
     * @return string|null|integer
     */

    public function getCount();

    /**
     * @return string|null|integer
     */
    public function getUPC();

    /**
     * @param string|integer|float $realCount
     * @return mixed
     */
    public function setRealCount($realCount);

    /**
     * @return mixed
     */
    public function getRealCount();

    /**
     * @param float $reserveCount
     * @return mixed
     */
    public function setReserveCount($reserveCount);

    /**
     * @return mixed
     */
    public function getReserveCount();

}