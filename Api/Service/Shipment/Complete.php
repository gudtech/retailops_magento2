<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 12.10.16
 * Time: 14.02
 */

namespace RetailOps\Api\Service\Shipment;


class Complete extends \RetailOps\Api\Service\Shipment
{
   const COMPLETE = 'complete';
    /**
     * @var \RetailOps\Api\Service\OrderCheck
     */
    protected $orderCheck;

    /**
     * @var \RetailOps\Api\Service\InvoiceHelper
     */
    protected $invoiceHelper;

    public function __construct(\Magento\Shipping\Model\Config $shippingConfig,
                                \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader $shipmentLoader,
                                \Magento\Sales\Model\Order\Email\Sender\ShipmentSender $shipmentSender,
                                \RetailOps\Api\Service\OrderCheck $orderCheck,
                                \RetailOps\Api\Service\InvoiceHelper $invoiceHelper)
    {
        $this->orderCheck = $orderCheck;
        $this->invoiceHelper = $invoiceHelper;
        parent::__construct($shippingConfig, $shipmentLoader, $shipmentSender);
    }
    /**
     * @param array $postData
     */
    public function registerShipment(array $postData = [])
    {
        if(!$this->getOrder()) {
            throw new \LogicException(__('No any orders'));
        }
        $order = $this->getOrder();
        if(!$this->orderCheck->canOrderShip($this->getOrder())) {
            throw new \LogicException(__(sprintf('This order can\'t be ship, order number: %s', $this->getOrder()->getId())));
        }
        $this->setUnShippedItems($postData);
        //synchonize api with Shipment abstract class
        if(isset($postData['shipment'])) {
            $postData['shipments'] = $postData['shipment'];
        }
        $this->setTrackingAndShipmentItems($postData);

        /**
         * check, issset this items in order
         */
        $this->issetItems($this->getShippmentItems()['items'], $order);
        /**
         * check, if in order we have enough products for shipment
         */
        $this->haveQuantityToShip($this->getShippmentItems()['items'], $order);


        $this->createShipment($this->getOrder());

    }

    protected function haveQuantityToShip($items, \Magento\Sales\Api\Data\OrderInterface $order)
    {
        foreach ($items as $itemId => $quantity) {
            if (!$this->orderCheck->itemCanShipment($itemId, $order)) {
                throw new \LogicException(__(sprintf('Item id:%s can\'t be shipped', $itemId)));
            }
        }
        return true;
    }

    protected function issetItems($items, \Magento\Sales\Api\Data\OrderInterface $order)
    {
        if(is_array($items) && count($items)) {
            foreach ($items as $item=>$qty) {
                if(!$this->orderCheck->hasItem($item, $order))
                    throw new \LogicException(__(sprintf('Item with such id:%s don\'t exists in  order:%s'), [$item, $order->getId()]));
            }
            return true;
        }
        throw new \LogicException(__('No have any items for shipment'));
    }

}