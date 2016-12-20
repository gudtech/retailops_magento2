<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 20.10.16
 * Time: 10.50
 */

namespace RetailOps\Api\Api\Order\Map;


interface UpcFinderInterface
{
    /**
     * @param \Magento\Sales\Api\Data\OrderItemInterface $orderItem
     * @param \Magento\Catalog\Api\Data\ProductInterface|null $product
     * @return string|null
     */
    public function getUpc(\Magento\Sales\Api\Data\OrderItemInterface $orderItem,
                           \Magento\Catalog\Api\Data\ProductInterface $product=null);

    /**
     * @param \RetailOps\Api\Api\Data\RetailOpsRicsLinkByUpcInterface $upc
     * @return $this
     */
    public function setRoUpc($upc);
}