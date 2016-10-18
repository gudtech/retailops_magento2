<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 26.9.16
 * Time: 11.59
 */

namespace RetailOps\Api\Model\Api\Order;


class Complete
{
    const COMPLETE = 'complete';
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;
    /**
     * @var \\RetailOps\Api\Logger\Logger
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

    /**
     * @var array|null
     */
    protected $response;

    /**
     * @var \RetailOps\Api\Api\Shipment\ShipmentInterface
     */
    protected $shipment;

    /**
     * @var \RetailOps\Api\Service\InvoiceHelper
     */
    protected $invoiceHelper;

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
            $shipment = $this->shipment;
            $shipment->setOrder($order);
            //create invoice, with shipments items
            $shipment->setUnShippedItems($postData);
            $shipment->setTrackingAndShipmentItems($postData);
            $order->setStatus(self::COMPLETE);
            $this->invoiceHelper->createInvoice($order, $this->shipment->getShippmentItems()['items']);
            $shipment->registerShipment($postData);


        }catch(\Exception $e) {
            $this->response['status'] = 'fail';
            $this->response['events'] = $this->response['events'] ?? [];
        }finally {
            return $this->response;
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
     * @param \RetailOps\Api\Logger\Logger $logger
     * @param \RetailOps\Api\Api\Shipment\ShipmentInterface
     * @param \RetailOps\Api\Service\InvoiceHelper $invoiceHelper
     */
    public function __construct(\Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
                                \RetailOps\Api\Logger\Logger $logger,
                                \RetailOps\Api\Api\Shipment\ShipmentInterface $shipment,
                                \RetailOps\Api\Service\InvoiceHelper $invoiceHelper)
    {
        $this->orderRepository = $orderRepository;
        $this->logger =  $logger;
        $this->shipment = $shipment;
        $this->invoiceHelper = $invoiceHelper;
    }


}