<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 14.11.16
 * Time: 15.10
 */

namespace RetailOps\Api\Service\CreditMemo;

use Magento\Framework\App\ObjectManager;
use RetailOps\Api\Api\Services\CreditMemo\CreditMemoHelperInterface;

class CreditMemoHelper implements CreditMemoHelperInterface
{
    use \RetailOps\Api\Model\Api\Traits\FullFilter;
    /**
     * @var float
     */
    protected $adjustmentPositive=0;

    /**
     * @var float
     */
    protected $refundCustomerbalanceReturnEnable=0;

    /**
     * @var float
     */
    protected $adjustmentNegative=0;

    /**
     * @var float
     */
    protected $shippingAmount=0;

    /**
     * @var \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader
     */
    protected $creditmemoLoader;

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\CreditmemoSender
     */
    protected $creditmemoSender;

    /**
     * @var \Magento\Sales\Model\Order\InvoiceRepository
     */
    protected $invoiceRepository;

    /**
     * @var \Magento\Framework\App\ObjectManager
     */
    protected $_objectManager;
    /**
     * @param \Magento\Sales\Api\Data\OrderItemInterface $orderItem
     * @return float
     */
    public function getQuantity(\Magento\Sales\Api\Data\OrderItemInterface $orderItem, $value)
    {
       $value = (float)$value;
       if($orderItem->getParentItem())
       {
           $orderItemForCalc = $orderItem->getParentItem();
       } else {
           $orderItemForCalc = $orderItem;
       }
        $delta = (float)$orderItemForCalc->getQtyOrdered()
                    - (float)$orderItemForCalc->getQtyInvoiced()
                    - (float)$orderItemForCalc->getQtyRefunded()
                    - (float)$orderItemForCalc->getQtyCanceled();
        if($delta >= $value)
        {
            return 0;
        }

        $qtyCreditMemo = $value - $delta;
        if( $qtyCreditMemo < 0 || $orderItemForCalc->getQtyOrdered() < $qtyCreditMemo )
        {
            throw new \LogicException('Qty of creditmemo more than quantity of invoice, item:'.$orderItemForCalc->getId());
        }
        return $qtyCreditMemo;

    }

    /**
     * check, if we need create credit memo for product
     * @param \Magento\Sales\Model\Order $order
     * @param array ['id'=>'quantity'] $items
     * @return array
     */
    public function needCreditMemo(\Magento\Sales\Model\Order $order, $items=[])
    {
        /**
         * @var \Magento\Sales\Api\Data\OrderItemInterface[] $itemsOrder
         */
        $itemsOrder = $order->getItems();
        $creditMemoItems = [];
        foreach ($items as $key => $value)
        {
            foreach ($itemsOrder as $itemOrder)
            {
                if((string)$itemOrder->getId() === (string)$key) {
                    $quantity = $this->getCreditMemoQuantity($itemOrder, $value);
                    if ($quantity>0) {
                        $creditMemoItems[$key] = $quantity;
                    }
                }
            }
        }
        return $creditMemoItems;
    }

    public function getCreditMemoQuantity($itemOrder, $value)
    {
        return $this->getQuantity($itemOrder, $value);
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param array $items
     * @return boolean
     */
    public function create(\Magento\Sales\Api\Data\OrderInterface $order, array $items)
    {
        $this->creditmemoLoader->setOrderId($order->getId());
        $this->creditmemoLoader->setCreditmemo($this->getPrepareCreditmemoData($order, $items));
        $invoice = $this->getInvoice($order, $items);
        if($invoice) {
            $this->creditmemoLoader->setInvoiceId($invoice->getId());
        }

        $creditmemo = $this->creditmemoLoader->load();
        if ($creditmemo) {
            if (!$creditmemo->isValidGrandTotal()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('The credit memo\'s total must be positive.')
                );
            }

            /**
             *@var  \Magento\Sales\Api\CreditmemoManagementInterface $creditmemoManagement
             */
            $creditmemoManagement = $this->_objectManager->create(
                'Magento\Sales\Api\CreditmemoManagementInterface'
            );
            /**
             * $creditmemo, offline/online, send_email
             */
            $creditmemoManagement->refund($creditmemo, $this->isOfflineRefund($order), 0);

            /**
             * for now it commented
             */
            if (false) {
                $this->creditmemoSender->send($creditmemo);
            }
        }
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param array $items
     * @return
     */
    public function getInvoice(\Magento\Sales\Api\Data\OrderInterface $order, $items)
    {
        $filter = $this->createFilter('order_id', 'eq', $order->getId());
        $this->addFilter('invoices', $filter);
        $this->addFilterGroups();
        $invoices = $this->invoiceRepository->getList($this->searchCriteria);
        foreach ($invoices as $invoice)
        {
            //return first invoice
            return $invoice;
        }

        return null;
    }


    /**
     * @param $order
     * @param $items
     * @return array
     */
    public function getPrepareCreditmemoData(\Magento\Sales\Api\Data\OrderInterface $order, array $items)
    {
        $prepare = [];
        $convertItems = [];
        foreach ($items as $id=>$quantity)
        {
            $convertItems[$id] = ['qty'=>(float)$quantity];
        }
        $prepare['items'] = $convertItems;
        $prepare['do_offline'] = $this->setDoOffline($order, $items);
        $prepare['comment_text'] = $this->getCommentText($order);
        $prepare['shipping_amount'] = $this->getShippingAmount($order, $items);
        $prepare['adjustment_positive'] = $this->getAdjustmentPositive($order, $items);
        $prepare['adjustment_negative'] = $this->getAdjustmentNegative($order, $items);
        $prepare['refund_customerbalance_return_enable'] = $this->getRefundCustomerbalanceReturnEnable($order, $items);
        return $prepare;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param array $items
     * @return int
     */
    public function getAdjustmentPositive(\Magento\Sales\Api\Data\OrderInterface $order, array $items)
    {
        return $this->adjustmentPositive;
    }

    public function getRefundCustomerbalanceReturnEnable(\Magento\Sales\Api\Data\OrderInterface $order, array $items)
    {
        return $this->refundCustomerbalanceReturnEnable;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param array $items
     * @return int
     */
    public function getAdjustmentNegative(\Magento\Sales\Api\Data\OrderInterface $order, array $items)
    {
        return $this->adjustmentNegative;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param array $items
     * @return int
     */
    public function getShippingAmount(\Magento\Sales\Api\Data\OrderInterface $order, array $items)
    {
        return $this->shippingAmount;
    }

    public function getCommentText(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        return __('Create for RetailOps response');
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param  array $items
     * @return bool
     */
    public function setDoOffline(\Magento\Sales\Api\Data\OrderInterface $order, array $items)
    {
        return $this->isOfflineRefund($order);
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return bool
     */
    public function isOfflineRefund( \Magento\Sales\Api\Data\OrderInterface $order)
    {
        $payment = $order->getPayment();
        if($payment && $payment->getBaseAmountPaidOnline() > 0) {
            return 0;
        }
        return 1;

    }

    public function __construct(
        \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader $creditmemoLoader,
        \Magento\Sales\Model\Order\Email\Sender\CreditmemoSender $creditmemoSender,
        \Magento\Sales\Model\Order\InvoiceRepository $invoiceRepository
    )
    {
        $this->creditmemoLoader = $creditmemoLoader;
        $this->_objectManager = ObjectManager::getInstance();
        $this->creditmemoSender = $creditmemoSender;
        $this->invoiceRepository = $invoiceRepository;
    }

    public function setAdjustmentPositive($amount)
    {
        $this->adjustmentNegative = $amount;
    }

    public function setRefundCustomerbalanceReturnEnable($amount)
    {
        $this->refundCustomerbalanceReturnEnable = $amount;
    }

    public function setAdjustmentNegative($amount)
    {
        $this->adjustmentNegative = $amount;
    }

    public function setShippingAmount($amount)
    {
        $this->shippingAmount = $amount;
    }


}