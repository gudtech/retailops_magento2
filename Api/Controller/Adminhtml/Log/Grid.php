<?php

namespace RetailOps\Api\Controller\Adminhtml\Log;


class Grid extends \RetailOps\Api\Controller\Adminhtml\Log
{
    public function execute()
    {
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }
}