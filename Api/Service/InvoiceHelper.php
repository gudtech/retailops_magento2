<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 17.10.16
 * Time: 14.11
 */

namespace RetailOps\Api\Service;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
class InvoiceHelper
{
    /**
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    protected $invoiceService;

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param $items
     * @return bool
     * @throws LocalizedException
     */
    public function createInvoice(\Magento\Sales\Model\Order $order, $items=[])
    {
        if ($order->canInvoice()) {
            $invoice = $this->invoiceService->prepareInvoice($order, $items);
            if (!$invoice) {
                throw new LocalizedException(__('We can\'t save the invoice right now.'));
            }

            if (!$invoice->getTotalQty()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('You can\'t create an invoice without products.')
                );
            }
             $invoice->addComment(
                    'Create for RetailOps'
               );


            $invoice->register();
            $invoice->getOrder()->setIsInProcess(true);
            return $this->saveInvoice($invoice);

        }else{
            return false;
        }
        return $invoice->getId() ? true : false;
    }

    /**
     * InvoiceHelper constructor.
     * @param \Magento\Sales\Model\Service\InvoiceService $invoiceService
     */
    public function __construct(\Magento\Sales\Model\Service\InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    /**
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     */
    public function saveInvoice(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        $transactionSave = ObjectManager::getInstance()->create(
            'Magento\Framework\DB\Transaction'
        )->addObject(
            $invoice
        )->addObject(
            $invoice->getOrder()
        )->save();
        return $invoice;
    }

}