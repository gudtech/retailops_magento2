<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 14.10.16
 * Time: 12.42
 */

namespace RetailOps\Api\Test\Unit\Service\Order;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
class CheckTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Api\Data\OrderInterface
     */
    protected $orderMockWrong;
    /**
     * @var \Magento\Sales\Api\Data\OrderInterface
     */
    protected $orderMockRight;

    /**
     * @var \Magento\Sales\Api\Data\OrderItemInterface
     */
    protected $item1;

    /**
     * @var
     */
    protected $item2;

    /**
     * @var \Magento\Sales\Api\Data\OrderItemInterface[]
     */
    protected $items;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManagerHelper;

    /**
     * @var \RetailOps\Api\Service\OrderCheck
     */
    protected $model;
    public function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->orderMockRight = $this->getMockBuilder('Magento\Sales\Model\Order')
                                ->disableOriginalConstructor()
                                ->getMock();
        $this->orderMockRight->expects($this->any())
                        ->method('canInvoice')
                        ->willReturn(true);
        $this->orderMockRight->expects($this->any())
                        ->method('canShip')
                        ->willReturn(true);

        $this->orderMockRight->expects($this->any())
                        ->method('getForcedShipmentWithInvoice')
                        ->willReturn(true);
        $this->orderMockRight->expects($this->any())
            ->method('getCanShipPartially')
            ->willReturn(true);
        $this->orderMockRight->expects($this->any())
            ->method('getCanShipPartiallyItem')
            ->willReturn(true);

        $this->orderMockWrong = $this->getMockBuilder('\Magento\Sales\Model\Order')
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderMockWrong->expects($this->any())
            ->method('canInvoice')
            ->willReturn(false);
        $this->orderMockWrong->expects($this->any())
            ->method('canShip')
            ->willReturn(false);
        $this->orderMockWrong->expects($this->any())
            ->method('getForcedShipmentWithInvoice')
            ->willReturn(false);
        $this->orderMockWrong->expects($this->any())
            ->method('getCanShipPartially')
            ->willReturn(false);
        $this->orderMockWrong->expects($this->any())
            ->method('getCanShipPartiallyItem')
            ->willReturn(false);

        $this->item1 = $this->getMockBuilder('\Magento\Sales\Model\Order\Item')
                            ->disableOriginalConstructor()
                            ->getMock();
        $this->item1->expects($this->any())
                    ->method('getItemId')
                    ->willReturn(1);
        $this->item1->expects($this->any())
                    ->method('getIsVirtual')
                    ->willReturn(false);

        $this->item1->expects($this->any())
            ->method('isDummy')
            ->willReturn(false);
        $this->item1->expects($this->any())
            ->method('getQtyToShip')
            ->willReturn(10);

        $this->item2 = $this->getMockBuilder('\Magento\Sales\Model\Order\Item')
            ->disableOriginalConstructor()
            ->getMock();
        $this->item2->expects($this->any())
            ->method('getItemId')
            ->willReturn(2);
        $this->item2->expects($this->any())
            ->method('isDummy')
            ->willReturn(false);
        $this->item2->expects($this->any())
            ->method('getIsVirtual')
            ->willReturn(true);

        $this->item2->expects($this->any())
            ->method('getQtyToShip')
            ->willReturn(0);
        $this->items = [
            0 => $this->item1,
            1 => $this->item2
        ];
        $this->orderMockRight->expects($this->any())
                             ->method('getItems')
                             ->willReturn($this->items);
        $this->orderMockWrong->expects($this->any())
            ->method('getItems')
            ->willReturn([]);
        $this->model = $this->objectManagerHelper->getObject('\RetailOps\Api\Service\OrderCheck');

    }

    public function testCanInvoice()
    {
        $this->assertTrue($this->model->canInvoice($this->orderMockRight));
        $this->assertFalse($this->model->canInvoice($this->orderMockWrong));
    }

    public function testCanOrderShip()
    {
        $this->assertTrue($this->model->canOrderShip($this->orderMockRight));
        $this->assertFalse($this->model->canOrderShip($this->orderMockWrong));
    }

    public function testHasItem()
    {
        $this->assertTrue($this->model->hasItem(1,$this->orderMockRight));
        $this->assertFalse($this->model->hasItem(3, $this->orderMockRight));
        $this->assertFalse($this->model->hasItem(2, $this->orderMockWrong));
    }


    public function testItemCanShipment()
    {
        $this->assertTrue($this->model->itemCanShipment($this->item1->getItemId(), $this->orderMockRight));
        $this->assertFalse($this->model->itemCanShipment($this->item2->getItemId(), $this->orderMockWrong));
    }
}