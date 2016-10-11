<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 26.9.16
 * Time: 11.53
 */

namespace RetailOps\Api\Controller\Frontend\Order;

use Magento\Framework\App\ObjectManager;
use \RetailOps\Api\Controller\RetailOps;

class Update extends RetailOps
{
    const SERVICENAME = 'order_update';

    protected $events = [];
    protected $response = [];
    protected $status = 'success';
    public function execute()
    {
        try {
            $postData = $this->getRequest()->getPost();
            $orderFactrory = $this->orderFactory->create();
            $response = $orderFactrory->updateOrder($postData);
            $serviceName = self::SERVICENAME;
            $areaName = "retailops_before_pull_{$serviceName}";
            $this->_eventManager->dispatch($areaName, [
                'response' => $response,
                'request' => $this->getRequest(),
            ]);
            $this->response = $response;
        } catch (\Exception $e) {
            $event = [
                'event_type' => 'error',
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'diagnostic_data' => 'string',
                'associations' => $this->association,
            ];

            $this->events[] = $event;
            $this->status = 'error';

        } finally {
            $this->response['events'] = [];
            foreach ($this->events as $event) {
                $this->response['events'][] = $event;
            }
            $this->_eventManager->dispatch($areaName, [
                'request' => $this->getRequest(),
                'response' =>$response
            ]);
            $this->getResponse()->representJson(json_encode($this->response));
            $this->getResponse()->setStatusCode('200');
            return $this->getResponse();
        }
    }


    public function __construct(RetailOps\Api\Model\Order\UpdateFactory $orderFactory,
                                \Magento\Framework\App\Action\Context $context )
    {
        $this->orderFactory = $orderFactory;
        parent::__construct($context);
        $this->logger = $this->_objectManager->get('\RetailOps\Api\Logger\Logger');
    }
}