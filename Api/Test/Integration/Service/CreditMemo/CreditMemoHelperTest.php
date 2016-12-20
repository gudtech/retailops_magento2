<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 14.11.16
 * Time: 17.17
 */

namespace RetailOps\Api\Test\Integration\Service\CreditMemo;

use Magento\TestFramework\Helper\Bootstrap;

class CreditMemoHelperTest extends \PHPUnit_Framework_TestCase
{
    const INCREMENT_1 = '100000001';

    protected function setUp()
    {
        Bootstrap::getObjectManager()->get('Magento\Framework\App\AreaList')
            ->getArea('adminhtml')
            ->load(\Magento\Framework\App\Area::PART_CONFIG);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testCreateNotCreateMemo()
    {
        $objectManager = Bootstrap::getObjectManager();
        /**
         * @var \Magento\Sales\Model\Order $order
         */
        $order = $objectManager->get('Magento\Sales\Model\Order');
        $order->loadByIncrementId(self::INCREMENT_1);
        $items = $order->getItems();
        $arrItems = [];
        foreach ($items as $item)
        {
            $arrItems[$item->getId()] = $item->getQtyOrdered();
        }
        /**
         * @var \RetailOps\Api\Service\CreditMemo\CreditMemoHelper
         */
        $creditMemoHelper = $objectManager->get('RetailOps\Api\Service\CreditMemo\CreditMemoHelper');
        $creditMemoItems = $creditMemoHelper->needCreditMemo($order, $arrItems);
        $this->assertEquals(0,count($creditMemoItems));
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @magentoDataFixture Magento/Sales/_files/invoice.php
     */
    public function testCreateMemoInvoice()
    {
        $objectManager = Bootstrap::getObjectManager();
        /**
         * @var \Magento\Sales\Model\Order $order
         */
        $order = $objectManager->get('Magento\Sales\Model\Order');
        $order->loadByIncrementId(self::INCREMENT_1);
        $items = $order->getItems();
        $arrItems = [];
        foreach ($items as $item)
        {
            $arrItems[$item->getId()] = $item->getQtyOrdered();
        }
        /**
         * @var \RetailOps\Api\Service\CreditMemo\CreditMemoHelper
         */
        $creditMemoHelper = $objectManager->get('RetailOps\Api\Service\CreditMemo\CreditMemoHelper');
        $creditMemoItems = $creditMemoHelper->needCreditMemo($order, $arrItems);
        foreach ($arrItems as $key => $quantity)
        {
            $this->assertEquals((float)$quantity, $creditMemoItems[$key]);
        }
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @magentoDataFixture Magento/Sales/_files/invoice.php
     * @magentoDataFixture ../../../../app/code/RetailOps/Api/Test/Integration/_files/add_product.php
     */
    public function testCreateMemoInvoiceAndOrdered()
    {
        $objectManager = Bootstrap::getObjectManager();
        /**
         * @var \Magento\Sales\Model\Order $order
         */
        $order = $objectManager->get('Magento\Sales\Model\Order');
        $order->loadByIncrementId(self::INCREMENT_1);
        $items = $order->getItems();
        $arrItems = [];
        foreach ($items as $item)
        {
            $arrItems[$item->getId()] = $item->getQtyOrdered();
        }
        $rightMassive = [];
        foreach ($items as $item){
            if($item->getQtyInvoiced() > 0){
                $rightMassive[$item->getId()] = $item->getQtyInvoiced();
            }else{
                $rightMassive[$item->getId()] = 0;
            }
        }

        /**
         * @var \RetailOps\Api\Service\CreditMemo\CreditMemoHelper
         */
        $creditMemoHelper = $objectManager->get('RetailOps\Api\Service\CreditMemo\CreditMemoHelper');
        $creditMemoItems = $creditMemoHelper->needCreditMemo($order, $arrItems);
        foreach ($creditMemoItems as $key => $quantity)
        {
            $this->assertEquals((float)$quantity, $rightMassive[$key]);
        }
    }
}