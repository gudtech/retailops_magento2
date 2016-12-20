<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 6.12.16
 * Time: 11.59
 */

namespace RetailOps\Api\Controller\Adminhtml\Queue;


class Cancel extends \RetailOps\Api\Controller\Adminhtml\Queue
{
    public function execute()
    {
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }
}