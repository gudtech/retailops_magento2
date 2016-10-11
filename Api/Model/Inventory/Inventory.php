<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 8.9.16
 * Time: 17.59
 */

namespace \RetailOps\Api\Model\Inventory;

use Psr\Log\LoggerInterface;
use Magento\Framework\Indexer\CacheContext;


class Inventory
{
    const FROM = 'retailops';
    const INVENTORY_TYPE = 'retailops/_RetailOps/statuses_inventory';
    const SKU = 'sku';
    const QUANTITY = 'quantity_available';
    /**
     * @param $inventory []
     */
    public function setInventory($inventory)
    {
        $productsRetailOps = [];
        array_walk($inventory, function (&$item) use (&$productsRetailOps) {
            if ($item->getSKU())
                $productsRetailOps[$item->getSKU()] = (float)$item->getCount();
        });
        $itemsLower = [];
        $itemsUpper = [];
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToFilter('sku', ['in' => array_keys($productsRetailOps)]);
        //        $websiteId = $this->_store->getWebsite()->getId();
        $websites = $this->_store->getWebsites(true, true);
        $website = $websites['admin'];
        $websiteId = $website->getId();
        //set product id for refresh cache
        $productsForRefreshCache = [];
        foreach ($collection as $item) {
            $stock = $this->_stock->getStockItem($item->getId(), (int)$websiteId);
//            $stock->setWebSiteId($websiteId);
//            $stock->setQty($productsRetailOps[$product->getData('upc')]);
//            $this->_stockItem->save($stock);

            $qty = $stock->getQty();
            $stock_id = $stock->getStockId();

            $this->_InventoryHistory = $this->_objectManager->create('\RICSApi\Model\InventoryHistory');

            $this->_InventoryHistory->setProductId($item->getId());
            $this->_InventoryHistory->setInventoryArrived($productsRetailOps[$item->getData('sku')]);

            $this->_InventoryHistory->setInventoryInShop($qty);
            $this->_InventoryHistory->setWebsiteId($websiteId);
            $this->_InventoryHistory->setStockId($stock_id);
            $this->_InventoryHistory->setFrom(self::FROM);
            if ($qty == 0 and $productsRetailOps[$item->getData('sku')] > 0) {
                $productsForRefreshCache[$item->getId()] = ['stock' => $stock, 'inventory' => $productsRetailOps[$item->getData('sku')]];
            }

            if ($qty > 0 and $productsRetailOps[$item->getData('sku')] <= 0) {
                $productsForRefreshCache[$item->getId()] = ['stock' => $stock, 'inventory' => $productsRetailOps[$item->getData('sku')]];
            }
//
            if ($qty > $productsRetailOps[$item->getData('sku')]) {
                $count = $qty - $productsRetailOps[$item->getData('sku')];
                $itemsLower[$item->getId()] = $count;
                $this->_InventoryHistory->setOperator('-');
                $this->_InventoryHistory->setInventoryAdd($count);
                $this->logger->debug('productId-:' . $item->getId(), [$itemsLower[$item->getSku()]]);
            }
            if ($qty < $productsRetailOps[$item->getData('sku')]) {
                $count = $productsRetailOps[$item->getData('sku')] - $qty;
                $itemsUpper[$item->getId()] = $count;
                $this->_InventoryHistory->setOperator('+');
                $this->_InventoryHistory->setInventoryAdd($count);
                $this->logger->debug('productId+:' . $item->getId(), [$itemsUpper[$item->getId()]]);
            }

            $this->_InventoryHistory->save();
        }

        $this->refreshChangeStockProducts($productsForRefreshCache);

        if (count($itemsLower)) {
            $this->_stockRegistry->correctItemsQty($itemsLower, $websiteId, '-');
        }
        if (count($itemsUpper)) {
            $this->_stockRegistry->correctItemsQty($itemsUpper, $websiteId, '+');
        }

        if (count($itemsLower) || count($itemsUpper)) {
            $this->_stockRegistry->updateSetInStock($websiteId);
            $this->_stockRegistry->updateSetOutOfStock($websiteId);
        }
    }

    public function refreshChangeStockProducts($products)
    {
        $ids = [];
        foreach ($products as $productId =>$items){
            $ids[] = $productId;
        }
        if(count($ids)>0){
            $cache = $this->getCacheContext();
            $cache->registerEntities(\Magento\Catalog\Model\Product::CACHE_TAG, $ids);
            $this->_eventManager->dispatch('clean_cache_by_tags', ['object' => $cache]);
        }


    }

    /**
     * Inventory constructor.
     * @param LoggerInterface $logger
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stock
     * @param \Magento\CatalogInventory\Model\ResourceModel\Stock $stockRepositories
     * @param \Magento\Store\Model\StoreManagerInterface $store
     * @param \Magento\CatalFFogInventory\Model\Stock\StockItemRepository $stockItem
     * @param \\RICSApi\Model\InventoryHistory $InventoryHistory
     */
    public function __construct(\RetailOps\Api\Logger\Logger $logger,
                                \RetailOps\Api\Model\Product\CollectionFactory $productCollectionFactory,
                                \Magento\CatalogInventory\Api\StockRegistryInterface $stock,
                                \Magento\CatalogInventory\Model\ResourceModel\Stock $stockRepositories,
                                \Magento\Store\Model\StoreManager $store,
                                \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItem,
                                \RICSApi\Model\InventoryHistory $InventoryHistory,
                                \Magento\Framework\ObjectManagerInterface $objectManager,
                                \Magento\Framework\Event\ManagerInterface $eventManager,
                                \Magento\Catalog\Model\ProductFactory $productFactory,
                                \Magento\Framework\View\Element\Context $context)
    {
        $this->logger = $logger;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_stock = $stock;
        $this->_stockRegistry = $stockRepositories;
        $this->_store = $store;
        $this->_stockItem = $stockItem;
        $this->_InventoryHistory = $InventoryHistory;
        $this->_objectManager = $objectManager;
        $this->_eventManager = $eventManager;
        $this->_productFactory = $productFactory;
        $this->_scopeConfig = $context->getScopeConfig();
    }

    protected function getCacheContext()
    {
        return \Magento\Framework\App\ObjectManager::getInstance()->create(CacheContext::class);
    }

    /**
     * @param array $inventories
     * @return array
     */
    public function calculateInventory(array $inventories)
    {
        $inventoryTypes =  $this->_scopeConfig->getValue(self::INVENTORY_TYPE);
        if( $inventoryTypes === null ) {
            return $this->setDefaultInventories($inventories);
        }
        $inventoryTypes = explode(',', $inventoryTypes);
        foreach ($inventories as &$inventory)
        {
            $quantityDetail = $inventory['quantity_detail'];
            $count = 0;
            foreach ($quantityDetail as $quantity) {
                if ( in_array($quantity['quantity_type'], $inventoryTypes)
                    || (in_array('empty', $inventoryTypes) && $quantity['quantity_type'] == '')) {
                    $count += (float)$quantity['total_quantity'];
                }

            }
            $inventory['calc_inventory'] = $count;
        }

        return $inventories;
    }

    /**
     * @param array $inventories
     * @return array
     */
    public function setDefaultInventories($inventories)
    {
        foreach ($inventories as &$inventory)
        {
            $inventory['calc_inventory'] = $inventory[self::QUANTITY];
        }

        return $inventories;
    }
}