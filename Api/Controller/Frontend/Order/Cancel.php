<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 23.9.16
 * Time: 11.38
 */

namespace RetailOps\Api\Controller\Frontend\Order;


use Magento\Framework\App\ObjectManager;
use \RetailOps\Api\Controller\RetailOps;

class Cancel extends RetailOps
{
    const SERVICENAME = 'order_cancel';
    /**
     * @var string
     */
    protected $areaName = self::BEFOREPULL.self::SERVICENAME;
    public function execute()
    {
        try{
            $postData = $this->getRequest()->getPost();
            $orderFactrory = $this->orderFactory->create();
            $response = $orderFactrory->cancelOrder($postData);
            $this->response = $response;
        }catch(\Exception $e){
            $this->logger->addCritical($e->getMessage());
            $this->response = (object)null;
            $this->status = 500;
            $this->error = $e;
        }finally{
            $this->getResponse()->representJson(json_encode($this->response));
            $this->getResponse()->setStatusCode($this->status);
            parent::execute();
        }
    }

    public function __construct(\RetailOps\Api\Model\Order\CancelFactory $orderFactory,
                                \Magento\Framework\App\Action\Context $context )
    {
        $this->orderFactory = $orderFactory;
        parent::__construct($context);
        $this->logger = $this->_objectManager->get('\RetailOps\Api\Logger\Logger');

    }
}