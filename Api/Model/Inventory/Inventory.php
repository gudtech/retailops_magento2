<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 8.9.16
 * Time: 17.59
 */

namespace RetailOps\Api\Model\Inventory;

use Psr\Log\LoggerInterface;
use Magento\Framework\Indexer\CacheContext;


class Inventory
{
    const FROM = 'retailops';
    const SKU = 'upc';


    /**
     * @var \RetailOps\Api\Api\InventoryHistoryInterface
     */
    protected $_inventoryHistoryRepository;

    /**
     * @var LoggerInterface|\RetailOps\Api\Logger\Logger
     */
    protected $logger;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory|\RetailOps\Api\Model\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $_stock;

    /**
     * @var \Magento\CatalogInventory\Model\ResourceModel\Stock
     */
    protected $_stockRegistry;

    /**
     * @var \Magento\Store\Model\StoreManager|\Magento\Store\Model\StoreManagerInterface
     */
    protected $_store;

    /**
     * @var \Magento\CatalogInventory\Model\Stock\StockItemRepository
     */
    protected $_stockItem;

    /**
     * @var \RetailOps\Api\Api\Data\InventoryHistoryInterfaceFactory_InventoryHistory
     */
    protected $_InventoryHistoryFactory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \RetailOps\Api\Service\CalculateInventory
     */
    protected $_calculateInventory;



    /**
     * @param $inventory []
     */
    public function setInventory($inventory)
    {
        $productsRetailOps = [];
        $inventoryObjects = [];
        array_walk($inventory, function (&$item) use (&$productsRetailOps, &$inventoryObjects) {
            if ($item->getUPC()){
                $productsRetailOps[$item->getUPC()] = (float)$item->getCount();
                $inventoryObjects[$item->getUPC()] = $item;
            }
        });
        $itemsLower = [];
        $itemsUpper = [];
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToFilter('upc', ['in' => array_keys($productsRetailOps)]);
        $collection->setStoreId(0);
        //        $websiteId = $this->_store->getWebsite()->getId();
        $websites = $this->_store->getWebsites(true, true);
        $website = $websites['admin'];
        $websiteId = $website->getId();
        //set product id for refresh cache
        $productsForRefreshCache = [];
        foreach ($collection as $item) {
            $stock = $this->_stock->getStockItem($item->getId(), (int)$websiteId);
            $qty = $stock->getQty();
            $stock_id = $stock->getStockId();

            $inventoryHistory = $this->_InventoryHistoryFactory->create();

            $inventoryHistory->setProductId($item->getId());
            $inventoryHistory->setInventoryArrived($productsRetailOps[$item->getData('upc')]);

            $inventoryHistory->setInventoryInShop($qty);
            $inventoryHistory->setWebsiteId($websiteId);
            $inventoryHistory->setStockId($stock_id);
            $inventoryHistory->setFrom(self::FROM);
            $inventoryHistory->setReserveCount($inventoryObjects[$item->getData('upc')]->getReserveCount());
            $inventoryHistory->setRealCount($inventoryObjects[$item->getData('upc')]->getRealCount());
            if ($qty <= 0 and $productsRetailOps[$item->getData('upc')] > 0) {
                $productsForRefreshCache[$item->getId()] = ['stock' => $stock, 'inventory' => $productsRetailOps[$item->getData('upc')]];
                $this->logger->debug('cleanCache:', [$item->getId()]);
            }

            if ($qty > 0 and $productsRetailOps[$item->getData('upc')] <= 0) {
                $productsForRefreshCache[$item->getId()] = ['stock' => $stock, 'inventory' => $productsRetailOps[$item->getData('upc')]];
                $this->logger->debug('cleanCache:', [$item->getId()]);
            }
            if ($qty > $productsRetailOps[$item->getData('upc')]) {
                $count = $qty - $productsRetailOps[$item->getData('upc')];
                $itemsLower[$item->getId()] = $count;
                $inventoryHistory->setOperator('-');
                $inventoryHistory->setInventoryAdd($count);
                $this->logger->debug('productId-:' . $item->getId(), [$itemsLower[$item->getId()]]);
            }
            if ($qty < $productsRetailOps[$item->getData('upc')]) {
                $count = $productsRetailOps[$item->getData('upc')] - $qty;
                $itemsUpper[$item->getId()] = $count;
                $inventoryHistory->setOperator('+');
                $inventoryHistory->setInventoryAdd($count);
                $this->logger->debug('productId+:' . $item->getId(), [$itemsUpper[$item->getId()]]);
            }

            $this->_inventoryHistoryRepository->save($inventoryHistory);
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
        //fire event
        if(count($ids)>0){
            $this->_eventManager->dispatch('sales_catalog_product_change_stock_status', ['products'=> $ids]);
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
     * @param \RICSApi\Model\InventoryHistory $InventoryHistory
     */
    public function __construct(\RetailOps\Api\Logger\Logger $logger,
                                \RetailOps\Api\Model\Product\CollectionFactory $productCollectionFactory,
                                \Magento\CatalogInventory\Api\StockRegistryInterface $stock,
                                \Magento\CatalogInventory\Model\ResourceModel\Stock $stockRepositories,
                                \Magento\Store\Model\StoreManager $store,
                                \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItem,
                                \RetailOps\Api\Api\Data\InventoryHistoryInterfaceFactory $InventoryHistory,
                                \RetailOps\Api\Api\InventoryHistoryInterface $inventoryHistoryRepository,
                                \Magento\Framework\ObjectManagerInterface $objectManager,
                                \Magento\Catalog\Model\ProductFactory $productFactory,
                                \Magento\Framework\View\Element\Context $context,
                                \RetailOps\Api\Service\CalculateInventory $calculateInventory)
    {
        $this->logger = $logger;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_stock = $stock;
        $this->_stockRegistry = $stockRepositories;
        $this->_store = $store;
        $this->_stockItem = $stockItem;
        $this->_InventoryHistoryFactory = $InventoryHistory;
        $this->_objectManager = $objectManager;
        $this->_eventManager = $context->getEventManager();
        $this->_productFactory = $productFactory;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_inventoryHistoryRepository = $inventoryHistoryRepository;
        $this->_calculateInventory = $calculateInventory;
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
        return $this->_calculateInventory->calculateInventory($inventories);

    }


}