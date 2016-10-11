<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 26.9.16
 * Time: 11.59
 */

namespace Shiekhdev\RetailOps\Model\Api\Order;


use Magento\Framework\App\ObjectManager;

class Complete
{
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;
    /**
     * @var \Shiekhdev\RetailOps\Logger\Logger
     */
    protected $logger;

    /**
     * @var \Magento\Shipping\Model\Config
     */
    protected $shippingConfig;

    /**
     * @var array
     */
    protected $unShippmentItems = [];

    protected $shippmentItems = [];


    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\ShipmentSender
     */
    protected $shipmentSender;

    /**
     * @var array
     */
    protected $tracking;

    /**
     * @var \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader
     */
    protected $shipmentLoader;

    protected $response;

    /**
     * @param array $postData
     */
    public function completeOrder( $postData )
    {
        $this->response['status'] = 'success';
        try {

            if (!isset($postData['channel_order_refnum'])) {
                throw new \LogicException(__('Don\'t have any order refnum'));
            }
            $orderId = $postData['channel_order_refnum'];
            $order = $this->orderRepository->get($orderId);
            if (!$order->getId()) {
                throw new \LogicException(sprintf('Don\'t have order with refnum %s', $postData['channel_order_refnum']));
            }

            $this->setUnShippedItems($postData);
            $this->setTracking($postData);
            $this->createShipment($order);
        }catch(\Exception $e) {
            $this->response['status'] = 'fail';
            $this->response['events'] = [];
        }

    }

    /**
     * @param  \Exception $e
     */
    protected function setEventsInfo($e)
    {
        $event = [];
        $event['event_type'] = 'error';
        $event['code'] = (string)$e->getCode();
        $event['message'] = $e->getMessage();
        $event['diagnostic_data'] = $e->getFile();
        if (isset($orderId)) {
            $event['associations'] = [
                'identifier_type' => 'order_refnum',
                'identifier' => (string)$orderId];

        }
        $this->events[] = $event;
    }

    /**
     * Complete constructor.
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Shiekhdev\RetailOps\Logger\Logger $logger
     */
    public function __construct(\Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
                                \Shiekhdev\RetailOps\Logger\Logger $logger,
                                \Magento\Shipping\Model\Config $shippingConfig,
                                \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader $shipmentLoader,
                                \Magento\Sales\Model\Order\Email\Sender\ShipmentSender $shipmentSender)
    {
        $this->orderRepository = $orderRepository;
        $this->logger =  $logger;
        $this->shippingConfig = $shippingConfig;
        $this->shipmentLoader = $shipmentLoader;
        $this->shipmentSender = $shipmentSender;

    }

    /**
     * @param $order
     */
    protected function createShipment($order)
    {
        $this->shipmentLoader->setOrderId($order->getId());
        $this->shipmentLoader->setShipment($this->shippmentItems);
        $this->shipmentLoader->setTracking($this->tracking);
        $shipment = $this->shipmentLoader->load();
        $shipment->addComment(__('Shipment from retailops'), true, true);
        $shipment->register();
        $this->_saveShipment($shipment);

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
        $transaction = ObjectManager::getInstance()->create(
            'Magento\Framework\DB\Transaction'
        );
        $transaction->addObject(
            $shipment
        )->addObject(
            $shipment->getOrder()
        )->save();

        return $this;
    }



    protected function setUnShippedItems($postData)
    {
        $unShipments = $postData['unshipped_items'] ?? null;
        if($unShipments === null)
            return;
        foreach ($unShipments as $item) {
            $unShipments[$item['channel_item_refnum']] = $item['unshipped_quantity'];
        }
        $this->unShippmentItems = $unShipments;
    }

    protected function setTracking($postData)
    {
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

    }

    /**
     * @param  array $packageItems
     */
    protected function setShipmentsItems($packageItems)
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

    protected function calcQuantity($itemId, $quantity)
    {
        if (isset($this->unShippmentItems[$itemId])) {
            $unShipQuantity =  $this->unShippmentItems[$itemId];
            if ($unShipQuantity >= $quantity) {
                $unShipQuantity = $unShipQuantity - $quantity;
                if ( $unShipQuantity === 0 ) {
                    unset($this->unShippmentItems[$itemId]);

                }
                $quantity = 0;
            } else {
                $quantity = $quantity - $unShipQuantity;
                $unShipQuantity = 0;
                unset($this->unShippmentItems[$itemId]);
            }
        }
        return $quantity;
    }


    /**
     * @return array
     */
    protected function _getCarriersInstances()
    {
        return $this->shippingConfig->getAllCarriers();
    }
}