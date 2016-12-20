<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 29.11.16
 * Time: 10.29
 */

namespace RetailOps\Api\Controller\Frontend\Order;

use Magento\Framework\App\ObjectManager;
use \RetailOps\Api\Controller\RetailOps;

class ReturnOrder extends RetailOps
{
    CONST ENABLE = 'retailops/RetailOps_feed/order_return';
    /**
     * @var \RetailOps\Api\Model\Order\OrderReturn
     */
    protected $orderReturn;

    protected $events;

    public function __construct(
       \RetailOps\Api\Model\Order\OrderReturn $orderReturn,
       \Magento\Framework\App\Action\Context $context,
       \RetailOps\Api\Logger\Logger $logger)
    {
        $this->orderReturn = $orderReturn;
        parent::__construct($context);
        $this->logger = $logger;
    }

    public function execute()
    {
        try {
            $scopeConfig = $this->_objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');
            if(!$scopeConfig->getValue(self::ENABLE)) {
                throw new \LogicException('This feed disable');
            }
            $postData = (array)$this->getRequest()->getPost();
            $response = $this->orderReturn->returnOrder($postData);
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
            if(!array_key_exists('events', $this->response)) {
                $this->response['events'] = [];
            }
//            $this->response['status'] = $this->response['status'] ?? $this->statusRetOps;
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