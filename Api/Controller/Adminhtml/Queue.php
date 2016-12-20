<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 6.12.16
 * Time: 11.57
 */

namespace RetailOps\Api\Controller\Adminhtml;

abstract class Queue extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;
    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $resultRedirectFactory;
    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $massFilter;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Ui\Component\MassAction\Filter $massFilter
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
        $this->massFilter = $massFilter;
        parent::__construct($context);
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('RetailOps_Api::inventory');
    }

    protected function _init()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu(
            'RetailOps_Api::cancel_queue'
        )->_addBreadcrumb(
            __('RetailOps'),
            __('Cancel queue')
        );
        return $this;
    }
}