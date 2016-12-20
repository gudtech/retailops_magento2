<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 2.11.16
 * Time: 18.22
 */

namespace RetailOps\Api\Service;


interface CalculateItemPriceInterface
{
    public function calculate(\Magento\Sales\Api\Data\OrderItemInterface $item);

    public function calculateItemTax(\Magento\Sales\Api\Data\OrderItemInterface $item);
}