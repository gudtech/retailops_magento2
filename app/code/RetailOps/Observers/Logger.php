<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 26.9.16
 * Time: 13.38
 */

namespace Shiekhdev\RetailOps\Observers;


use Magento\Framework\App\ObjectManager;

class Logger implements \Magento\Framework\Event\ObserverInterface
{
    protected $loggerRetailOps;
    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
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

    public function __construct(\Shiekhdev\RetailOps\Model\LoggerFactory $loggerRetailOps)
    {
        $this->loggerRetailOps = $loggerRetailOps;
    }
}