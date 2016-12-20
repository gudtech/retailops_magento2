<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 21.11.16
 * Time: 11.50
 */

namespace RetailOps\Api\Test\Integration\Model\Api\Order;

use Magento\TestFramework\Helper\Bootstrap;

class CancelTest extends \PHPUnit_Framework_TestCase
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
    public function testCancel()
    {
        $objectManager = Bootstrap::getObjectManager();
        /**
         * @var \RetailOps\Api\Model\Api\Order\Cancel $orderCancel
         */
        $orderCancel = $objectManager->create('RetailOps\Api\Model\Api\Order\Cancel');
        $orderCancel->cancel(['channel_order_refnum'=>self::INCREMENT_1]);
        $order = $objectManager->get('Magento\Sales\Model\Order');
        $order->loadByIncrementId(self::INCREMENT_1);
        $this->assertEquals('canceled', $order->getStatus());
        foreach ($order->getItems() as $item) {
            $this->assertEquals($item->getQtyOrdered(), $item->getQtyCanceled());
        }
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @magentoDataFixture Magento/Sales/_files/invoice.php
     */
    public function testCancelRefund()
    {
        $objectManager = Bootstrap::getObjectManager();
        /**
         * @var \RetailOps\Api\Model\Api\Order\Cancel $orderCancel
         */
        $orderCancel = $objectManager->create('RetailOps\Api\Model\Api\Order\Cancel');
        $orderCancel->cancel(['channel_order_refnum'=>self::INCREMENT_1]);
        $order = $objectManager->get('Magento\Sales\Model\Order');
        $order->loadByIncrementId(self::INCREMENT_1);
        $this->assertEquals('complete', $order->getStatus());
        foreach ($order->getItems() as $item) {
            $this->assertEquals($item->getQtyOrdered(), $item->getQtyRefunded());
        }
    }
}