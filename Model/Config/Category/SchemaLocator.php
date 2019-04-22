<?php

namespace Genmato\StoreSetup\Model\Config\Category;

use Genmato\StoreSetup\Model\Config\AbstractSchemaLocator;
use Magento\Framework\Module\Dir;
use Magento\Framework\Module\Dir\Reader;

/**
 * Category locator
 */
class SchemaLocator extends AbstractSchemaLocator
{
    /**
     * @param Reader $moduleReader
     */
    public function __construct(Reader $moduleReader)
    {
        $this->_schema = $moduleReader->getModuleDir(Dir::MODULE_ETC_DIR, 'Genmato_StoreSetup') . '/' . 'category.xsd';
    }
}
