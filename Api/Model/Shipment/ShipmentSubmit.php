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
    /**
     * @var \RetailOps\Api\Api\Shipment\ShipmentInterface
     */
    protected $shipment;
    /**
     * @var \RetailOps\Api\Service\OrderCheck
     */
    protected $orderCheck;

    protected $events;

    protected $response;


    /**
     * ShipmentSubmit constructor.
     * @param \RetailOps\Api\Api\Shipment\ShipmentInterface $shipment
     * @param \RetailOps\Api\Service\OrderCheck $orderCheck
     */
    public function __construct(\RetailOps\Api\Api\Shipment\ShipmentInterface $shipment,
                                \RetailOps\Api\Service\OrderCheck $orderCheck)
    {
        $this->shipment = $shipment;
        $this->orderCheck = $orderCheck;
    }

    public function updateOrder(array $postData)
    {
        try{
            $orderId = $postData['channel_order_refnum'];
            $order = $this->getOrder($orderId);
            $this->shipment->setOrder($order);
            $this->shipment->registerShipment($postData);
        }catch(\Exception $e) {
           $this->setEventsInfo($e);
            $this->response['status'] = 'error';

        }finally{
            $response = [];
            $response['events'] = $this->events;
            $this->response = $response;
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
     * @param int|string $orderId
     */
    protected function getOrder($orderId)
    {
        return $this->orderCheck->getOrder((int)$orderId);
    }


}