<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 26.9.16
 * Time: 13.38
 */

namespace RetailOps\Api\Observers;


use Magento\Framework\App\ObjectManager;

class Logger implements \Magento\Framework\Event\ObserverInterface
{
    const LOG_STATUS = 'retailops/RetailOps/enable_log';

    /**
     * @var \RetailOps\Api\Model\LoggerFactory
     */
    protected $loggerRetailOps;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->scopeConfig->getValue(self::LOG_STATUS)) {
            return;
        }
        $response = $observer->getResponse();
        $request = $observer->getRequest();
        if($request instanceof \Magento\Framework\App\Request\Http) {
            $request = (array)$request->getPost();
        }
        if( isset( $request['integration_auth_token'] )) {
            unset($request['integration_auth_token']);
        }
        $loggerRetailOps = $this->loggerRetailOps->create();
        $loggerRetailOps->setRequest(json_encode($request));
        $loggerRetailOps->setResponse(json_encode($response));
        $loggerRetailOps->setStatus($observer->getStatus());
        $loggerRetailOps->setUrl((string)$observer->getRequest()->getRequestString());
        $time = ObjectManager::getInstance()->get('Magento\Framework\Stdlib\DateTime\DateTime');
        $loggerRetailOps->setCreateDate($time->gmtDate());
        if(is_object($observer->getError())) {
            $loggerRetailOps->setError($observer->getError()->getMessage());
        }
        $loggerRetailOps->save();
    }

    public function __construct(\RetailOps\Api\Model\LoggerFactory $loggerRetailOps, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->loggerRetailOps = $loggerRetailOps;
        $this->scopeConfig = $scopeConfig;
    }
}