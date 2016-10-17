<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 12.9.16
 * Time: 15.14
 */

namespace RetailOps\Api\Model\Api\Map;


use Magento\Framework\App\ObjectManager;

class Order
{
    const CONFIGURABLE = 'configurable';
    /**
     * Status for retailops
     * @var array $retailopsItemStatus
     * http://gudtech.github.io/retailops-sdk/v1/channel/#!/default/post_order_pull_v1
     */
    public static $retailopsItemStatus = ['ship', 'advisory', 'instore'];

    /**
     * @var array $paymentProcessingType
     * from http://gudtech.github.io/retailops-sdk/v1/channel/#!/default/post_order_pull_v1
     */
    public static $paymentProcessingType = [
        'default' => 'channel_payment',
        'reward' => 'channel_storecredit',
        'gift' => 'channel_giftcert',
        'authorized' => 'authorize.net'
    ];

    /**
     * @param array $orders
     * @return array
     */
    public function getOrders($orders)
    {
        if (count($orders)) {
            $prepareOrders = [];
            foreach ($orders as $order) {
                $prepareOrders[] = Order::prepareOrder($order, $this);
            }

            return $prepareOrders;
        }
        return [];
    }

    /**
     * @param $order
     * @param Order $instance
     * @return mixed
     */
    static public function prepareOrder($order, $instance)
    {
        $prepareOrder = [];
        $prepareOrder['channel_order_refnum'] = $order->getId();
        $prepareOrder['currency_code'] = $order->getOrderCurrencyCode();
        $prepareOrder['currency_values'] = $instance->getCurrencyValues($order);
        $prepareOrder['channel_date_created'] = (new \DateTime($order->getCreatedAt(), new \DateTimeZone('UTC')))
            ->format('c');
        $prepareOrder['billing_address'] = $instance->getAddress($order, $order->getBillingAddress());
        $prepareOrder['shipping_address'] = $instance->getAddress($order, $order->getShippingAddress());
        $prepareOrder['order_items'] = $instance->getOrderItems($order);
        //add gift message if available
        if ($order->getGiftMessageAvailable()) {
            $giftHelper = ObjectManager::getInstance()->get('Magento\GiftMessage\Helper\Message');
            $message = $giftHelper->getGiftMessage($order->getGiftMessageId());
            $prepareOrder['gift_message'] = $message;
        }
        //@todo how send orders with coupon code and gift cart
        $prepareOrder['payment_transactions'] = $instance->getPaymentTransactions($order);
        $prepareOrder['customer_info'] = $instance->getCustmoerInfo($order);
        return $instance->clearNullValues($prepareOrder);
    }

    private function getCurrencyValues($order)
    {
        $values = [];
        $values['shipping_amt'] = (float)$order->getShippingAmount();
        $values['tax_amt'] = (float)$order->getTaxAmount();
        $values['discount_amt'] = (float)$order->getDiscountAmount();
        return $values;
    }

    /**
     * @var $address \Magento\Sales\Api\Data\OrderAddressInterface
     */
    private function getAddress($order, $address)
    {
        $addr = [];
        $addr['state_match'] = $address->getRegion();
        $addr['country_match'] = $address->getCountryId();
        $addr['last_name'] = $address->getLastname();
        if (is_array($address->getStreet()) && count($address->getStreet()) > 1) {
            $addr['address2'] = $address->getStreet()[1];
        }
        $addr['city'] = $address->getCity();
        $addr['postal_code'] = $address->getPostcode();
        $addr['address1'] = is_array($address->getStreet()) ? $address->getStreet()[0] : $address->getStreet();
        $addr['company'] = $address->getCompany();
        $addr['first_name'] = $address->getFirstname();
        return $addr;
    }

    /**
     * @param  \Magento\Sales\Model\Order $order
     * @return array
     */
    public function getOrderItems($order)
    {
        $items = [];
        $item = [];
        $orderItems = $order->getItems();
        foreach ($orderItems as $orderItem) {
            if ($orderItem->getParentItem()) {
                continue;
            }
            $item['channel_item_refnum'] = $orderItem->getId();
            $products = $orderItem->getChildrenItems();
            $product = reset($products);
            $item['sku'] = $orderItem->getChildrenItems()[0]->getProduct()->getUpc();
            $item['sku_description'] = sprintf('UPC: %s', $orderItem->getData('upc'));
            $item['quantity'] = $this->getQuantity($orderItem);
            $item['item_type'] = $this->getItemType($orderItem);
            $item['currency_values'] = $this->getItemCurrencyValues($orderItem);
            $items[] = $item;
        }
        return $items;

    }

    protected function getQuantity($item)
    {
        if ($item->getParentItem()) {
            $item = $item->getParentItem();
        }
        $qty = $item->getQtyOrdered() - $item->getQtyRefunded() - $item->getQtyCanceled();
        return (int)$qty;
    }

    protected function getItemType($item)
    {
        //@todo after design shiiping with retaiops add logic for orders
        return 'advisory';
    }

    /**
     * @param $item
     * @return array
     */
    public function getItemCurrencyValues($item)
    {
        $itemCurrency = [];
        if ($item->getParentItem()) {
            $item = $item->getParentItem();
        }
        $itemCurrency['discount_amt'] = (float)$item->getDiscountAmount();
        $itemCurrency['unit_price'] = (float)$item->getBasePrice();
        $itemCurrency['unit_tax'] = (float)$item->getTaxAmount();
        $itemCurrency['unit_tax_pct'] = (float)$item->getTaxPercent();
        return $itemCurrency;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    public function getPaymentTransactions($order)
    {
        $paymentR = [];
        $payment = $order->getPayment();
        $paymentR['payment_processing_type'] = self::$paymentProcessingType['default'];
        $paymentR['payment_type'] = $payment->getMethod();
        $paymentR['amount'] = (float)$payment->getBaseAmountPaid();
        return $this->getGiftPaymentTransaction([$paymentR], $order);

    }

    /**
     * @param array $payments
     * @param  \Magento\Sales\Model\Order $order
     * @return array
     */
    public function getGiftPaymentTransaction(array $payments, $order)
    {
        if ($order->getGiftCardsAmount() > 0) {
            $paymentG = [];
            $paymentG['payment_type'] = 'gift';
            $paymentG['payment_processing_type'] = self::$paymentProcessingType['gift'];
            $paymentG['amount'] = (float)$order->getBaseGiftCardsAmount();
            $payments[] = $paymentG;
        }
        return $payments;
    }

    /**
     * @param  \Magento\Sales\Model\Order $order
     * @return array
     */
    public function getCustmoerInfo($order)
    {
        $customerR = [];
        $customerR['email_address'] = $order->getCustomerEmail();
        if ($order->getCustomerIsGuest()) {
            $customerR['full_name'] = 'Guest';
        } else {
            $customerR['full_name'] = $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname();
        }
        return $customerR;
    }

    /**
     * @param  array $orders
     * @return mixed
     */
    public function clearNullValues(&$orders)
    {
        foreach ($orders as $key => &$order) {
            if (is_array($order)) {
                $this->clearNullValues($order);
            }
            if ($order === null) {
                unset($orders[$key]);
            }
        }
        return $orders;
    }
}