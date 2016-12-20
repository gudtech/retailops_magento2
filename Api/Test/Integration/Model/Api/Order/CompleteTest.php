<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 8.11.16
 * Time: 16.20
 */

namespace RetailOps\Api\Test\Integration\Model\Api\Order;

use Magento\TestFramework\Helper\Bootstrap;

class CompleteTest extends \PHPUnit_Framework_TestCase
{
    const INCREMENT_1 = '100000001';
    protected $postData = [
        'channel_order_refnum' => 'xxxxxxxxxxxx',
        'grand_total' => 'xxxxxx',
        'retailops_order_id' =>8375687,
        'shipment'

    ];
    protected function setUp()
    {
        Bootstrap::getObjectManager()->get('Magento\Framework\App\AreaList')
            ->getArea('adminhtml')
            ->load(\Magento\Framework\App\Area::PART_CONFIG);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testCompleteOrder()
    {
        $this->setPostDataAllShipment();
        $objectManager = Bootstrap::getObjectManager();
        $orderComplete = $objectManager->create('RetailOps\Api\Model\Api\Order\Complete');
        /**
         * @var \RetailOps\Api\Model\Api\Order\Complete $orderComplete 
         */
        $orderComplete->completeOrder($this->postData);

        /**
         * @var \Magento\Sales\Model\Order $order
         */
        $order = $objectManager->get('Magento\Sales\Model\Order');
        $order->loadByIncrementId(self::INCREMENT_1);
        $this->assertEquals('complete', $order->getStatus());
        foreach ($order->getItems() as $item) {
            $this->assertEquals($item->getQtyOrdered(), $item->getQtyInvoiced());
            $this->assertEquals($item->getQtyOrdered(), $item->getQtyShipped());
        }
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @magentoDataFixture Magento/Sales/_files/invoice.php
     * @magentoDataFixture Magento/Sales/_files/shipment.php
     * @magentoDataFixture ../../../../app/code/RetailOps/Api/Test/Integration/_files/add_product.php
     */
    public function testCompleteOrderPartShipment()
    {
        $this->setPostDataAllShipment();
        $objectManager = Bootstrap::getObjectManager();
        $orderComplete = $objectManager->create('RetailOps\Api\Model\Api\Order\Complete');
        /**
         * @var \RetailOps\Api\Model\Api\Order\Complete $orderComplete
         */
        $orderComplete->completeOrder($this->postData);

        /**
         * @var \Magento\Sales\Model\Order $order
         */
        $order = $objectManager->get('Magento\Sales\Model\Order');
        $order->loadByIncrementId(self::INCREMENT_1);
        $this->assertEquals('complete', $order->getStatus());
        foreach ($order->getItems() as $item) {
            $this->assertEquals($item->getQtyOrdered(), $item->getQtyInvoiced());
            $this->assertEquals($item->getQtyOrdered(), $item->getQtyShipped());
        }
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @magentoDataFixture Magento/Sales/_files/invoice.php
     * @magentoDataFixture Magento/Sales/_files/shipment.php
     */
    public function testCompleteOrderAfterShipment()
    {
        $this->setPostDataAllShipment();
        $objectManager = Bootstrap::getObjectManager();
        $orderSubmit = $objectManager->create('RetailOps\Api\Model\Shipment\ShipmentSubmit');
        /**
         * @var \RetailOps\Api\Model\Shipment\ShipmentSubmit $orderSubmit
         */
        $orderSubmit->updateOrder($this->postData);
        $orderComplete = $objectManager->create('RetailOps\Api\Model\Api\Order\Complete');
        /**
         * @var \RetailOps\Api\Model\Api\Order\Complete $orderComplete
         */
        $orderComplete->completeOrder($this->postData);

        /**
         * @var \Magento\Sales\Model\Order $order
         */
        $order = $objectManager->get('Magento\Sales\Model\Order');
        $order->loadByIncrementId(self::INCREMENT_1);
        $this->assertEquals('complete', $order->getStatus());
        foreach ($order->getItems() as $item) {
            $this->assertEquals($item->getQtyOrdered(), $item->getQtyInvoiced());
            $this->assertEquals($item->getQtyOrdered(), $item->getQtyShipped());
        }
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @magentoDataFixture ../../../../app/code/RetailOps/Api/Test/Integration/_files/add_three_product.php
     * @magentoDataFixture ../../../../app/code/RetailOps/Api/Test/Integration/_files/invoice.php
     */
    public function testCompleteOrderBeforeShipment()
    {
        $this->setPostDataTwoShipment();
        $objectManager = Bootstrap::getObjectManager();
        $orderSubmit = $objectManager->create('RetailOps\Api\Model\Shipment\ShipmentSubmit');
        /**
         * @var \RetailOps\Api\Model\Shipment\ShipmentSubmit $orderSubmit
         */
        $orderSubmit->updateOrder($this->postData);
        $orderComplete = $objectManager->create('RetailOps\Api\Model\Api\Order\Complete');
        /**
         * @var \RetailOps\Api\Model\Api\Order\Complete $orderComplete
         */
        $orderComplete->completeOrder($this->postData);

        /**
         * @var \Magento\Sales\Model\Order $order
         */
        $order = $objectManager->get('Magento\Sales\Model\Order');
        $order->loadByIncrementId(self::INCREMENT_1);
        $this->assertEquals('complete', $order->getStatus());
        foreach ($order->getItems() as $item) {
            $this->assertEquals(2, $item->getQtyInvoiced()-$item->getQtyRefunded() -$item->getQtyCanceled());
            $this->assertEquals(2, $item->getQtyShipped());
        }
        $this->assertEquals(1,$item->getQtyRefunded());
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @magentoDataFixture Magento/Sales/_files/invoice.php
     */
    public function testCompleteOrderOneRefund()
    {
        $this->setPostDataOneRefund();
        $objectManager = Bootstrap::getObjectManager();
        $orderComplete = $objectManager->create('RetailOps\Api\Model\Api\Order\Complete');
        /**
         * @var \RetailOps\Api\Model\Api\Order\Complete $orderComplete
         */
        $orderComplete->completeOrder($this->postData);

        /**
         * @var \Magento\Sales\Model\Order $order
         */
        $order = $objectManager->get('Magento\Sales\Model\Order');
        $order->loadByIncrementId(self::INCREMENT_1);
        $this->assertEquals('complete', $order->getStatus());
        foreach ($order->getItems() as $item) {
            $this->assertEquals($item->getQtyOrdered(), $item->getQtyInvoiced());
            $this->assertEquals(1, $item->getQtyShipped());
            $this->assertEquals(1, $item->getQtyRefunded());
        }
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @magentoDataFixture Magento/Sales/_files/invoice.php
     * @magentoDataFixture ../../../../app/code/RetailOps/Api/Test/Integration/_files/add_product.php
     */
    public function testCompleteOrderOneCancel()
    {
        $this->setPostDataOneShipment();
        $objectManager = Bootstrap::getObjectManager();
        $orderComplete = $objectManager->create('RetailOps\Api\Model\Api\Order\Complete');
        /**
         * @var \RetailOps\Api\Model\Api\Order\Complete $orderComplete
         */
        $orderComplete->completeOrder($this->postData);

        /**
         * @var \Magento\Sales\Model\Order $order
         */
        $order = $objectManager->get('Magento\Sales\Model\Order');
        $order->loadByIncrementId(self::INCREMENT_1);
        $this->assertEquals('complete', $order->getStatus());
        $items = $order->getItems();
        $item = array_shift($items);
        $this->assertEquals($item->getQtyOrdered(), $item->getQtyInvoiced());
        $this->assertEquals(2, $item->getQtyShipped());
        $item = array_shift($items);
        $this->assertEquals(1, $item->getQtyCanceled());
    }

    protected function setPostDataAllShipment()
    {
        $postData = [];
        $objectManager = Bootstrap::getObjectManager();
        $order = $objectManager->get('Magento\Sales\Model\Order');
        $order->loadByIncrementId(self::INCREMENT_1);
        $postData['channel_order_refnum'] = $order->getIncrementId();
        $postData['grand_total'] = $order->getBaseGrandTotal();
        $postData['retailops_order_id'] = 8375687;
        $package["date_shipped"] = "2016-11-08T20:46:17Z";
        $packageItems = [];
        /**
         * @var \Magento\Sales\Api\Data\OrderInterface
         */
        $items = $order->getItems();
        $packagesItems = [];
        foreach ($items as $item) {
            $packageItem['channel_item_refnum'] = $item->getId();
            $packageItem['quantity'] = $item->getQtyOrdered();
            $packageItem["retailops_order_item_id"] = 68454;
            $packageItem["retailops_shipment_item_id"] = 0;
            $packageItem['sku'] = '889772351387';
            $packagesItems[] = $packageItem;

        }
        $packageItemInfo = [];
        $packageItemInfo['package_items'] = $packagesItems;
        $packageItemInfo['retailops_package_id'] = 32532;
        $packageItemInfo['tracking_number'] = 'TEST1ZRw232702823403233172401';
        $packageItemInfo['weight_kg'] = 1.5;
        $packageItemInfo['carrier_class_name'] = "UPS Ground";
        $packageItemInfo['carrier_name'] = "UPS";
        $packageItems[] = $packageItemInfo;
        $package["packages"] = $packageItems;
        $postData['shipments'][] = $package;
        $this->postData = $postData;

    }
    protected function setPostDataOneShipment()
    {
        $postData = [];
        $objectManager = Bootstrap::getObjectManager();
        $order = $objectManager->get('Magento\Sales\Model\Order');
        $order->loadByIncrementId(self::INCREMENT_1);
        $postData['channel_order_refnum'] = $order->getIncrementId();
        $postData['grand_total'] = $order->getBaseGrandTotal();
        $postData['retailops_order_id'] = 8375687;
        $package["date_shipped"] = "2016-11-08T20:46:17Z";
        $packageItems = [];
        /**
         * @var \Magento\Sales\Api\Data\OrderInterface
         */
        $items = $order->getItems();
        foreach ($items as $item) {
            $packageItem['channel_item_refnum'] = $item->getId();
            $packageItem['quantity'] = $item->getQtyOrdered();
            $packageItem["retailops_order_item_id"] = 68454;
            $packageItem["retailops_shipment_item_id"] = 0;
            $packageItem['sku'] = '889772351387';
            break;

        }
        $packageItemInfo = [];
        $packageItemInfo['package_items'][] = $packageItem;
        $packageItemInfo['retailops_package_id'] = 32532;
        $packageItemInfo['tracking_number'] = 'TEST1ZRw232702823403233172401';
        $packageItemInfo['weight_kg'] = 1.5;
        $packageItemInfo['carrier_class_name'] = "UPS Ground";
        $packageItemInfo['carrier_name'] = "UPS";
        $packageItems[] = $packageItemInfo;
        $package["packages"] = $packageItems;
        $postData['shipments'][] = $package;
        $this->postData = $postData;

    }

    protected function setPostDataTwoShipment()
    {
        $postData = [];
        $objectManager = Bootstrap::getObjectManager();
        $order = $objectManager->get('Magento\Sales\Model\Order');
        $order->loadByIncrementId(self::INCREMENT_1);
        $postData['channel_order_refnum'] = $order->getIncrementId();
        $postData['grand_total'] = $order->getBaseGrandTotal();
        $postData['retailops_order_id'] = 8375687;
        $package["date_shipped"] = "2016-11-08T20:46:17Z";
        $packageItems = [];
        /**
         * @var \Magento\Sales\Api\Data\OrderInterface
         */
        $items = $order->getItems();
        $packagesItems = [];
        foreach ($items as $item) {
            $packageItem['channel_item_refnum'] = $item->getId();
            $packageItem['quantity'] = 2;
            $packageItem["retailops_order_item_id"] = 68454;
            $packageItem["retailops_shipment_item_id"] = 0;
            $packageItem['sku'] = '889772351387';
            $packagesItems[] = $packageItem;

        }
        $packageItemInfo = [];
        $packageItemInfo['package_items'] = $packagesItems;
        $packageItemInfo['retailops_package_id'] = 32532;
        $packageItemInfo['tracking_number'] = 'TEST1ZRw232702823403233172401';
        $packageItemInfo['weight_kg'] = 1.5;
        $packageItemInfo['carrier_class_name'] = "UPS Ground";
        $packageItemInfo['carrier_name'] = "UPS";
        $packageItems[] = $packageItemInfo;
        $package["packages"] = $packageItems;
        $postData['shipments'][] = $package;
        $this->postData = $postData;
    }

    protected function setPostDataOneRefund()
    {
        $postData = [];
        $objectManager = Bootstrap::getObjectManager();
        $order = $objectManager->get('Magento\Sales\Model\Order');
        $order->loadByIncrementId(self::INCREMENT_1);
        $postData['channel_order_refnum'] = $order->getIncrementId();
        $postData['grand_total'] = $order->getBaseGrandTotal();
        $postData['retailops_order_id'] = 8375687;
        $package["date_shipped"] = "2016-11-08T20:46:17Z";
        $packageItems = [];
        /**
         * @var \Magento\Sales\Api\Data\OrderInterface
         */
        $items = $order->getItems();
        foreach ($items as $item) {
            $packageItem['channel_item_refnum'] = $item->getId();
            $packageItem['quantity'] = 1;
            $packageItem["retailops_order_item_id"] = 68454;
            $packageItem["retailops_shipment_item_id"] = 0;
            $packageItem['sku'] = '889772351387';
        }
        $packageItemInfo = [];
        $packageItemInfo['package_items'][] = $packageItem;
        $packageItemInfo['retailops_package_id'] = 32532;
        $packageItemInfo['tracking_number'] = 'TEST1ZRw232702823403233172401';
        $packageItemInfo['weight_kg'] = 1.5;
        $packageItemInfo['carrier_class_name'] = "UPS Ground";
        $packageItemInfo['carrier_name'] = "UPS";
        $packageItems[] = $packageItemInfo;
        $package["packages"] = $packageItems;
        $postData['shipments'][] = $package;
        $packageItem['unshipped_quantity'] = 1;
        unset($packageItem['quantity']);
        $postData['unshipped_items'][] = $packageItem;
        $this->postData = $postData;
    }
}