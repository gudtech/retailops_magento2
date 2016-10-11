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

class Acknowledge  extends RetailOps
{
    const SERVICENAME = 'order_acknowledge';
    /**
     * @var string
     */
    protected $areaName = self::BEFOREPULL.self::SERVICENAME;

    /**
     * @var \Shiekhdev\RetailOps\Model\Acknowledge
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
            $postData = $this->getRequest()->getPost();
            $orderFactrory = $this->orderFactory->create();
            $response = $orderFactrory->setOrderRefs($postData);
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

    public function __construct(\Shiekhdev\RetailOps\Model\AcknowledgeFactory $orderFactory,
                                \Magento\Framework\App\Action\Context $context )
    {
        $this->orderFactory = $orderFactory;
        parent::__construct($context);
        $this->logger = $this->_objectManager->get('\Shiekhdev\RetailOps\Logger\Logger');

    }
}