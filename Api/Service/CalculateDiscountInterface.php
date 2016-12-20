<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 2.11.16
 * Time: 15.43
 */

namespace RetailOps\Api\Service;


interface CalculateDiscountInterface
{
    /**
     * @param $item
     * @return float
     */
    public function calculate(\Magento\Sales\Api\Data\OrderInterface $item):float;
}