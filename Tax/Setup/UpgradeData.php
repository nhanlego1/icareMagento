<?php
/**
 * Created by PhpStorm.
 * User: nhan_nguyen
 * Date: 28/10/2016
 * Time: 14:32
 */

namespace Icare\Tax\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Tax\Setup\TaxSetupFactory;


/**
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * Tax setup factory
     *
     * @var TaxSetupFactory
     */
    private $taxSetupFactory;

    /**
     * Init
     *
     * @param TaxSetupFactory $taxSetupFactory
     */
    public function __construct(TaxSetupFactory $taxSetupFactory)
    {
        $this->taxSetupFactory = $taxSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var TaxSetup $taxSetup */
        $taxSetup = $this->taxSetupFactory->create(['resourceName' => 'tax_setup', 'setup' => $setup]);

        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            //Update the tax_class_id attribute in the 'catalog_eav_attribute' table
            $taxSetup->updateAttribute(4, 129, 'is_required', 1);
            $taxSetup->updateAttribute(4, 129, 'source_model', 'Icare\Tax\Model\TaxClass\Source\Product');
        }

        $setup->endSetup();
    }
}