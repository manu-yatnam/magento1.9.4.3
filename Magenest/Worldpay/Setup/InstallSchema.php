<?php

namespace Magenest\Worldpay\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $table = $installer->getConnection()->newTable($installer->getTable('magenest_worldpay_saved_cards'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                [
                    'auto_increment' => true,
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true
                ],
                'Id'
            )->addColumn(
                'customer_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned'  => true,
                    'nullable'  => false
                ],
                'Customer Id'
            )->addColumn(
                'token',
                Table::TYPE_TEXT,
                255,
                [],
                'Token'
            );

        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()->newTable($installer->getTable('magenest_worldpay_subscription_plans'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )
            ->addColumn(
                'product_id',
                Table::TYPE_INTEGER,
                null,
                [],
                'Product ID'
            )
            ->addColumn(
                'enabled',
                Table::TYPE_SMALLINT,
                null,
                [],
                'Is product enabled with subscription'
            )
            ->addColumn(
                'can_define_startdate',
                Table::TYPE_SMALLINT,
                null,
                [],
                'Can customer define startdate'
            )
            ->addColumn(
                'can_define_startdate',
                Table::TYPE_SMALLINT,
                null,
                [],
                'Can customer define startdate'
            )
            ->addColumn(
                'subscription_value',
                Table::TYPE_TEXT,
                null,
                [],
                'Subscription Value'
            )
            ->setComment('Subscription Plans Table');

        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()->newTable($installer->getTable('magenest_worldpay_subscription_profiles'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )
            // ids
            ->addColumn('product_id', Table::TYPE_INTEGER, null, [], 'Product ID')
            ->addColumn('order_id', Table::TYPE_TEXT, 15, [], 'Order ID')
            ->addColumn('customer_id', Table::TYPE_INTEGER, null, [], 'Product ID')
            // worldpay fields
            ->addColumn('token', Table::TYPE_TEXT, 255, [], 'Token')
            // order fields
            ->addColumn('status', Table::TYPE_TEXT, 10, [], 'Status')
            ->addColumn('amount', Table::TYPE_DECIMAL, '12,4', [], 'Amount')
            ->addColumn('currency', Table::TYPE_TEXT, 5, [], 'Currency Code')
            // recurring fields
            ->addColumn('total_cycles', Table::TYPE_SMALLINT, 3, [], 'Total Cycles')
            ->addColumn('frequency', Table::TYPE_TEXT, 20, [], 'Frequency')
            ->addColumn('remaining_cycles', Table::TYPE_SMALLINT, 3, [], 'Remaining cycles')
            ->addColumn('start_date', Table::TYPE_DATE, null, [], 'Start Date')
            ->addColumn('last_billed', Table::TYPE_DATE, null, [], 'Last Billed Date')
            ->addColumn('next_billing', Table::TYPE_DATE, null, [], 'Next Billing Day')
            // additional field
            ->addColumn('sequence_order_ids', Table::TYPE_TEXT, null, [], 'Sequence Order IDs')
            ->addColumn('additional_data', Table::TYPE_TEXT, null, [], 'Additional Data')
            ->setComment('Subscription Plans Table');

        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
