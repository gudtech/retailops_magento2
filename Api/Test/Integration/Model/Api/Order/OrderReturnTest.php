<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 29.11.16
 * Time: 10.54
 */

namespace RetailOps\Api\Test\Integration\Model\Api\Order;

use Magento\TestFramework\Helper\Bootstrap;

class OrderReturnTest extends \PHPUnit_Framework_TestCase
{
    const INCREMENT_1 = '100000001';

    protected $postData;

    protected function setUp()
    {
        Bootstrap::getObjectManager()->get('Magento\Framework\App\AreaList')
            ->getArea('adminhtml')
            ->load(\Magento\Framework\App\Area::PART_CONFIG);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testReturnData()
    {
    $this->setPostDataAllReturn();
    $objectManager = Bootstrap::getObjectManager();
    $orderReturn = $objectManager->create('RetailOps\Api\Model\Api\Order\OrderReturn');
    /**
     * @var \RetailOps\Api\Model\Api\Order\Complete $orderComplete
     */
    $orderReturn->returnData($this->postData);

    /**
     * @var \Magento\Sales\Model\Order $order
     */
    $order = $objectManager->get('Magento\Sales\Model\Order');
    $order->loadByIncrementId(self::INCREMENT_1);
    $this->assertEquals('canceled', $order->getStatus());
    foreach ($order->getItems() as $item)
    {
            $this->assertEquals($item->getQtyOrdered(), $item->getQtyCanceled());
    }
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @magentoDataFixture Magento/Sales/_files/invoice.php
     */
    public function testReturnAfterInvoice()
    {
        $this->setPostDataAllReturn();
        $objectManager = Bootstrap::getObjectManager();
        $orderReturn = $objectManager->create('RetailOps\Api\Model\Api\Order\OrderReturn');
        /**
         * @var \RetailOps\Api\Model\Api\Order\Complete $orderComplete
         */
        $orderReturn->returnData($this->postData);

        /**
         * @var \Magento\Sales\Model\Order $order
         */
        $order = $objectManager->get('Magento\Sales\Model\Order');
        $order->loadByIncrementId(self::INCREMENT_1);
        $this->assertEquals('closed', $order->getStatus());
        foreach ($order->getItems() as $item) {
            $this->assertEquals($item->getQtyOrdered(), $item->getQtyRefunded());
        }
    }

    public function setPostDataAllReturn()
    {
        $postData = [];
        $objectManager = Bootstrap::getObjectManager();
        $order = $objectManager->get('Magento\Sales\Model\Order');
        $order->loadByIncrementId(self::INCREMENT_1);
        $postData['order']['channel_order_refnum'] = $order->getIncrementId();
        $postData['order']['grand_total'] = $order->getBaseGrandTotal();
        $postData['order']['retailops_order_id'] = 8375687;
        $package["date_shipped"] = "2016-11-08T20:46:17Z";
        $packageItems = [];
        /**
         * @var \Magento\Sales\Api\Data\OrderInterface
         */
        $items = $order->getItems();
        $packagesItems = [];
        $returnItems = [];
        foreach ($items as $item) {
            $packageItem['channel_item_refnum'] = $item->getId();
            $packageItem['quantity'] = $item->getQtyOrdered();
            $packageItem["retailops_order_item_id"] = 68454;
            $packageItem["retailops_shipment_item_id"] = 0;
            $packageItem['sku'] = '889772351387';
            $packagesItems[] = $packageItem;
            //add to return section
            $returnItems = $packageItem;

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
        $postData['order']['shipments'][] = $package;
        $this->postData = $postData;
    }

}