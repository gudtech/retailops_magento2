<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 12.10.16
 * Time: 18.49
 */

namespace RetailOps\Api\Test\Unit\Service\Shipment;


class CompleteTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {

        $context = $this->getMockBuilder(
            'Magento\Framework\View\Element\Context'
        )
            ->disableOriginalConstructor()
            ->setMethods(['getScopeConfig'])
            ->getMock();
        $storeConfig = $this->getMockBuilder('\Magento\Framework\App\Config\ScopeConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $storeConfig->expects($this->once())
            ->method('getValue')
            ->willReturn(true);
        $context->expects($this->once())
            ->method('getScopeConfig')
            ->willReturn($storeConfig);



        $this->observer = new \Shiekhdev\RICSApi\Model\Observer(
            $context
        );

    }

}