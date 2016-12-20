<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 11.11.16
 * Time: 14.21
 */

namespace RetailOps\Api\Service\Order\Map;

use RetailOps\Api\Api\Order\Map\CalculateAmountInterface;

class CalculateAmout implements CalculateAmountInterface
{
    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return float
     */
    public function calculateShipping(\Magento\Sales\Api\Data\OrderInterface $order)
    {
       $shippingAmount = (float)$order->getBaseShippingAmount()
                            -(float)$order->getBaseShippingCanceled()
                            -(float)$order->getBaseShippingRefunded();
        if ($shippingAmount < 0 ) {
            throw  new \LogicException('Shipping amount is:'.$shippingAmount.', but amt cannot be negative, order:'.$order->getId());
        }
        return $shippingAmount;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return float
     */
    public function calculateGrandTotal(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        $total = (float)$order->getBaseGrandTotal()
                        -(float)$order->getBaseTotalRefunded()
                        -(float)$order->getBaseTotalCanceled();
        if ($total < 0) {
           throw new \LogicException('Total amout cannot be negative, order:'.$order->getId());
        }
        return $total;
    }
}