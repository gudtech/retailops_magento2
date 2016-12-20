<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 10.10.16
 * Time: 16.44
 */

namespace RetailOps\Api\Service;

abstract class Shipment implements \RetailOps\Api\Api\Shipment\ShipmentInterface
{
    /**
     * @var array|null
     */
    protected $unShippmentItems = [];

    /**
     * @var \Magento\Shipping\Model\Config
     */
    protected $shippingConfig;

    /**
     * @var \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader
     */
    protected $shipmentLoader;

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\ShipmentSender
     */
    protected $shipmentSender;

    /**
     * @var array|null
     */
    protected $tracking = [];

    /**
     * @var array|null
     */
    protected $shippmentItems = [];

    /**
     * @var \Magento\Sales\Api\Data\OrderInterface
     */
    protected $order;

    /**
     * @param array $postData
     * @return void
     */
    public function setUnShippedItems(array $postData = [])
    {
        $this->unShippmentItems = [];
        if(!count($postData)) {
            return;
        }
        $unShipments = $postData['unshipped_items'] ?? null;
        if($unShipments === null)
            return;
        foreach ($unShipments as $item) {
            $this->unShippmentItems[$item['channel_item_refnum']] = $item['unshipped_quantity'];
        }
    }

    public function setTrackingAndShipmentItems(array $postData = [])
    {
        $this->shippmentItems = [];
        $this->tracking = [];
        if( !isset($postData['shipments']) ) {
            return;
        }
        $shipment = $postData['shipments'];
        if ( !count($shipment)) {
            return;
        }
        foreach ($shipment as $ship) {
            if(!isset($ship['packages'])) {
                throw new \LogicException(__('No any package for orders'));
            }
            $tracking = [];
            $magentoTracking = $this->_getCarriersInstances();
            foreach ( $ship['packages'] as $package ) {
                $carrierName = isset($package['carrier_name']) ? strtolower($package['carrier_name']) : null;
                if ($carrierName === null) {
                    continue;
                }
                if(isset($package['tracking_number']) && !empty($package['tracking_number'])) {

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
                }
                $this->setShipmentsItems($package['package_items']);
                $this->tracking = $tracking;
            }
        }
    }

    /**
     * @return array
     */
    protected function _getCarriersInstances()
    {
        return $this->shippingConfig->getAllCarriers();
    }

    public function __construct(\Magento\Shipping\Model\Config $shippingConfig,
                                \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader $shipmentLoader,
                                \Magento\Sales\Model\Order\Email\Sender\ShipmentSender $shipmentSender)
    {
        $this->shippingConfig = $shippingConfig;
        $this->shipmentLoader = $shipmentLoader;
        $this->shipmentSender = $shipmentSender;

    }

    /**
     * @param  array $packageItems
     */
    public function setShipmentsItems(array $packageItems=[])
    {
        if ( is_array($packageItems) && count($packageItems) === 0 ) {
            return;
        }
        foreach ($packageItems as $item) {
            if (isset($this->shippmentItems['items'][$item['channel_item_refnum']])) {
                $quantity = (float)$this->shippmentItems['items'][$item['channel_item_refnum']] + (float)$item['quantity'];
                $this->shippmentItems['items'][$item['channel_item_refnum']] =
                    $this->calcQuantity($item['channel_item_refnum'], $quantity);
            }else{
                $this->shippmentItems['items'][$item['channel_item_refnum']] =
                    $this->calcQuantity($item['channel_item_refnum'], (float)$item['quantity']);
            }
        }


    }

    private function calcQuantity($itemId, $quantity)
    {
//        if (isset($this->unShippmentItems[$itemId])) {
//            $unShipQuantity =  $this->unShippmentItems[$itemId];
//            if ($unShipQuantity >= $quantity) {
//                $unShipQuantity = $unShipQuantity - $quantity;
//                if ( $unShipQuantity === 0 ) {
//                    unset($this->unShippmentItems[$itemId]);
//
//                }else{
//                    $this->unShippmentItems[$itemId] = $unShipQuantity;
//                }
//                $quantity = 0;
//            } else {
//                $quantity = $quantity - $unShipQuantity;
//                unset($this->unShippmentItems[$itemId]);
//            }
//        }
        return $quantity;
    }

    /**
     * @param $order
     */
    public function createShipment(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        $this->shipmentLoader->setOrderId($order->getId());
        $this->shipmentLoader->setShipment($this->shippmentItems);
        $this->shipmentLoader->setTracking($this->tracking);
        $shipment = $this->shipmentLoader->load();
        $shipment->addComment(__('Shipment from retailops'), true, true);
        $shipment->register();
        $this->_saveShipment($shipment);
        $this->sendEmail($shipment);

    }


    /**
     * Save shipment and order in one transaction
     *
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     * @return $this
     */
    protected function _saveShipment($shipment)
    {
        $shipment->getOrder()->setIsInProcess(true);
        $transaction = \Magento\Framework\App\ObjectManager::getInstance()->create(
            'Magento\Framework\DB\Transaction'
        );
        $transaction->addObject(
            $shipment
        )->addObject(
            $shipment->getOrder()
        )->save();

        return $this;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * return void
     */
    public function setOrder( \Magento\Sales\Api\Data\OrderInterface $order)
    {
        $this->order = $order;
    }

    /**
     * @return array|null
     */
    public function getUnShippmentItems()
    {
        return $this->unShippmentItems;
    }

    /**
     * @return array|null
     */
    public function getTracking()
    {
        return $this->tracking;
    }

    /**
     * @return array|null
     */
    public function getShippmentItems()
    {
        return $this->shippmentItems;
    }

    /**
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function getOrder(): \Magento\Sales\Api\Data\OrderInterface
    {
        return $this->order;
    }

    public function registerShipment(array $postData=[])
    {
        $this->setUnShippedItems($postData);
        $this->setTrackingAndShipmentItems($postData);
        $this->createShipment($this->getOrder());
    }

    protected function sendEmail($shipment)
    {
        $this->shipmentSender->send($shipment);
    }


}