<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 14.10.16
 * Time: 12.17
 */

namespace RetailOps\Api\Model\Shipment;


class ShipmentSubmit
{
    use \RetailOps\Api\Model\Api\Traits\Filter;
    /**
     * @var \RetailOps\Api\Api\Shipment\ShipmentInterface
     */
    protected $shipment;
    /**
     * @var \RetailOps\Api\Service\OrderCheck
     */
    protected $orderCheck;

    /**
     * @var \RetailOps\Api\Service\ItemsManager
     */
    protected $itemsManager;

    protected $events=[];

    protected $response;

    /**
     * @var \RetailOps\Api\Service\InvoiceHelper
     */
    protected $invoiceHelper;

    /**
     * @var \RetailOps\Api\Api\Services\CreditMemo\CreditMemoHelperInterface
     */
    protected $creditMemoHelper;

    /**
     * @var \Magento\Sales\Api\OrderManagementInterface
     */
    protected $orderManager;


    /**
     * ShipmentSubmit constructor.
     * @param \RetailOps\Api\Api\Shipment\ShipmentInterface $shipment
     * @param \RetailOps\Api\Service\OrderCheck $orderCheck
     */
    public function __construct(\RetailOps\Api\Api\Shipment\ShipmentInterface $shipment,
                                \RetailOps\Api\Service\OrderCheck $orderCheck,
                                \RetailOps\Api\Service\ItemsManagerFactory $itemsManagerFactory,
                                \Magento\Sales\Model\Service\OrderService $orderManagement,
                                \RetailOps\Api\Service\InvoiceHelper $invoiceHelper)
    {
        $this->shipment = $shipment;
        $this->orderCheck = $orderCheck;
        $this->itemsManager = $itemsManagerFactory->create();
        $this->invoiceHelper = $invoiceHelper;
        $this->orderManager = $orderManagement;
    }

    public function updateOrder(array $postData)
    {
        try{
            $orderId = $this->getOrderIdByIncrement($postData['channel_order_refnum']);
            $order = $this->getOrder($orderId);
            $this->shipment->setOrder($order);
            //create invoice, with shipments items
            $this->shipment->setUnShippedItems($postData);
            $this->shipment->setTrackingAndShipmentItems($postData);
            //for synchronize with complete block, add shipments key
            if(array_key_exists('shipment', $postData) && !array_key_exists('shipments', $postData))
            {
                $postData['shipments'][] = $postData['shipment'];
                unset($postData['shipment']);
            }
            if(array_key_exists('items', $this->shipment->getShippmentItems()) && count($this->shipment->getShippmentItems()['items'])) {
                //remove items, that already had invoice
                $needInvoiceItems = $this->itemsManager->removeInvoicedAndShippedItems($order, $this->shipment->getShippmentItems()['items']);
                $this->itemsManager->canInvoiceItems($order, $needInvoiceItems);
                $this->invoiceHelper->createInvoice($order, $needInvoiceItems);

            }
            $this->shipment->registerShipment($postData);
        }catch(\Exception $e) {
           $this->setEventsInfo($e);
//            $this->response['status'] = 'fail';

        }finally{
            $this->response['events'] = $this->events;
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
        $event['associations'] = [];
        if (isset($orderId)) {
            $event['associations'][] = [
                'identifier_type' => 'order_refnum',
                'identifier' => (string)$orderId];

        }
        $this->events[] = $event;
    }

    /**
     * @param int|string $orderId
     */
    protected function getOrder($orderId)
    {
        return $this->orderCheck->getOrder((int)$orderId);
    }


}