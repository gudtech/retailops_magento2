<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 2.11.16
 * Time: 15.40
 */

namespace RetailOps\Api\Service;


class CalculateOrderDiscount implements CalculateDiscountInterface
{
    /**
     * @var Order\Map\RewardPointsInterface
     */
    protected $_rewardPoints;

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return float
     */
    public function calculate(\Magento\Sales\Api\Data\OrderInterface $order):float
    {
        $discount = 0;
        $discount +=(float)$order->getBaseDiscountTaxCompensationAmount();
        $discount +=(float)$order->getBaseShippingDiscountAmount();
        $discount +=(float)$order->getBaseShippingDiscountTaxCompensationAmnt();
        $discount +=(float)$order->getBaseCustomerBalanceAmount();
        return $this->addRewardPoints($discount, $order);
    }

    /**
     * @param float $discount
     * @return float
     */
    public function addRewardPoints(float $discount, \Magento\Sales\Api\Data\OrderInterface $order) :float
    {
        return $this->_rewardPoints->getRewardsPointsPaymentTransaction($discount, $order);
    }

    /**
     * CalculateOrderDiscount constructor.
     * @param Order\Map\RewardPointsInterface $rewardPoints
     */
    public function __construct(\RetailOps\Api\Service\Order\Map\RewardPointsInterface $rewardPoints)
    {
        $this->_rewardPoints = $rewardPoints;
    }
}