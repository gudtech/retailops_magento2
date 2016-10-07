<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 8.9.16
 * Time: 13.45
 */

namespace Shiekhdev\RetailOps\Controller;

use Magento\Framework\App\RequestInterface;

abstract class RetailOps extends \Magento\Framework\App\Action\Action
{
    protected $action;
    protected $version;
    protected $client_id;
    protected $channel_info;
    public function dispatch(RequestInterface $request)
    {
        $this->setParams($request);
        return parent::dispatch($request);
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