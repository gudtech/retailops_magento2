<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 14.10.16
 * Time: 12.26
 */

namespace RetailOps\Api\Api\Services\Order;


/**
 * Interface Check
 * @package RetailOps\Api\Api\Services\Order
 */
interface Check
{
    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return boolean
     */
    public function canInvoice( \Magento\Sales\Model\Order $order );

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return boolean
     */
    public function canOrderShip(\Magento\Sales\Model\Order $order);

    /**
     * @param string|integer $itemId
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return boolean
     */
    public function hasItem($itemId, \Magento\Sales\Model\Order $order);

    /**
     * @param string|integer $itemId
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return mixed
     */
    public function itemCanShipment($itemId, \Magento\Sales\Model\Order $order);

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return mixed
     */
    public function getForcedShipmentWithInvoice(\Magento\Sales\Model\Order $order);
}