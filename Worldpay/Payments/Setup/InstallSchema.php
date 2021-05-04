<?php 

namespace Worldpay\Payments\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * Installs DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $table = $installer->getConnection()
            ->newTable($installer->getTable('worldpay_payment'))
            ->addColumn('id', Table::TYPE_INTEGER, null, array(
                    'auto_increment' => true,
                    'identity'  => true,
                    'unsigned'  => true,
                    'nullable'  => false,
                    'primary'   => true
                ), 'Id')
            ->addColumn('customer_id', Table::TYPE_INTEGER, null, array(
                    'unsigned'  => true,
                    'nullable'  => false
                ), 'Customer Id')

            ->addColumn('token', Table::TYPE_TEXT, 255, array(
                ), 'Token');

        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }

}