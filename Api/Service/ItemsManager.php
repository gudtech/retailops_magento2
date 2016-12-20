<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 15.11.16
 * Time: 13.03
 */

namespace RetailOps\Api\Service;


class ItemsManager implements \RetailOps\Api\Api\ItemsManagerInterface
{
    /**
     * @var array
     */
    protected $cancelItems = [];

    /**
     * @var array
     */
    protected $needShipmentItems = [];

    /**
     * @var array
     */
    protected $needInvoiceItems = [];

    /**
     * @return array
     */
    public function getCancelItems()
    {
        return $this->cancelItems;
    }

    /**
     * @return array
     */
    public function getNeedInvoiceItems()
    {
        return $this->needInvoiceItems;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param array $items
     * @return array
     */
    public function removeCancelItems(\Magento\Sales\Api\Data\OrderInterface $order, array $items)
    {
        foreach ($order->getItems() as $orderItem)
        {
            if (array_key_exists($orderItem->getId(), $items))
            {
                $quantity = (float)$items[$orderItem->getId()];
                $delta = $quantity - (float)$orderItem->getQtyToCanceled();
                if( $delta <= 0) {
                    $this->cancelItems[$orderItem->getId()] = $delta;
                    unset($items[$orderItem->getId()]);
                }else{
                    if($orderItem->getQtyToCanceled() > 0) {
                        $this->cancelItems[$orderItem->getId()] = $orderItem->getQtyToCanceled();
                        $items[$orderItem->getId()] = $delta;
                    }
                }
            }
        }
        return $items;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param array $items
     */
    public function removeInvoicedAndShippedItems(\Magento\Sales\Api\Data\OrderInterface $order, array $items)
    {
        foreach ($order->getItems() as $orderItem)
        {
            if(array_key_exists($orderItem->getId(), $items)) {
                $quantityInvoice = (float)$items[$orderItem->getId()];
                $delta = $quantityInvoice -
                                            (float)$orderItem->getQtyInvoiced()
                                            +(float)$orderItem->getQtyCanceled()
                                            +(float)$orderItem->getQtyRefunded();
                if($delta <= 0) {
                    unset($items[$orderItem->getId()]);
                } else {
                    $items[$orderItem->getId()] = $delta;
                }

            }
        }
        $this->needInvoiceItems = $items;
        return $items;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param array $items
     * @return array
     */
    public function removeShippedItems(\Magento\Sales\Api\Data\OrderInterface $order, array &$items)
    {
        foreach ($order->getItems() as $orderItem)
        {
            if(array_key_exists($orderItem->getId(), $items)) {
                $quantityShip = (float)$items[$orderItem->getId()];
                $delta = $quantityShip -
                    (float)$orderItem->getQtyShipped()
                    +(float)$orderItem->getQtyCanceled()
                    +(float)$orderItem->getQtyRefunded();
                if($delta <= 0) {
                    unset($items[$orderItem->getId()]);
                } else {
                    $items[$orderItem->getId()] = $delta;
                }

            }
        }
        $this->needShipmentItems = $items;
        return $items;
    }

    public function canInvoiceItems(\Magento\Sales\Api\Data\OrderInterface $order, array $items)
    {
        foreach ($order->getItems() as $orderItem)
        {
            if(array_key_exists($orderItem->getId(), $items)) {
                $quantity = $items[$orderItem->getId()];
                if($orderItem->getQtyToInvoice() < $quantity) {
                    throw new \LogicException(__('Cannot create invoice for this item:'.$orderItem->getId()));
                }
                unset($items[$orderItem->getId()]);
            }
        }
        if(count($items)) {
            throw new \LogicException(__('Cannot create invoice for this items:'.json_encode($items)));
        }
    }

}