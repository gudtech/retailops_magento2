<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 12.9.16
 * Time: 11.51
 */

namespace RetailOps\Api\Controller\Frontend\Order;

use Magento\Framework\App\ObjectManager;
use \RetailOps\Api\Controller\RetailOps;

class Pull  extends RetailOps
{
    const SERVICENAME = 'order';
    const MAX_COUNT_ORDERS_PER_REQUEST = 50;
    const MIN_COUNT_ORDERS_PER_REQUEST = 1;
    const ENABLE = 'retailops/RetailOps_feed/order_pull';
    const COUNT_ORDERS_PER_REQUEST = 'retailops/RetailOps/order_count';
    /**
     * @var string
     */
    protected $areaName = self::BEFOREPULL.self::SERVICENAME;
    /**
     * @var \\RetailOps\Model\Pull\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var null|string|array
     */
    protected $response;

    /**
     * @var \\RetailOps\Logger\Logger
     */
    protected $logger;

    /**
     * @var string|int
     */
    protected $status = 200;

    public function execute()
    {
        try{
            $scopeConfig = $this->_objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');
            if(!$scopeConfig->getValue(self::ENABLE)) {
                throw new \LogicException('This feed disable');
            }
            $orderFactory = $this->orderFactory->create();
            $pageToken = $this->getRequest()->getParam('page_token');
            $postData = $this->getRequest()->getPost();
            $countOfOrders = $scopeConfig->getValue(self::COUNT_ORDERS_PER_REQUEST);
            if($countOfOrders > 50) {
                $countOfOrders = 50;
            }
            if($countOfOrders < 1) {
                $countOfOrders = 1;
            }
            $response = $orderFactory->getOrders($pageToken, $countOfOrders, $postData);
            $this->response = $response;
        }catch(\Exception $e){
            $this->logger->addCritical($e->getMessage());
            $this->response = [];
            $this->status = 500;
            $this->error = $e;
            parent::execute();
        }finally{
            $this->getResponse()->representJson(json_encode($this->response));
            $this->getResponse()->setStatusCode($this->status);
            parent::execute();
        }
    }

    public function __construct(\RetailOps\Api\Model\Pull\OrderFactory $orderFactory,
                                \Magento\Framework\App\Action\Context $context )
    {
        $this->orderFactory = $orderFactory;
        parent::__construct($context);
        $this->logger = $this->_objectManager->get('\RetailOps\Api\Logger\Logger');

    }
}