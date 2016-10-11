<?php

namespace \RetailOps\Api\Plugin;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\AuthenticationException;

class Authorized
{
    const integration_key_value = 'retailops/_RetailOps/password';
    const integration_key = 'integration_auth_token';
    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $response;

    /**
     * @var \Magento\Framework\Webapi\ErrorProcessor
     */
    protected $errorProcessor;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function aroundDispatch($subject, $proceed, $request)
    {
        try{
            $key = $request->getPost('integration_auth_token');
            $valid_key = $this->scopeConfig->getValue(self::integration_key_value);
            if (!$key || $valid_key !== $key) {
                throw new \Magento\Framework\Exception\AuthenticationException(
                    __('A retailops having the specified key does not exist')
                );
            }
            return $proceed($request);
        }catch (\Exception $e){
            if ($e instanceof AuthenticationException){
                $this->response->setContent(__('Cannot authorized'));
                $this->response->setStatusCode('401');
            }else{
                $this->response->setContent(__('Error occur while do request'));
                $this->response->setStatusCode('500');
            }
            $logger = ObjectManager::getInstance()->get('\RetailOps\Api\Logger\Logger');
            $logger->addCritical('Error in retailops:'.$e->getMessage(), (array)$request->getPost());
            return $this->response;
        }


    }

    public function __construct(Context $context, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->response = $context->getResponse();
        $this->scopeConfig = $scopeConfig;
    }

}