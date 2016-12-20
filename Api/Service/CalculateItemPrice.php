<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 2.11.16
 * Time: 18.21
 */

namespace RetailOps\Api\Service;


class CalculateItemPrice implements CalculateItemPriceInterface
{
    public function calculate(\Magento\Sales\Api\Data\OrderItemInterface $item):float
    {
        $qty = (float)$item->getQtyOrdered();
        $basePrice = (float)$item->getBasePrice();
        $discount = (float)$item->getBaseDiscountAmount();
        $discountPerProduct = round($discount/$qty, 4, PHP_ROUND_HALF_UP);
        $priceWithDiscount = round($basePrice - $discountPerProduct, 2, PHP_ROUND_HALF_UP);
        if ($priceWithDiscount < 0) {
           $priceWithDiscount = 0;
        }
        return $priceWithDiscount;
    }

    public function calculateItemTax(\Magento\Sales\Api\Data\OrderItemInterface $item):float
    {
        $qty = (float)$item->getQtyOrdered();
        $tax = (float)$item->getTaxAmount();
        $taxPerProduct = round($tax/$qty, 4, PHP_ROUND_HALF_UP);

        return $taxPerProduct;
    }
}