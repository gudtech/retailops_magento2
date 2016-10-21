<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 14.10.16
 * Time: 12.07
 */

namespace RetailOps\Api\Controller\Frontend\Order;

use \RetailOps\Api\Controller\RetailOps;

class Shipment  extends RetailOps
{
    const SERVICENAME = 'shipment_submit';
    const COUNT_ORDERS_PER_REQUEST = 50;
    /**
     * @var string
     */
    protected $areaName = self::BEFOREPULL.self::SERVICENAME;

    /**
     * @var \RetailOps\Api\Model\Shipment\ShipmentSubmit
     */
    protected $shipmentSubmit;

    /**
     * @var string
     */
    protected $statusRetOps = 'success';

    /**
     * @var array|null
     */
    protected $events=[];

    public function __construct(\RetailOps\Api\Model\Shipment\ShipmentSubmit $shipmentSubmit,
                                \Magento\Framework\App\Action\Context $context,
                                \RetailOps\Api\Logger\Logger $logger)
    {
        $this->shipmentSubmit = $shipmentSubmit;
        parent::__construct($context);
        $this->logger = $logger;
    }

    public function execute()
    {
        try {
            $postData = (array)$this->getRequest()->getPost();
            $response = $this->shipmentSubmit->updateOrder($postData);
            $this->response = $response;
        } catch (\Exception $e) {
            $event = [
                'event_type' => 'error',
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'diagnostic_data' => 'string',
                'associations' => $this->association,
            ];
            $this->error = $e;
            $this->events[] = $event;
            $this->statusRetOps = 'error';

        } finally {
            $this->response['status'] = $this->response['status'] ?? $this->statusRetOps;
            foreach ($this->events as $event) {
                $this->response['events'][] = $event;
            }
            $this->getResponse()->representJson(json_encode($this->response));
            $this->getResponse()->setStatusCode('200');
            parent::execute();
            return $this->getResponse();
        }
    }




}