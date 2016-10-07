<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 23.9.16
 * Time: 11.38
 */

namespace Shiekhdev\RetailOps\Controller\Frontend\Order;


use Magento\Framework\App\ObjectManager;
use \Shiekhdev\RetailOps\Controller\RetailOps;

class Cancel extends RetailOps
{
    const SERVICENAME = 'order_cancel';
    /**
     * @var array|null
     */
    protected $response;

    protected $status = 200;


    public function execute()
    {
        try{
            $postData = $this->getRequest()->getPost();
            $orderFactrory = $this->orderFactory->create();
            $response = $orderFactrory->cancelOrder($postData);
            $serviceName = self::SERVICENAME;
            $areaName = "retailops_before_pull_{$serviceName}";
            $this->_eventManager->dispatch($areaName, [
                'response' => $response,
                'request' => $this->getRequest(),
            ]);
            $this->response = $response;
        }catch(\Exception $e){
            $this->logger->addCritical($e->getMessage());
            $this->response = (object)null;
            $this->status = 500;
        }finally{
            $this->getResponse()->representJson(json_encode($this->response));
            $this->getResponse()->setStatusCode($this->status);
        }
    }

    public function __construct(\Shiekhdev\RetailOps\Model\Order\CancelFactory $orderFactory,
                                \Magento\Framework\App\Action\Context $context )
    {
        $this->orderFactory = $orderFactory;
        parent::__construct($context);
        $this->logger = $this->_objectManager->get('\Shiekhdev\RetailOps\Logger\Logger');

    }
}