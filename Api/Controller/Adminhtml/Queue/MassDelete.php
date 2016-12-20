<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 6.12.16
 * Time: 16.56
 */

namespace RetailOps\Api\Controller\Adminhtml\Queue;


class MassDelete extends \RetailOps\Api\Controller\Adminhtml\Queue
{
   public function execute()
   {
       $collection = $this->_objectManager->create('RetailOps\Api\Model\ResourceModel\Queue\Collection');
       $collection = $this->massFilter->getCollection($collection);
       $collectionSize = $collection->getSize();

       foreach ($collection as $item) {
           $item->delete();
       }

       $this->messageManager->addSuccess(__('A total of %1 record(s) have been deleted.', $collectionSize));

       /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
       $resultRedirect = $this->resultRedirectFactory->create();

       return $resultRedirect->setPath('*/*/');
   }
}