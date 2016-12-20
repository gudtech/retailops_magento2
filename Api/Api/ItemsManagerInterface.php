<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 15.11.16
 * Time: 13.04
 */

namespace RetailOps\Api\Api;


interface ItemsManagerInterface
{
    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param array $items
     * @return array
     */
    public function removeCancelItems(\Magento\Sales\Api\Data\OrderInterface $order, array $items);

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param array $items
     * @return array
     */
    public function removeInvoicedAndShippedItems(\Magento\Sales\Api\Data\OrderInterface $order, array $items);

    /**
     * @return array
     */
    public function getCancelItems();


    /**
     * @return array
     */
    public function getNeedInvoiceItems();

}