<?php
namespace RetailOps\Api\Setup;
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 11.10.16
 * Time: 16.59
 */
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $table = $installer->getConnection()
            ->newTable($installer->getTable('retailops_inventory_history'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )
                ->addColumn(
                    'product_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Product id'
                )
                ->addColumn(
                    'inventory_arrived',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    ['nullable' => true],
                    'Inventory that send from brige'
                )
                ->addColumn(
                    'inventory_in_shop',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    ['nullable' => true],
                    'Inventory in shop before update stock by rics'
                )
                ->addColumn(
                    'operator',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    1,
                    [],
                    '+/-'
                )
                ->addColumn(
                    'inventory_add',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    [],
                    'Module of invetory items from bridge'
                )
                ->addColumn(
                    'date_create',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                    'Date Create'
                );
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}