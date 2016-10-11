<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 8.9.16
 * Time: 13.45
 */

namespace RetailOps\Api\Controller;

use Magento\Framework\App\RequestInterface;

abstract class RetailOps extends \Magento\Framework\App\Action\Action
{
    const BEFOREPULL = 'retailops_before_pull_';


    /**
     * @var string
     */
    protected $action;

    /**
     * @var int
     */
    protected $version;
    /**
     * @var int
     */
    protected $client_id;
    /**
     * @var int
     */
    protected $channel_info;
    /**
     * @var \Exception
     */
    protected $error;
    /**
     * @var array
     */
    protected $response;

    /**
     * @var int
     */
    protected $status = 200;


    public function dispatch(RequestInterface $request)
    {
        $this->setParams($request);
        return parent::dispatch($request);
    }

    /**
     * Use this method for logging system
     */
    public function execute()
    {
        $this->_eventManager->dispatch($this->areaName, [
            'response' => $this->response,
            'request' => $this->getRequest(),
            'error' => $this->error,
            'status' => $this->status,
        ]);
    }

    protected function setParams($request)
    {
        $this->setAction($request);
        $this->setVersion($request);
        $this->setClientId($request);
        $this->setChannelInfo($request);
    }

    protected function setAction($request)
    {
        $this->action = $request->getParam('action');
    }

    protected function setVersion($request)
    {
        $this->version = $request->getParam('version');
    }

    protected function setClientId($request)
    {
        $this->client_id = $request->getParam('client_id');
    }

    protected function setChannelInfo($request)
    {
        $this->channel_info = $request->getParam('channel_info');
    }

    public function getAction()
    {
        return $this->action;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function getClientId()
    {
        return $this->client_id;
    }

    public function getChannelInfo()
    {
        return $this->channel_info;
    }

}