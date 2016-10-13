<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 10.10.16
 * Time: 17.37
 */

namespace RetailOps\Api\Api\Shipment;


interface ShipmentInterface
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
    public function setTrackingAndShipmentItems(array $postData=[]);

    /**
     * @param array $packageItems
     * @return void
     */
    public function setShipmentsItems(array $packageItems=[]);


    /**
     * @param array $postData
     * @return void
     */
    public function registerShipment(array $postData=[]);

}