<?php
//require __DIR__ .'/../../../../../../../dev/tests/integration/testsuite/Magento/Sales/_files/order.php';
//require __DIR__ .'/../../../../../../../dev/tests/integration/testsuite/Magento/Sales/_files/invoice.php';
require __DIR__ . '/../../../../../../../dev/tests/integration/testsuite/Magento/Catalog/_files/product_simple.php';

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Sales\Model\Order\Item $orderItem */
$orderItem = $objectManager->create('Magento\Sales\Model\Order\Item');
$orderItem->setProductId($product->getId())->setQtyOrdered(3);
$orderItem->setBasePrice(10);
$orderItem->setPrice(10);
$orderItem->setRowTotal(10);
$orderItem->setProductType('simple');

/** @var \Magento\Sales\Model\Order $order */
$order = $objectManager->get('Magento\Sales\Model\Order');
$orderO = $order->loadByIncrementId('100000001');
$orderO->setSubtotal(
    110
)->setGrandTotal(
    110
)->setBaseSubtotal(
    110
)->setBaseGrandTotal(
    110
)->addItem(
    $orderItem
);
$orderO->save();