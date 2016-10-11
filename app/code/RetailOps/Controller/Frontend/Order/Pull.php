<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 12.9.16
 * Time: 11.51
 */

namespace Shiekhdev\RetailOps\Controller\Frontend\Order;

use Magento\Framework\App\ObjectManager;
use \Shiekhdev\RetailOps\Controller\RetailOps;

class Pull  extends RetailOps
{
    const SERVICENAME = 'order';
    const COUNT_ORDERS_PER_REQUEST = 50;
    /**
     * @var string
     */
    protected $areaName = self::BEFOREPULL.self::SERVICENAME;
    /**
     * @var \Shiekhdev\RetailOps\Model\Pull\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var null|string|array
     */
    protected $response;

    /**
     * @var \Shiekhdev\RetailOps\Logger\Logger
     */
    protected $logger;

    /**
     * @var string|int
     */
    protected $status = 200;

    public function execute()
    {
        try{
            $orderFactory = $this->orderFactory->create();
            $pageToken = $this->getRequest()->getParam('page_token');
            $postData = $this->getRequest()->getPost();
            $response = $orderFactory->getOrders($pageToken, self::COUNT_ORDERS_PER_REQUEST, $postData);
            $this->response = $response;
        }catch(\Exception $e){
            $this->logger->addCritical($e->getMessage());
            $this->response = [];
            $this->status = 500;
            $this->error = $e;
        }finally{
            $this->getResponse()->representJson(json_encode($this->response));
            $this->getResponse()->setStatusCode($this->status);
            parent::execute();
        }
    }

    public function __construct(\Shiekhdev\RetailOps\Model\Pull\OrderFactory $orderFactory,
                                \Magento\Framework\App\Action\Context $context )
    {
        $this->orderFactory = $orderFactory;
        parent::__construct($context);
        $this->logger = $this->_objectManager->get('\Shiekhdev\RetailOps\Logger\Logger');

    }
}