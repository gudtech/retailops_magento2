<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 10.10.16
 * Time: 17.37
 */

namespace Shiekhdev\RetailOps\Api\Shipment;


interface Shipment
{
    /**
     * @param array $postData
     * @return void
     */
    public function setUnShippedItems(array $postData=[]);

    /**
     * @param array $postData
     * @return void
     */
    public function setTracking(array $postData=[]);

    /**
     * @param array $packageItems
     * @return void
     */
    public function setShipmentsItems(array $packageItems=[]);

    /**
     * @param $itemId
     * @param $quantity
     * @return float
     */
    public function calcQuantity($itemId, $quantity);

}