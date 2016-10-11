<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 26.9.16
 * Time: 11.53
 */

namespace RetailOps\Controller\Frontend\Order;

use Magento\Framework\App\ObjectManager;
use \RetailOps\Api\Controller\RetailOps;

class Complete extends RetailOps
{
    const SERVICENAME = 'order_complete';

    protected $events = [];
    protected $response = [];
    protected $statusRetOps = 'success';
    /**
     * @var \\RetailOps\Model\Order\Complete
     */
    protected $orderFactory;
    /**
     * @var string
     */
    protected $areaName = self::BEFOREPULL.self::SERVICENAME;
    public function execute()
    {

        try {
            $postData = (array)$this->getRequest()->getPost();
            /**
             * \\RetailOps\Model\Order\CompleteFactory
             */
            $orderFactrory = $this->orderFactory->create();
            $response = $orderFactrory->updateOrder($postData);
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
            $this->response['events'] = [];
            $this->response['status'] = $this->statusRetOps;
            foreach ($this->events as $event) {
                $this->response['events'][] = $event;
            }
            $this->getResponse()->representJson(json_encode($this->response));
            $this->getResponse()->setStatusCode('200');
            parent::execute();
            return $this->getResponse();
        }
    }


    public function __construct(\RetailOps\Api\Model\Order\CompleteFactory $orderFactory,
                                \Magento\Framework\App\Action\Context $context )
    {
        $this->orderFactory = $orderFactory;
        parent::__construct($context);
        $this->logger = $this->_objectManager->get('\RetailOps\Api\Logger\Logger');
    }
}