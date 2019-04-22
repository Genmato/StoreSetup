<?php

namespace Genmato\StoreSetup\Setup;

use Genmato\StoreSetup\Model\SetupList;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Recurring implements InstallSchemaInterface
{
    /**
     * @var DataFactory
     */
    protected $configData;

    /**
     * @var SetupList
     */
    protected $setupList;

    /**
     * Init
     * @param DataFactory $configData
     * @param SetupList $setupList
     */
    public function __construct(
        SetupList $setupList
    ) {
        $this->setupList = $setupList;
    }

    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->setupList->executeSetup(false);
    }
}
