<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 15.9.16
 * Time: 16.06
 */

namespace Shiekhdev\RetailOps\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if(version_compare($context->getVersion(),'0.0.2') < 0){
            $tableName = $setup->getTable('sales_order');
            $tableNameGrid = $setup->getTable('sales_order_grid');
            if ($setup->getConnection()->isTableExists($tableName)) {
                $columns = [
                  'retailops_send_status' => [
                      'type' => Table::TYPE_BOOLEAN,
                      'default' => false,
                      'nullable' => false,
                      'comment' => 'Status order retailops'
                  ]
                ];
                $connection = $setup->getConnection();
                foreach ($columns as $name => $definition) {
                    $connection->dropColumn($tableName, $name);
                    $connection->dropColumn($tableNameGrid, $name);
                    $connection->addColumn($tableName, $name, $definition);
                    $connection->addColumn($tableNameGrid, $name, $definition);
                }
            }
        }

        if (version_compare($context->getVersion(), '0.0.3') < 0) {
            $tableName = $setup->getTable('sales_order');
            $tableNameGrid = $setup->getTable('sales_order_grid');
            if ($setup->getConnection()->isTableExists($tableName)) {
                $columns = [
                    'retailops_order_id' => [
                        'type' => Table::TYPE_TEXT,
                        'default' => false,
                        'nullable' => false,
                        'length' => 255,
                        'comment' => 'Order id in retailops'
                    ]
                ];
                $connection = $setup->getConnection();
                foreach ($columns as $name => $definition) {
                    $connection->dropColumn($tableName, $name);
                    $connection->dropColumn($tableNameGrid, $name);
                    $connection->addColumn($tableName, $name, $definition);
                    $connection->addColumn($tableNameGrid, $name, $definition);
                }
            }
        }
        if (version_compare($context->getVersion(),'0.0.4') < 0 ) {
            $this->createOrderHistory($setup);
        }
        if (version_compare($context->getVersion(), '0.1.0') < 0 ) {
            $this->createLoggerTable($setup);
        }
        $setup->endSetup();
    }

    protected function createLoggerTable($installer)
    {
        if ( $installer->getConnection()->isTableExists($installer->getTable('retailops/order_status_history'))) {
            $installer->getConnection()->dropTable($installer->getTable('retailops/order_status_history'));
        }
        $table = $installer->getConnection()
             ->newTable($installer->getTable('retailops/order_status_history'))
             ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true],
                'ID'
             )
            ->addColumn(
                'request',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                [],
                'Logger post data request'
            )
            ->addColumn(
                'response',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                [],
                'Logger response'

            )
            ->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '255',
                [],
                'Status'
            )
            ->addColumn(
                'error',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '8k',
                [],
                'Log exception, if exists'
            )
            ->addColumn(
                'url',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '255',
                [],
                'Url request'
            )
            ->addColumn(
                'create_date',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [],
                'Date create time'
            );
        $installer->getConnection()->createTable($table);

    }

    protected function createOrderHistory($installer)
    {
        $table = $installer->getConnection()
            ->newTable($installer->getTable('retailops/order_logger'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true],
                'ID'
            )
            ->addColumn(
                'parent_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Email'
            )
            ->addColumn(
                'comment',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                [],
                'Comment'
            )
            ->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                32,
                [],
                'Status'
            )
            ->addColumn('created_at', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, array(
            ), 'Created At')
            ->addIndex(
                $installer->getIdxName(
                    $installer->getTable('retailops/order_status_history'),
                    ['parent_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                ),
                ['parent_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX]
            )
            ->addIndex(
                $installer->getIdxName(
                    $installer->getTable('retailops/order_status_history'),
                    ['created_at'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                ),
                ['created_at'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX]
            )
            ->addIndex(
                $installer->getIdxName(
                    $installer->getTable('retailops/order_status_history'),
                    ['created_at'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                ),
                ['created_at'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX]
            )
            ->addIndex(
                $installer->getIdxName(
                    $installer->getTable('retailops/order_status_history'),
                    ['status'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                ),
                ['status'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX]
            )
            ->setComment('Retail ops order status history');
        $installer->getConnection()->createTable($table);
    }
}