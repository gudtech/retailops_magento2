<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 27.10.16
 * Time: 10.11
 */

namespace RetailOps\Api\Service;

use \Magento\Sales\Model\Order as MagentoOrder;
class CalculateInventory
{
    const QUANTITY = 'quantity_available';
    const INVENTORY_TYPE = 'retailops/_RetailOps/statuses_inventory';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory
     */
    protected $productAttributeCollectionFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory
     */
    protected $orderItemCollectionFactory;

    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
                                \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
                                \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $orderItemCollectionFactory,
                                \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $productAttributeCollectionFactory
                                )
    {
        $this->orderItemCollectionFactory = $orderItemCollectionFactory;
        $this->productAttributeCollectionFactory = $productAttributeCollectionFactory;
        $this->scopeConfig = $scopeConfig;
    }

    public function calculateInventory(array $inventories)
    {
        $inventoryTypes =  $this->scopeConfig->getValue(self::INVENTORY_TYPE);
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

        return $inventories ?? [];
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

    /**
     * @param array \RetailOps\Api\Api\InventoryInterface[] $inventories
     */
    public function addInventoiesFromNotSendedOrderYet(array $inventories)
    {
        if(!count($inventories)) {
            return;
        }
        $upcs = [];
        foreach ($inventories as $inventory)
        {
            $upcs[] = (string)$inventory->getUpc();
        }
        if(count($upcs)) {
            $upcsWithCount = $this->getItemsQuantity($upcs);
            foreach ($inventories as $inventory) {
                if (isset($upcsWithCount[$inventory->getUpc()])) {
                    $quantity = (float)$inventory->getCount() - (float)$upcsWithCount[$inventory->getUpc()];
                    $inventory->setRealCount((float)$inventory->getCount());
                    $inventory->setReserveCount((float)$upcsWithCount[$inventory->getUpc()]);
                    $inventory->setCount($quantity);
                }
            }
        }

    }

    /**
     * @param array $upcs
     * @return array
     */
    protected function getItemsQuantity(array $upcs)
    {
        $upcWithCount = [];
        foreach ($upcs as $upc)
        {
            $upcWithCount[$upc] = 0;
        }
        /**
         * @var \Magento\Sales\Model\ResourceModel\Order\Item\Collection $collection
         */
        $collection = $this->orderItemCollectionFactory->create();
        /**
         * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection $collectionAttr
         */
        $collectionAttr = $this->productAttributeCollectionFactory->create();
        $attributeCode = 'upc';
        $upcId = $collectionAttr->getItemByColumnValue('attribute_code', $attributeCode)->getId();
        $connection = $collection->getConnection();
        $collection->getSelect()->joinLeft(
            ['so' => $connection->getTableName('sales_order')],
            'so.entity_id=main_table.order_id',
            []
        );
        $collection->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
        $collection->getSelect()->joinLeft(
            ['cpe' => $connection->getTableName('catalog_product_entity')],
            'main_table.product_id=cpe.row_id',
            []
        );
        $collection->getSelect()->joinLeft(
            ['cpev' => $connection->getTableName('catalog_product_entity_varchar')],
            'cpev.row_id=cpe.row_id AND cpev.store_id = 0 AND cpev.attribute_id='.$upcId,
            ['upc'=> 'cpev.value']
        );
        $collection->getSelect()->joinLeft(
            ['soi' => $connection->getTableName('sales_order_item')],
            'soi.item_id=main_table.parent_item_id',
            [new \Zend_Db_Expr('(IF(ISNULL(main_table.parent_item_id),
        SUM(main_table.qty_ordered),
        SUM(soi.qty_ordered)) 
        - IF(ISNULL(main_table.parent_item_id),
        SUM(main_table.qty_canceled),
        SUM(soi.qty_canceled))
        - IF(ISNULL(main_table.parent_item_id),
        SUM(main_table.qty_refunded),
        SUM(soi.qty_refunded))) AS sum_ordered')]
        );
        $collection->getSelect()->where('so.retailops_send_status=?',\RetailOps\Api\Model\Api\Map\Order::ORDER_NO_SEND_STATUS);
        $collection->getSelect()->where('cpev.value in (?)', $upcs);
        $collection->getSelect()->where('so.state NOT IN (?)', [MagentoOrder::STATE_CANCELED, MagentoOrder::STATE_CLOSED]);
        $collection->getSelect()->where('main_table.product_type=?', \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
//        $collection->getSelect()->where('soi.product_type=?', \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE);
        $collection->getSelect()->where('cpev.value IS NOT NULL');
        $collection->getSelect()->group('cpev.value');
        $collection->load();
        foreach ($collection as $item) {
            if(isset($upcWithCount[$item->getUpc()])) {
                $upcWithCount[$item->getUpc()] = (float)$item->getSumOrdered();
            }
        }
        return $upcWithCount;
    }
}