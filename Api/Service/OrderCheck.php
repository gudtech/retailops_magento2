<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 14.10.16
 * Time: 12.40
 */

namespace RetailOps\Api\Service;


class OrderCheck implements \RetailOps\Api\Api\Services\Order\Check
{
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    public function canInvoice(\Magento\Sales\Model\Order $order)
    {
        return $order->canInvoice();
    }
    public function getForcedShipmentWithInvoice(\Magento\Sales\Model\Order $order)
    {
        return $order->getForcedShipmentWithInvoice();
    }

    public function canOrderShip(\Magento\Sales\Model\Order $order)
    {
        return $order->canShip();
    }

    public function hasItem($itemId, \Magento\Sales\Model\Order $order)
    {
        $items = $order->getItems();
        foreach ($items as $item) {
            if ($itemId == $item->getItemId()) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param int|string $itemId
     * @param \Magento\Sales\Model\Order $order
     * @return bool
     */
    public function itemCanShipment($itemId, \Magento\Sales\Model\Order $order)
    {
        $items = $order->getItems();
        $item = null;
        foreach ($items as $itemEntity) {
            if ($itemEntity->getItemId() == $itemId) {
                $item = $itemEntity;
            }
        }
        if(is_object($item)) {

            if ($item->getIsVirtual() || $item->getLockedDoShip()) {
                return false;
            }

            if ($item->isDummy(true)) {
                if ($item->getHasChildren()) {
                    if ($item->isShipSeparately()) {
                        return true;
                    }

                    foreach ($item->getChildrenItems() as $child) {
                        if ($child->getIsVirtual()) {
                            continue;
                        }

                            if ($child->getQtyToShip() > 0) {
                                return true;
                            }
                    }

                    return false;
                } elseif ($item->getParentItem()) {
                    $parent = $item->getParentItem();

                    if (empty($items)) {
                        return $parent->getQtyToShip() > 0;
                    } else {
                        return isset($items[$parent->getId()]) && $items[$parent->getId()] > 0;
                    }
                }
            } else {
                return $item->getQtyToShip() > 0;
            }
        }
        return false;

    }

    /**
     * @param $orderId
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function getOrder($orderId)
    {
        $order = $this->orderRepository->get((int)$orderId);
        if( is_object($order) && $order->getId() ) {
            return $order;
        }
        throw new \LogicException('No order with id'.$orderId);
    }

    public function __construct(\Magento\Sales\Api\OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

}