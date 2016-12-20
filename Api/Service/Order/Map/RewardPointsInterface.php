<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 20.12.16
 * Time: 11.16
 */

namespace RetailOps\Api\Service\Order\Map;

/**
 * Interface RewardPointsInterface
 * @package RetailOps\Api\Service\Order\Map
 */
interface RewardPointsInterface
{
    /**
     * @param array $payments
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return array $payments
     */
    public function getRewardsPointsPaymentTransaction(float $discount, \Magento\Sales\Api\Data\OrderInterface $order);
}