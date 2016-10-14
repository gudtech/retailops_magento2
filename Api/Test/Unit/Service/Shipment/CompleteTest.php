<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 12.10.16
 * Time: 18.49
 */

namespace RetailOps\Api\Test\Unit\Service\Shipment;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
class CompleteTest extends \PHPUnit_Framework_TestCase
{
    const PATH = '/app/code/RetailOps/Api/Test/Unit/Service/Shipment';
    /**
     * @var \Magento\Shipping\Model\Config | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $shippingConfigMock;

    /**
     * @var \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $shipmentLoaderMock;

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\ShipmentSender|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $shipmentSenderMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManagerHelper;

    /**
     * @var \RetailOps\Api\Service\Shipment\Complete
     */
    protected $model;

    public function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->shippingConfigMock = $this->getMockBuilder('\\Magento\Shipping\Model\Config')
                                         ->disableOriginalConstructor()
                                         ->getMock();
        $upsMock = $this->getMockBuilder('\\Magento\Ups\Model\Carrier')
        ->disableOriginalConstructor()
        ->getMock();
        $upsMock->expects($this->any())
            ->method('getConfigData')
            ->willReturn('ups');
        $uspsMock = $this->getMockBuilder('\\Magento\Usps\Model\Carrier')
        ->disableOriginalConstructor()
        ->getMock();
        $uspsMock->expects($this->any())
        ->method('getConfigData')
        ->willReturn('usps');
        $allCarriers = [
            'ups' => $upsMock,
            'usps' =>$uspsMock

        ];
        $this->shippingConfigMock->expects($this->any())
                                 ->method('getAllCarriers')
                                 ->willReturn($allCarriers);
        $this->shipmentLoaderMock = $this->getMockBuilder('\Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader')
                                         ->disableOriginalConstructor()
                                         ->getMock();
        $this->shipmentSenderMock = $this->getMockBuilder('\Magento\Sales\Model\Order\Email\Sender\ShipmentSender')
                                         ->disableOriginalConstructor()
                                         ->getMock();
        $this->model = $this->objectManagerHelper->getObject(
            'RetailOps\Api\Service\Shipment\Complete',
            [
            'shippingConfig' => $this->shippingConfigMock,
            'shipmentLoader' => $this->shipmentLoaderMock,
            'shipmentSender' => $this->shipmentSenderMock
            ]
        );
    }
    //Start unshipping elements

    public function testSetUnShippedItemsEmpty()
    {
        $postData = \file_get_contents($this->getPath('orderCompleteEmptyUnShipment.json'));
        $postData = json_decode($postData, true);
        $this->model->setUnShippedItems($postData['order']);
        $this->assertEmpty($this->model->getUnShippmentItems());
    }

    public function testSetUnShippedItemsNotEmpty()
    {
        $postData = \file_get_contents($this->getPath('orderCompleteUnShipment.json'));
        $postData = json_decode($postData, true);
        $this->model->setUnShippedItems($postData['order']);
        $this->assertCount(1, $this->model->getUnShippmentItems());
    }

    public function testCalcQuantity()
    {
        $this->setPropertyValue($this->model,'unShippmentItems', ['150'=>1]);
        $this->assertEquals(1,$this->getDataFromMethod($this->model, 'calcQuantity',['150', 2]));
        $this->assertEquals(2,$this->getDataFromMethod($this->model, 'calcQuantity',['150', 2]));
        $this->setPropertyValue($this->model,'unShippmentItems', ['150'=>10]);
        $this->assertEquals(0, $this->getDataFromMethod($this->model, 'calcQuantity',['150', 2]));
        $this->assertEquals(0, $this->getDataFromMethod($this->model, 'calcQuantity',['150', 8]));
        $this->assertEquals(8, $this->getDataFromMethod($this->model, 'calcQuantity',['150', 8]));

    }

    public function testSetTrackingAndShipmentItems()
    {
        $postData = \file_get_contents($this->getPath('orderCompleteUnShipment.json'));
        $postData = json_decode($postData, true);
        $this->setPropertyValue($this->model,'unShippmentItems', ['150'=>1]);
        $this->model->setTrackingAndShipmentItems($postData['order']);
        $tracking = $this->model->getTracking();
        $this->assertEquals(1, count($tracking));
        $this->assertEquals('ups',$tracking[0]['carrier_code']);
        $shipmentsItems = $this->model->getShippmentItems();
        $this->assertEquals(1, count($shipmentsItems));
        $this->assertEquals(1,$shipmentsItems['items']['150']);

        //fails test
        $postData = \file_get_contents($this->getPath('orderCompleteEmptyUnShipment.json'));
        $postData = json_decode($postData, true);
        //reset values
        $this->setPropertyValue($this->model,'unShippmentItems', []);
        $this->setPropertyValue($this->model,'shippmentItems', []);
        //reset values
        $this->model->setTrackingAndShipmentItems($postData['order']);
        $tracking = $this->model->getTracking();
        $this->assertEquals(2, count($tracking));
        $this->assertEquals('custom',$tracking[1]['carrier_code']);
        $shipmentsItems = $this->model->getShippmentItems();
        $this->assertEquals(2, count($shipmentsItems['items']));
        $this->assertEquals(3,$shipmentsItems['items']["62"]);

    }

    protected function getDataFromMethod($object, $methodName, $args)
    {
        $class  = new \ReflectionClass($object);
        $method = $class->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $args);
    }

    protected function getPath($filename)
    {
        $dirname = BP.self::PATH;
        return $dirname.DIRECTORY_SEPARATOR.$filename;
    }

    protected function setPropertyValue($object, $propertyName, $value)
    {
        $reflection = new \ReflectionClass($object);
        $reflection_property = $reflection->getProperty($propertyName);
        $reflection_property->setAccessible(true);
        $reflection_property->setValue($object,$value);
        return $object;
    }


}