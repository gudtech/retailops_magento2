<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 15.9.16
 * Time: 16.06
 */

namespace RetailOps\Api\Setup;

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
        if(version_compare($context->getVersion(), '1.0.0')< 0) {
            $this->addUpcFinderTable($setup);
        }
        if(version_compare($context->getVersion(), '1.0.1')<0) {
            $this->addColumnsToInventoryHistory($setup);
        }

        if(version_compare($context->getVersion(), '1.0.2')<0) {
            $this->addQueueTable($setup);
        }
        $setup->endSetup();
    }

    protected function addQueueTable($installer)
    {
        if ($installer->getConnection()->isTableExists($installer->getTable('retailops_api_queue'))) {
            return;
        }
            $table = $installer->getConnection()->newTable(
                $installer->getTable('retailops_api_queue')
            )->addColumn(
                'retailops_api_queue_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [ 'identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true, ],
                'Entity ID'
            )->addColumn(
                'message',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                [ 'nullable' => true, ],
                'Message'
            )->addColumn(
                'creation_time',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [ 'nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT, ],
                'Creation Time'
            )->addColumn(
                'update_time',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [ 'nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE, ],
                'Modification Time'
            )->addColumn(
                'is_active',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                [ 'nullable' => false, 'default' => '1', ],
                'Is Active'
            )->addColumn(
                'queue_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [ 'nullable' => false, 'default' => 1, ],
                'Type of queue(cancel and e.t.c.)'
            )->addColumn(
                'order_increment_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Order increment id'
            );
        $installer->getConnection()->createTable($table);
    }

    protected function addUpcFinderTable($installer)
    {
        if ($installer->getConnection()->isTableExists($installer->getTable('retailops_rics_retailops_link_upc'))) {
            return;
//            $installer->getConnection()->dropTable($installer->getTable('retailops_rics_retailops_link_upc'));
        }
        $table = $installer->getConnection()
                           ->newTable($installer->getTable('retailops_rics_retailops_link_upc'))
                          ->addColumn(
                            'entity_id',
                            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                            255,
                            ['identity'=>true,'nullable'=>false, 'primary' => true],
                            'ID'
            )
                           ->addColumn(
                               'rics_integration_id',
                               \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                               255,
                               [],
                               'ID'
                           )
                          ->addColumn(
                              'upc',
                              \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                              '255',
                               ['default'=> null],
                              'Upc\'s from rics'
                          )
                         ->addColumn(
                             'retail_ops_upc',
                             \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                             null,
                            ['default' => null],
                             'Upc which use in retail_ops system, boolean'
                          )
                        ->addColumn(
                            'created_at',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                            null,
                            ['default'=>\Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT, 'nullable' => false],
                            'Created At'
                         )
                       ->addColumn(
                           'update_at',
                           \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                           null,
                           ['default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE, 'nullable' => false],
                           'Update at'
                       )
                       ->addIndex(
                           $installer->getIdxName('rics_integration_id', ['rics_integration_id', 'upc','retail_ops_upc'],
                               \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE),
                           ['rics_integration_id', 'upc','retail_ops_upc'],
                           ['type' =>  \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
                       )
                       ->addIndex(
                            $installer->getIdxName('upc', ['upc'],
                                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX),
                            ['upc'],
                           ['type'=>\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX]
                       )
                       ->addIndex(
                           $installer->getIdxName('rics_integration_id', ['rics_integration_id'],
                               \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                           ),
                           ['rics_integration_id'],
                           ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX]
                           );
            $installer->getConnection()->createTable($table);
    }

    protected function createLoggerTable($installer)
    {
        if ( $installer->getConnection()->isTableExists($installer->getTable('retailops_order_status_history'))) {
            $installer->getConnection()->dropTable($installer->getTable('retailops_order_status_history'));
        }
        $table = $installer->getConnection()
             ->newTable($installer->getTable('retailops_order_status_history'))
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
            ->newTable($installer->getTable('retailops_order_logger'))
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
                    $installer->getTable('retailops_order_status_history'),
                    ['parent_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                ),
                ['parent_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX]
            )
            ->addIndex(
                $installer->getIdxName(
                    $installer->getTable('retailops_order_status_history'),
                    ['created_at'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                ),
                ['created_at'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX]
            )
            ->addIndex(
                $installer->getIdxName(
                    $installer->getTable('retailops_order_status_history'),
                    ['created_at'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                ),
                ['created_at'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX]
            )
            ->addIndex(
                $installer->getIdxName(
                    $installer->getTable('retailops_order_status_history'),
                    ['status'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                ),
                ['status'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX]
            )
            ->setComment('Retail ops order status history');
        $installer->getConnection()->createTable($table);
    }

    protected function addColumnsToInventoryHistory($installer)
    {
        $tableName = $installer->getTable('retailops_inventory_history');
        if ($installer->getConnection()->isTableExists($tableName)) {
            $columns = [
                'real_count' => [
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                    'default' => 0,
                    'nullable' => false,
                    'comment' => 'Real count from RO'
                ],

                'reserve_count' => [
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                    'default' => 0,
                    'nullable' => false,
                    'comment' => 'Rererve by script'
                ]
            ];
            $connection = $installer->getConnection();
            foreach ($columns as $name => $definition) {
                $connection->addColumn($tableName, $name, $definition);
            }
        }
    }
}