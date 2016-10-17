<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 17.10.16
 * Time: 14.11
 */

namespace RetailOps\Api\Service;


class InvoiceHelper
{
    /**
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    protected $invoiceService;

    protected function isInvoice($itemId, \Magento\Sales\Model\Order $order)
    {

    }

    public function createInvoice(\Magento\Sales\Model\Order $order, $items)
    {
        if($order->canInvoice()) {
            $invoice = $this->invoiceService->prepareInvoice($order, $items);
            if (!$invoice) {
                throw new LocalizedException(__('We can\'t save the invoice right now.'));
            }

            if (!$invoice->getTotalQty()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('You can\'t create an invoice without products.')
                );
            }
            //@todo create capture
//            $invoice->setRequestedCaptureCase($data['capture_case']);
                $invoice->addComment(
                    'Create for RetailOps'
                );


            $invoice->register();
            $invoice->getOrder()->setIsInProcess(true);

            $transactionSave = $this->_objectManager->create(
                'Magento\Framework\DB\Transaction'
            )->addObject(
                $invoice
            )->addObject(
                $invoice->getOrder()
            );

        }else{
            throw new \LogicException(__(sprintf('Canno\'t create invoice for this order: ', $order->getId())));
        }
        return $invoice->getId() ? true : false;
    }


    public function __construct(\Magento\Sales\Model\Service\InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

}