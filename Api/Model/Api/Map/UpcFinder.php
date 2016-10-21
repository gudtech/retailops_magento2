<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 20.10.16
 * Time: 11.10
 */

namespace RetailOps\Api\Model\Api\Map;

use \RetailOps\Api\Api\Order\Map\UpcFinderInterface;
class UpcFinder implements UpcFinderInterface
{
    /**
     * @param \Magento\Sales\Api\Data\OrderItemInterface $orderItem
     * @param \Magento\Catalog\Api\Data\ProductInterface|null $product
     * @return string
     */
    public function getUpc(\Magento\Sales\Api\Data\OrderItemInterface $orderItem,
                           \Magento\Catalog\Api\Data\ProductInterface $product = null)
    {
        if($product !== null) {
            return $product->getUpc();
        }else {
            return $this->getUpcBySku($orderItem);
        }
    }

    /**
     * This function actual only for sku, where sku = upc + 's'
     * @param \Magento\Sales\Api\Data\OrderItemInterface $orderItem
     * @return string
     */
    public function getUpcBySku(\Magento\Sales\Api\Data\OrderItemInterface $orderItem)
    {
        return ltrim($orderItem->getSku(),'\S\s');
    }

    public function setUpc()
    {

    }

}