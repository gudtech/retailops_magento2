<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 17.10.16
 * Time: 10.57
 */

namespace RetailOps\Api\Service\Shipment;


class ShipmentSubmit extends \RetailOps\Api\Service\Shipment
{
    /**
     * @var \RetailOps\Api\Service\OrderCheck
     */
    protected $orderCheck;

    /**
     * @var \RetailOps\Api\Service\InvoiceHelper
     */
    protected $invoiceHelper;

    /**
     * ShipmentSubmit constructor.
     * @param \Magento\Shipping\Model\Config $shippingConfig
     * @param \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader $shipmentLoader
     * @param \Magento\Sales\Model\Order\Email\Sender\ShipmentSender $shipmentSender
     * @param \RetailOps\Api\Service\OrderCheck $orderCheck
     * @param \RetailOps\Api\Service\InvoiceHelper $invoiceHelper
     */
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

        if($this->orderCheck->getForcedShipmentWithInvoice($order)) {
            $this->invoiceHelper->createInvoice($this->getShippmentItems(), $order);
        }

        $this->createShipment($this->getOrder());
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

    public function setTrackingAndShipmentItems(array $postData = [])
    {
        if( !isset($postData['shipment']) ) {
            return;
        }
        $shipment = $postData['shipment'];
        if ( !count($shipment)) {
            return;
        }

            if(!isset($shipment['packages'])) {
                throw new \LogicException(__('No any package for orders'));
            }
            $tracking = [];
            $magentoTracking = $this->_getCarriersInstances();
            foreach ( $shipment['packages'] as $package ) {
                $carrierName = isset($package['carrier_name']) ? strtolower($package['carrier_name']) : null;
                if ($carrierName === null) {
                    continue;
                }
                //try to find retailops carrier in magento carriers, else use custom label
                if (isset($magentoTracking[$carrierName])) {
                    $tracking[] = [
                        'carrier_code' => $carrierName,
                        'title' => $magentoTracking[$carrierName]->getConfigData('title'),
                        'number' => isset($package['tracking_number']) ? $package['tracking_number'] : null
                    ];
                } else {
                    $tracking[] = [
                        'carrier_code' => 'custom',
                        'title' => $package['carrier_class_name'] ?? 'RetailOps',
                        'number' => isset($package['tracking_number']) ? $package['tracking_number'] : null
                    ];
                }
                $this->setShipmentsItems($package['package_items']);
                $this->tracking = $tracking;
            }
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
}